// ============================================
// BOOKSHELF 3D - THREE.JS IMPLEMENTATION
// ============================================

// Scene variables
let scene, camera, renderer, controls;
let bookshelf, books = [];
let decorations = [];
let userBooks = [];
let autoRotate = false;
let rotationSpeed = 0.5;
let selectedBook = null;
let decorationsVisible = true; // Track decoration visibility state

// Initialize Three.js scene - Room 3D
function init() {
    // Create scene with room environment
    scene = new THREE.Scene();
    scene.background = new THREE.Color(0xe8e8e8); // Light gray room background
    scene.fog = new THREE.Fog(0xe8e8e8, 15, 40);

    // Create camera
    camera = new THREE.PerspectiveCamera(
        60, // Wider FOV for room view
        window.innerWidth / window.innerHeight,
        0.1,
        1000
    );
    camera.position.set(6, 4, 8); // Better angle to view the room
    camera.lookAt(0, 1, 0);

    // Create renderer with performance optimizations
    const container = document.getElementById('canvas-container');
    renderer = new THREE.WebGLRenderer({ 
        antialias: false, // Disable antialiasing for better performance
        alpha: true,
        powerPreference: "high-performance"
    });
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2)); // Cap pixel ratio for performance
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap; // Better shadows for room
    container.appendChild(renderer.domElement);

    // Add OrbitControls (simple implementation if not available)
    setupControls();

    // Create room first
    createRoom();
    
    // Add improved lights
    addLights();

    // Create beautiful bookshelf
    createBookshelf();
    
    // Create room furniture
    createRoomFurniture();

    // Hide loading overlay immediately after scene is initialized
    const loadingOverlay = document.getElementById('loading-overlay');
    if (loadingOverlay) {
        // Hide overlay after a short delay to show scene is ready
        setTimeout(() => {
            loadingOverlay.style.opacity = '0';
            loadingOverlay.style.transition = 'opacity 0.5s ease-out';
            setTimeout(() => {
                loadingOverlay.style.display = 'none';
            }, 500);
        }, 500); // Show for at least 500ms
    }

    // Load user's books and decorations asynchronously with lazy loading
    // Use requestIdleCallback for better performance, fallback to setTimeout
    if (window.requestIdleCallback) {
        requestIdleCallback(() => {
            loadUserBooksLazy();
            loadDecorations();
        }, { timeout: 1000 });
    } else {
        setTimeout(() => {
            loadUserBooksLazy();
            loadDecorations();
        }, 1000); // Delay to let initial render complete
    }

    // Setup mouse interaction
    setupMouseInteraction();

    // Start animation loop
    animate();

    // Handle window resize
    window.addEventListener('resize', onWindowResize);

    // Loading overlay will be hidden after books are loaded
}

// Setup camera controls using OrbitControls
function setupControls() {
    // Try to use OrbitControls from CDN (loaded globally)
    // For Three.js r128, OrbitControls should be available via window.THREE.OrbitControls
    // or directly as THREE.OrbitControls if loaded properly
    try {
        // Check multiple possible locations for OrbitControls
        let OrbitControlsClass = null;
        if (typeof THREE !== 'undefined' && THREE.OrbitControls) {
            OrbitControlsClass = THREE.OrbitControls;
        } else if (typeof window !== 'undefined' && window.THREE && window.THREE.OrbitControls) {
            OrbitControlsClass = window.THREE.OrbitControls;
        } else if (typeof OrbitControls !== 'undefined') {
            OrbitControlsClass = OrbitControls;
        }
        
        if (OrbitControlsClass) {
            controls = new OrbitControlsClass(camera, renderer.domElement);
            controls.enableDamping = true; // Smooth rotation
            controls.dampingFactor = 0.05;
            controls.minDistance = 5;
            controls.maxDistance = 20;
            controls.maxPolarAngle = Math.PI / 2; // Prevent going below floor
            controls.target.set(0, 1, 0); // Look at room center
            controls.update();
            console.log('✓ Using OrbitControls for camera');
            return; // Success, exit early
        }
    } catch (error) {
        console.warn('OrbitControls not available, using custom controls:', error);
    }
    
    // Fallback to custom controls if OrbitControls not available
    console.log('Using custom camera controls (mouse drag to rotate, scroll to zoom)');
    
    // Custom controls implementation
    let isDragging = false;
    let previousMousePosition = { x: 0, y: 0 };
    let cameraRotation = { x: 0.3, y: 0.5 }; // Initial rotation
    let cameraDistance = 12;

    const canvas = renderer.domElement;

    // Mouse down - start dragging
    canvas.addEventListener('mousedown', (e) => {
        // Only start dragging on left mouse button and when not clicking on UI elements
        if (e.button === 0) {
            isDragging = true;
            canvas.style.cursor = 'grabbing';
            previousMousePosition = { x: e.clientX, y: e.clientY };
        }
    });

    // Mouse move - rotate camera
    canvas.addEventListener('mousemove', (e) => {
        if (!isDragging) return;

        const deltaX = e.clientX - previousMousePosition.x;
        const deltaY = e.clientY - previousMousePosition.y;

        // Rotate around Y axis (horizontal) and X axis (vertical)
        cameraRotation.y += deltaX * 0.005;
        cameraRotation.x += deltaY * 0.005;
        cameraRotation.x = Math.max(-Math.PI / 3, Math.min(Math.PI / 2.5, cameraRotation.x));

        updateCameraPosition();
        previousMousePosition = { x: e.clientX, y: e.clientY };
    });

    // Mouse up - stop dragging
    canvas.addEventListener('mouseup', (e) => {
        if (e.button === 0) {
            isDragging = false;
            canvas.style.cursor = 'grab';
        }
    });

    // Mouse leave - stop dragging
    canvas.addEventListener('mouseleave', () => {
        isDragging = false;
        canvas.style.cursor = 'grab';
    });

    // Mouse wheel - zoom
    canvas.addEventListener('wheel', (e) => {
        e.preventDefault();
        cameraDistance += e.deltaY * 0.01;
        cameraDistance = Math.max(5, Math.min(25, cameraDistance));
        updateCameraPosition();
    });

    // Set initial cursor
    canvas.style.cursor = 'grab';

    function updateCameraPosition() {
        const x = Math.sin(cameraRotation.y) * Math.cos(cameraRotation.x) * cameraDistance;
        const y = Math.sin(cameraRotation.x) * cameraDistance + 4;
        const z = Math.cos(cameraRotation.y) * Math.cos(cameraRotation.x) * cameraDistance;
        
        camera.position.set(x, y, z);
        camera.lookAt(0, 1, 0);
    }
    
    // Store update function for animate loop
    window.updateCustomCamera = updateCameraPosition;
}

// Create room (floor, walls, ceiling)
function createRoom() {
    const roomGroup = new THREE.Group();
    
    // Floor - Wood texture
    const floorGeometry = new THREE.PlaneGeometry(20, 20);
    const floorMaterial = new THREE.MeshStandardMaterial({
        color: 0xd4a574, // Light wood color
        roughness: 0.8,
        metalness: 0.1
    });
    const floor = new THREE.Mesh(floorGeometry, floorMaterial);
    floor.rotation.x = -Math.PI / 2;
    floor.position.y = -0.1;
    floor.receiveShadow = true;
    roomGroup.add(floor);
    
    // Add wood grain pattern (simple texture)
    const floorPattern = createWoodTexture();
    floorMaterial.map = floorPattern;
    
    // Back wall
    const backWallGeometry = new THREE.PlaneGeometry(20, 8);
    const backWallMaterial = new THREE.MeshStandardMaterial({
        color: 0xf5f5f0, // Off-white
        roughness: 0.9
    });
    const backWall = new THREE.Mesh(backWallGeometry, backWallMaterial);
    backWall.position.z = -10;
    backWall.position.y = 3.9;
    backWall.receiveShadow = true;
    roomGroup.add(backWall);
    
    // Left wall
    const leftWall = new THREE.Mesh(backWallGeometry.clone(), backWallMaterial);
    leftWall.rotation.y = Math.PI / 2;
    leftWall.position.x = -10;
    leftWall.position.y = 3.9;
    leftWall.receiveShadow = true;
    roomGroup.add(leftWall);
    
    // Right wall
    const rightWall = new THREE.Mesh(backWallGeometry.clone(), backWallMaterial);
    rightWall.rotation.y = -Math.PI / 2;
    rightWall.position.x = 10;
    rightWall.position.y = 3.9;
    rightWall.receiveShadow = true;
    roomGroup.add(rightWall);
    
    // Ceiling
    const ceilingGeometry = new THREE.PlaneGeometry(20, 20);
    const ceilingMaterial = new THREE.MeshStandardMaterial({
        color: 0xffffff,
        roughness: 0.9
    });
    const ceiling = new THREE.Mesh(ceilingGeometry, ceilingMaterial);
    ceiling.rotation.x = Math.PI / 2;
    ceiling.position.y = 8;
    roomGroup.add(ceiling);
    
    // Window on back wall (simulated with lighter area)
    const windowGeometry = new THREE.PlaneGeometry(4, 3);
    const windowMaterial = new THREE.MeshStandardMaterial({
        color: 0x87ceeb, // Sky blue
        emissive: 0x87ceeb,
        emissiveIntensity: 0.3
    });
    const window = new THREE.Mesh(windowGeometry, windowMaterial);
    window.position.z = -9.9;
    window.position.y = 4;
    window.position.x = 6;
    roomGroup.add(window);
    
    scene.add(roomGroup);
}

// Create simple wood texture pattern
function createWoodTexture() {
    const canvas = document.createElement('canvas');
    canvas.width = 256;
    canvas.height = 256;
    const ctx = canvas.getContext('2d');
    
    // Base color
    ctx.fillStyle = '#d4a574';
    ctx.fillRect(0, 0, 256, 256);
    
    // Wood grain lines
    ctx.strokeStyle = '#b8956a';
    ctx.lineWidth = 2;
    for (let i = 0; i < 20; i++) {
        ctx.beginPath();
        ctx.moveTo(0, i * 12 + Math.random() * 5);
        ctx.quadraticCurveTo(128, i * 12 + Math.random() * 5, 256, i * 12 + Math.random() * 5);
        ctx.stroke();
    }
    
    const texture = new THREE.CanvasTexture(canvas);
    texture.wrapS = THREE.RepeatWrapping;
    texture.wrapT = THREE.RepeatWrapping;
    texture.repeat.set(4, 4);
    return texture;
}

// Add improved lights for room
function addLights() {
    // Ambient light - softer
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.5);
    scene.add(ambientLight);

    // Main directional light (window light)
    const mainLight = new THREE.DirectionalLight(0xfff8e1, 0.8);
    mainLight.position.set(6, 6, 4); // From window direction
    mainLight.castShadow = true;
    mainLight.shadow.mapSize.width = 2048;
    mainLight.shadow.mapSize.height = 2048;
    mainLight.shadow.camera.near = 0.5;
    mainLight.shadow.camera.far = 50;
    mainLight.shadow.camera.left = -10;
    mainLight.shadow.camera.right = 10;
    mainLight.shadow.camera.top = 10;
    mainLight.shadow.camera.bottom = -10;
    mainLight.shadow.bias = -0.0001;
    scene.add(mainLight);

    // Fill light (softer, from opposite side)
    const fillLight = new THREE.DirectionalLight(0xffffff, 0.3);
    fillLight.position.set(-5, 4, -5);
    scene.add(fillLight);

    // Point light (lamp on desk)
    const lampLight = new THREE.PointLight(0xffaa44, 0.6, 10);
    lampLight.position.set(-3, 2.5, 3);
    lampLight.castShadow = true;
    lampLight.shadow.mapSize.width = 512;
    lampLight.shadow.mapSize.height = 512;
    scene.add(lampLight);

    // Hemisphere light (sky/ambient)
    const hemisphereLight = new THREE.HemisphereLight(0xffffff, 0x444444, 0.4);
    hemisphereLight.position.set(0, 10, 0);
    scene.add(hemisphereLight);
    
    // Spot light for bookshelf area
    const spotLight = new THREE.SpotLight(0xffffff, 0.5);
    spotLight.position.set(0, 6, 5);
    spotLight.target.position.set(0, 0, 0);
    spotLight.angle = Math.PI / 6;
    spotLight.penumbra = 0.3;
    spotLight.castShadow = true;
    scene.add(spotLight);
    scene.add(spotLight.target);
}

// Create bookshelf model
function createBookshelf() {
    const bookshelfGroup = new THREE.Group();
    
    // Beautiful wood material with texture
    const woodMaterial = new THREE.MeshStandardMaterial({ 
        color: 0x8B4513, // Rich brown
        roughness: 0.6,
        metalness: 0.1
    });
    
    // Add wood texture
    const woodTexture = createWoodTexture();
    woodMaterial.map = woodTexture;

    // Shelf boards (horizontal) - 5 shelves for better display
    for (let i = 0; i < 5; i++) {
        const shelfGeometry = new THREE.BoxGeometry(7, 0.15, 1.8);
        const shelf = new THREE.Mesh(shelfGeometry, woodMaterial);
        shelf.position.set(0, i * 1.4 - 2.8, 0);
        shelf.castShadow = true;
        shelf.receiveShadow = true;
        bookshelfGroup.add(shelf);
        
        // Add subtle edge detail
        const edgeGeometry = new THREE.BoxGeometry(7.1, 0.05, 0.05);
        const edgeMaterial = new THREE.MeshStandardMaterial({ color: 0x654321 });
        const frontEdge = new THREE.Mesh(edgeGeometry, edgeMaterial);
        frontEdge.position.set(0, i * 1.4 - 2.8, 0.925);
        bookshelfGroup.add(frontEdge);
    }

    // Side panels (vertical) - thicker and more detailed
    const sideGeometry = new THREE.BoxGeometry(0.25, 7, 1.8);
    
    // Left side
    const leftSide = new THREE.Mesh(sideGeometry, woodMaterial);
    leftSide.position.set(-3.5, 0, 0);
    leftSide.castShadow = true;
    leftSide.receiveShadow = true;
    bookshelfGroup.add(leftSide);

    // Right side
    const rightSide = new THREE.Mesh(sideGeometry, woodMaterial);
    rightSide.position.set(3.5, 0, 0);
    rightSide.castShadow = true;
    rightSide.receiveShadow = true;
    bookshelfGroup.add(rightSide);

    // Back panel with darker wood
    const backGeometry = new THREE.BoxGeometry(7, 7, 0.1);
    const backMaterial = new THREE.MeshStandardMaterial({ 
        color: 0x654321,
        roughness: 0.8
    });
    const backPanel = new THREE.Mesh(backGeometry, backMaterial);
    backPanel.position.set(0, 0, -0.9);
    backPanel.receiveShadow = true;
    bookshelfGroup.add(backPanel);
    
    // Top decorative molding
    const topMoldingGeometry = new THREE.BoxGeometry(7.5, 0.3, 0.2);
    const topMolding = new THREE.Mesh(topMoldingGeometry, woodMaterial);
    topMolding.position.set(0, 3.5, 0.9);
    bookshelfGroup.add(topMolding);
    
    // Bottom base
    const baseGeometry = new THREE.BoxGeometry(7.5, 0.3, 0.3);
    const base = new THREE.Mesh(baseGeometry, woodMaterial);
    base.position.set(0, -3.5, 0.9);
    bookshelfGroup.add(base);

    bookshelf = bookshelfGroup;
    bookshelf.position.set(0, 0, -5); // Position against back wall
    scene.add(bookshelf);
}

// Create room furniture (desk, chair, etc.)
function createRoomFurniture() {
    const furnitureGroup = new THREE.Group();
    
    // Reading desk
    const deskTopGeometry = new THREE.BoxGeometry(3, 0.1, 1.5);
    const deskMaterial = new THREE.MeshStandardMaterial({
        color: 0x8B4513,
        roughness: 0.7
    });
    const deskTop = new THREE.Mesh(deskTopGeometry, deskMaterial);
    deskTop.position.set(-3, 1.5, 3);
    deskTop.castShadow = true;
    deskTop.receiveShadow = true;
    furnitureGroup.add(deskTop);
    
    // Desk legs
    const legGeometry = new THREE.BoxGeometry(0.1, 1.5, 0.1);
    const legPositions = [
        [-3.4, 0.75, 2.9], [-2.6, 0.75, 2.9],
        [-3.4, 0.75, 3.1], [-2.6, 0.75, 3.1]
    ];
    legPositions.forEach(pos => {
        const leg = new THREE.Mesh(legGeometry, deskMaterial);
        leg.position.set(...pos);
        leg.castShadow = true;
        furnitureGroup.add(leg);
    });
    
    // Reading chair (simple)
    const chairSeatGeometry = new THREE.BoxGeometry(0.6, 0.1, 0.6);
    const chairSeat = new THREE.Mesh(chairSeatGeometry, deskMaterial);
    chairSeat.position.set(-3, 1.2, 4);
    chairSeat.castShadow = true;
    chairSeat.receiveShadow = true;
    furnitureGroup.add(chairSeat);
    
    // Chair back
    const chairBackGeometry = new THREE.BoxGeometry(0.6, 0.8, 0.1);
    const chairBack = new THREE.Mesh(chairBackGeometry, deskMaterial);
    chairBack.position.set(-3, 1.6, 4.2);
    chairBack.castShadow = true;
    furnitureGroup.add(chairBack);
    
    // Rug on floor
    const rugGeometry = new THREE.PlaneGeometry(4, 3);
    const rugMaterial = new THREE.MeshStandardMaterial({
        color: 0x8B7355, // Brown rug
        roughness: 0.9
    });
    const rug = new THREE.Mesh(rugGeometry, rugMaterial);
    rug.rotation.x = -Math.PI / 2;
    rug.position.set(0, 0.01, -2);
    rug.receiveShadow = true;
    furnitureGroup.add(rug);
    
    scene.add(furnitureGroup);
}

// Create book model
function createBook(bookData, position) {
    const bookGroup = new THREE.Group();
    
    // Book dimensions
    const bookWidth = 0.3;
    const bookHeight = 0.4;
    const bookDepth = 0.05;
    
    // Book cover
    const coverGeometry = new THREE.BoxGeometry(bookWidth, bookHeight, bookDepth);
    let coverMaterial;
    
    // Use color material first for faster loading, load texture asynchronously
    coverMaterial = new THREE.MeshStandardMaterial({ 
        color: getBookColor(bookData.title),
        roughness: 0.5
    });
    
    // Load book cover image asynchronously (non-blocking)
    // Support both 'cover' and 'cover_url' fields from API
    const coverUrl = bookData.cover_url || bookData.cover;
    if (coverUrl && (coverUrl.startsWith('http') || coverUrl.startsWith('data:'))) {
        const textureLoader = new THREE.TextureLoader();
        // Load texture in background, don't wait for it
        setTimeout(() => {
            textureLoader.load(
                coverUrl,
                (texture) => {
                    texture.minFilter = THREE.LinearFilter;
                    texture.magFilter = THREE.LinearFilter;
                    const newMaterial = new THREE.MeshStandardMaterial({ 
                        map: texture,
                        roughness: 0.5
                    });
                    const cover = bookGroup.children.find(c => c.userData.type === 'cover');
                    if (cover) {
                        cover.material = newMaterial;
                        // Dispose old material
                        if (coverMaterial) coverMaterial.dispose();
                    }
                },
                undefined,
                (error) => {
                    // Keep default color material on error
                    console.warn('Error loading texture:', error);
                }
            );
        }, 100); // Small delay to not block initial render
    }
    
    const cover = new THREE.Mesh(coverGeometry, coverMaterial);
    cover.position.set(0, 0, bookDepth / 2);
    cover.castShadow = true;
    cover.receiveShadow = true;
    cover.userData.type = 'cover';
    cover.userData.bookId = bookData.id;
    cover.userData.bookData = bookData;
    bookGroup.add(cover);
    
    // Book pages (spine)
    const pagesGeometry = new THREE.BoxGeometry(bookWidth * 0.95, bookHeight * 0.95, bookDepth * 0.3);
    const pagesMaterial = new THREE.MeshStandardMaterial({ 
        color: 0xffffff,
        roughness: 0.8
    });
    const pages = new THREE.Mesh(pagesGeometry, pagesMaterial);
    pages.position.set(0, 0, 0);
    pages.castShadow = true;
    bookGroup.add(pages);
    
    // Book title label (optional - can be added as texture or 3D text)
    
    // Position book
    bookGroup.position.set(position.x, position.y, position.z);
    bookGroup.userData.bookData = bookData;
    bookGroup.userData.type = 'book';
    
    // Add hover effect
    cover.onBeforeRender = function() {
        // Can add glow effect here
    };
    
    scene.add(bookGroup);
    books.push(bookGroup);
    
    return bookGroup;
}

// Get color based on book title (for books without cover)
function getBookColor(title) {
    const colors = [
        0x4A90E2, // Blue
        0x50C878, // Green
        0xFF6B6B, // Red
        0xFFB347, // Orange
        0x9B59B6, // Purple
        0x3498DB, // Light Blue
        0xE74C3C, // Dark Red
        0x1ABC9C  // Teal
    ];
    let hash = 0;
    for (let i = 0; i < title.length; i++) {
        hash = title.charCodeAt(i) + ((hash << 5) - hash);
    }
    return colors[Math.abs(hash) % colors.length];
}

// Load user's books with lazy loading for better performance
async function loadUserBooksLazy() {
    try {
        // Try to load from API first
        if (window.APIClient) {
            const booksData = await window.APIClient.getBooks('all');
            userBooks = Array.isArray(booksData) ? booksData : (booksData.books || []);
            
            // If no books from API, try localStorage as fallback
            if (userBooks.length === 0) {
                const savedBooks = localStorage.getItem('user_books');
                if (savedBooks) {
                    try {
                        userBooks = JSON.parse(savedBooks);
                    } catch (e) {
                        console.error('Error parsing user books:', e);
                        userBooks = getDemoBooks();
                    }
                } else {
                    userBooks = getDemoBooks();
                }
            }
        } else {
            // Fallback to localStorage if API not available
            const savedBooks = localStorage.getItem('user_books');
            if (savedBooks) {
                try {
                    userBooks = JSON.parse(savedBooks);
                } catch (e) {
                    console.error('Error parsing user books:', e);
                    userBooks = getDemoBooks();
                }
            } else {
                userBooks = getDemoBooks();
            }
        }
    } catch (error) {
        console.error('Error loading books from API:', error);
        // Fallback to localStorage or demo
        const savedBooks = localStorage.getItem('user_books');
        if (savedBooks) {
            try {
                userBooks = JSON.parse(savedBooks);
            } catch (e) {
                userBooks = getDemoBooks();
            }
        } else {
            userBooks = getDemoBooks();
        }
    }
    
    // Limit books for performance (max 20 books for faster loading)
    const maxBooks = Math.min(userBooks.length, 20);
    const booksToRender = userBooks.slice(0, maxBooks);
    
    // Loading overlay should already be hidden by init(), but hide it here too if still visible
    const loadingOverlay = document.getElementById('loading-overlay');
    if (loadingOverlay && loadingOverlay.style.display !== 'none') {
        loadingOverlay.style.opacity = '0';
        loadingOverlay.style.transition = 'opacity 0.3s ease-out';
        setTimeout(() => {
            loadingOverlay.style.display = 'none';
        }, 300);
    }
    
    // Render books in batches for better performance
    const batchSize = 6; // Render 6 books at a time
    let currentIndex = 0;
    
    function renderBatch() {
        const endIndex = Math.min(currentIndex + batchSize, booksToRender.length);
        
        for (let i = currentIndex; i < endIndex; i++) {
            const book = booksToRender[i];
            const shelfIndex = Math.min(Math.floor(i / 6), 4); // 6 books per shelf, max 5 shelves
            const bookIndex = i % 6;
            const x = (bookIndex - 2.5) * 1.1; // Better spacing
            const y = shelfIndex * 1.4 - 2.8; // Match shelf positions
            const z = 0.1 + Math.random() * 0.1; // Slight depth variation
            
            // Add slight random rotation for natural look
            const rotation = (Math.random() - 0.5) * 0.15;
            
            const bookGroup = createBook(book, { x, y, z });
            bookGroup.rotation.y = rotation;
            bookGroup.position.set(x, y, z);
        }
        
        currentIndex = endIndex;
        
        // Continue rendering next batch if there are more books
        if (currentIndex < booksToRender.length) {
            // Use requestAnimationFrame for smooth rendering
            requestAnimationFrame(() => {
                setTimeout(renderBatch, 50); // Small delay between batches
            });
        } else {
            // All books rendered, hide loading overlay
            if (loadingOverlay) {
                loadingOverlay.style.display = 'none';
            }
        }
    }
    
    // Start rendering first batch
    renderBatch();
}

// Keep original function for compatibility
async function loadUserBooks() {
    return loadUserBooksLazy();
}

// Get demo books if no books in storage
function getDemoBooks() {
    return [
        { id: 1, title: 'Sapiens', author: 'Yuval Noah Harari', cover: '' },
        { id: 2, title: 'Atomic Habits', author: 'James Clear', cover: '' },
        { id: 3, title: 'The Lean Startup', author: 'Eric Ries', cover: '' },
        { id: 4, title: '1984', author: 'George Orwell', cover: '' },
        { id: 5, title: 'To Kill a Mockingbird', author: 'Harper Lee', cover: '' },
        { id: 6, title: 'The Great Gatsby', author: 'F. Scott Fitzgerald', cover: '' },
        { id: 7, title: 'Pride and Prejudice', author: 'Jane Austen', cover: '' },
        { id: 8, title: 'The Catcher in the Rye', author: 'J.D. Salinger', cover: '' }
    ];
}

// Load decorations from shop - only show equipped decorations
async function loadDecorations() {
    let equippedDecorationIds = [];
    
    try {
        // Get equipped items from localStorage
        const savedEquipped = localStorage.getItem('bookOnline_equipped_items');
        let equippedItems = {};
        if (savedEquipped) {
            try {
                equippedItems = JSON.parse(savedEquipped);
            } catch (e) {
                console.error('Error parsing equipped items:', e);
            }
        }
        
        // Get equipped decoration ID (only show equipped decorations)
        const equippedDecorationId = equippedItems['decoration'];
        if (!equippedDecorationId) {
            console.log('No decoration equipped, showing default decorations');
            // Show default decorations if nothing equipped
            const defaultDecorations = [
                { type: 'plant', position: { x: -3.5, y: 0.2, z: 2.5 }, scale: 1.2, id: 'default_plant' },
                { type: 'lamp', position: { x: -3, y: 2.5, z: 3 }, scale: 1, id: 'default_lamp' },
                { type: 'vase', position: { x: -2.5, y: 1.6, z: 3 }, scale: 0.8, id: 'default_vase' }
            ];
            defaultDecorations.forEach(decoration => {
                createDecoration(decoration);
            });
            return;
        }
        
        // Load user inventory to verify item exists and get details
        let inventory = [];
        if (window.APIClient) {
            try {
                const inventoryData = await window.APIClient.getInventory();
                inventory = Array.isArray(inventoryData) ? inventoryData : (inventoryData.items || []);
            } catch (error) {
                console.error('Error loading inventory:', error);
            }
        }
        
        // Filter to only decoration category items
        const decorationItems = inventory.filter(item => {
            const category = item.category || '';
            return category === 'decoration';
        });
        
        // Check if equipped decoration exists in inventory
        const equippedItem = decorationItems.find(item => {
            const itemId = item.item_id || item.id;
            return itemId == equippedDecorationId; // Use == for type coercion
        });
        
        if (!equippedItem) {
            console.log('Equipped decoration not found in inventory');
            return;
        }
        
        equippedDecorationIds = [equippedDecorationId];
        
    } catch (error) {
        console.error('Error loading decorations:', error);
    }
    
    // Shop items mapping - positioned in room (item_id -> decoration config)
    const shopItemsMap = {
        1: { type: 'plant', position: { x: -3.5, y: 0.2, z: 2.5 }, scale: 1.2 }, // On floor near desk
        2: { type: 'lamp', position: { x: -3, y: 2.5, z: 3 }, scale: 1 }, // On desk
        3: { type: 'frame', position: { x: 6, y: 4, z: -9.8 }, scale: 1 }, // On wall
        4: { type: 'statue', position: { x: 3.5, y: 0.3, z: -3 }, scale: 1 }, // On floor near bookshelf
        5: { type: 'vase', position: { x: -2.5, y: 1.6, z: 3 }, scale: 0.8 }, // On desk
        6: { type: 'clock', position: { x: 6, y: 5, z: -9.8 }, scale: 1 }, // On wall above window
        7: { type: 'coffee', position: { x: -2.5, y: 1.55, z: 3.2 }, scale: 0.5 }, // On desk
        8: { type: 'candle', position: { x: -3.5, y: 1.55, z: 3.2 }, scale: 0.6 } // On desk
    };
    
    // Create decorations for equipped items only
    equippedDecorationIds.forEach(itemId => {
        const itemIdNum = parseInt(itemId);
        const item = shopItemsMap[itemIdNum];
        if (item) {
            console.log(`Creating decoration for item ${itemId}:`, item);
            createDecoration({ id: itemIdNum, ...item });
        } else {
            console.warn(`Decoration mapping not found for item ID: ${itemId}`);
        }
    });
}

// Create decoration
function createDecoration(decoration) {
    let geometry, material, scale = 1;
    
    switch(decoration.type) {
        case 'plant':
            // Create a more realistic plant with pot and leaves
            const plantGroup = new THREE.Group();
            
            // Pot
            const potGeometry = new THREE.CylinderGeometry(0.12, 0.1, 0.15, 8);
            const potMaterial = new THREE.MeshStandardMaterial({ 
                color: 0x8B4513,
                roughness: 0.7
            });
            const pot = new THREE.Mesh(potGeometry, potMaterial);
            pot.position.y = 0.075;
            plantGroup.add(pot);
            
            // Plant stem/leaves
            const leavesGeometry = new THREE.ConeGeometry(0.2, 0.5, 8);
            const leavesMaterial = new THREE.MeshStandardMaterial({ 
                color: 0x4CAF50,
                roughness: 0.7
            });
            const leaves = new THREE.Mesh(leavesGeometry, leavesMaterial);
            leaves.position.y = 0.4;
            plantGroup.add(leaves);
            
            // Add to scene
            plantGroup.position.set(
                decoration.position.x,
                decoration.position.y,
                decoration.position.z
            );
            plantGroup.castShadow = true;
            plantGroup.receiveShadow = true;
            plantGroup.userData.type = 'decoration';
            plantGroup.userData.decorationId = decoration.id;
            const finalScale = decoration.scale || 1;
            plantGroup.scale.set(finalScale, finalScale, finalScale);
            
            scene.add(plantGroup);
            decorations.push(plantGroup);
            return plantGroup;
            
        case 'lamp':
            // Create a desk lamp with base and shade
            const lampGroup = new THREE.Group();
            
            // Lamp base
            const lampBaseGeometry = new THREE.CylinderGeometry(0.1, 0.12, 0.15, 8);
            const lampBaseMaterial = new THREE.MeshStandardMaterial({ 
                color: 0x2C2C2C,
                metalness: 0.8,
                roughness: 0.2
            });
            const lampBase = new THREE.Mesh(lampBaseGeometry, lampBaseMaterial);
            lampGroup.add(lampBase);
            
            // Lamp stem
            const stemGeometry = new THREE.CylinderGeometry(0.02, 0.02, 0.3, 8);
            const stem = new THREE.Mesh(stemGeometry, lampBaseMaterial);
            stem.position.y = 0.225;
            lampGroup.add(stem);
            
            // Lamp shade (truncated cone)
            const shadeGeometry = new THREE.CylinderGeometry(0.12, 0.08, 0.2, 8);
            const shadeMaterial = new THREE.MeshStandardMaterial({ 
                color: 0xFFD700,
                metalness: 0.3,
                roughness: 0.4,
                emissive: 0xFFD700,
                emissiveIntensity: 0.2
            });
            const shade = new THREE.Mesh(shadeGeometry, shadeMaterial);
            shade.position.y = 0.4;
            lampGroup.add(shade);
            
            // Add to scene
            lampGroup.position.set(
                decoration.position.x,
                decoration.position.y,
                decoration.position.z
            );
            lampGroup.castShadow = true;
            lampGroup.receiveShadow = true;
            lampGroup.userData.type = 'decoration';
            lampGroup.userData.decorationId = decoration.id;
            const lampScale = decoration.scale || 1;
            lampGroup.scale.set(lampScale, lampScale, lampScale);
            
            scene.add(lampGroup);
            decorations.push(lampGroup);
            return lampGroup;
        case 'frame':
            geometry = new THREE.BoxGeometry(0.3, 0.4, 0.05);
            material = new THREE.MeshStandardMaterial({ 
                color: 0xD4AF37,
                metalness: 0.5,
                roughness: 0.3
            });
            break;
        case 'vase':
            // More detailed vase
            geometry = new THREE.CylinderGeometry(0.1, 0.08, 0.25, 12);
            material = new THREE.MeshStandardMaterial({ 
                color: 0x8B4513,
                metalness: 0.4,
                roughness: 0.5
            });
            break;
        case 'clock':
            // Wall clock with frame and face
            const clockGroup = new THREE.Group();
            
            // Clock frame (outer ring)
            const frameGeometry = new THREE.CylinderGeometry(0.25, 0.25, 0.05, 32);
            const frameMaterial = new THREE.MeshStandardMaterial({ 
                color: 0x2C3E50,
                metalness: 0.6,
                roughness: 0.3
            });
            const frame = new THREE.Mesh(frameGeometry, frameMaterial);
            clockGroup.add(frame);
            
            // Clock face
            const faceGeometry = new THREE.CylinderGeometry(0.22, 0.22, 0.02, 32);
            const faceMaterial = new THREE.MeshStandardMaterial({ 
                color: 0xFFFFFF,
                roughness: 0.8
            });
            const face = new THREE.Mesh(faceGeometry, faceMaterial);
            face.position.z = 0.01;
            clockGroup.add(face);
            
            // Clock hands (simple)
            const handGeometry = new THREE.BoxGeometry(0.15, 0.01, 0.01);
            const handMaterial = new THREE.MeshStandardMaterial({ color: 0x000000 });
            const hourHand = new THREE.Mesh(handGeometry, handMaterial);
            hourHand.rotation.z = -Math.PI / 6; // 10 o'clock
            hourHand.position.z = 0.02;
            clockGroup.add(hourHand);
            
            const minuteHandGeometry = new THREE.BoxGeometry(0.18, 0.008, 0.008);
            const minuteHand = new THREE.Mesh(minuteHandGeometry, handMaterial);
            minuteHand.rotation.z = Math.PI / 3; // 20 minutes
            minuteHand.position.z = 0.02;
            clockGroup.add(minuteHand);
            
            // Add to scene
            clockGroup.position.set(
                decoration.position.x,
                decoration.position.y,
                decoration.position.z
            );
            clockGroup.castShadow = true;
            clockGroup.receiveShadow = true;
            clockGroup.userData.type = 'decoration';
            clockGroup.userData.decorationId = decoration.id;
            const clockScale = decoration.scale || 1;
            clockGroup.scale.set(clockScale, clockScale, clockScale);
            
            scene.add(clockGroup);
            decorations.push(clockGroup);
            return clockGroup;
        case 'coffee':
            // Coffee cup with handle
            const coffeeGroup = new THREE.Group();
            
            // Cup body
            const cupGeometry = new THREE.CylinderGeometry(0.08, 0.08, 0.12, 12);
            const cupMaterial = new THREE.MeshStandardMaterial({ 
                color: 0xFFFFFF,
                roughness: 0.3,
                metalness: 0.1
            });
            const cup = new THREE.Mesh(cupGeometry, cupMaterial);
            coffeeGroup.add(cup);
            
            // Coffee inside
            const coffeeGeometry = new THREE.CylinderGeometry(0.075, 0.075, 0.1, 12);
            const coffeeMaterial = new THREE.MeshStandardMaterial({ 
                color: 0x6F4E37,
                roughness: 0.8
            });
            const coffee = new THREE.Mesh(coffeeGeometry, coffeeMaterial);
            coffee.position.y = 0.01;
            coffeeGroup.add(coffee);
            
            // Handle (simple torus)
            const handleGeometry = new THREE.TorusGeometry(0.05, 0.01, 8, 16);
            const handle = new THREE.Mesh(handleGeometry, cupMaterial);
            handle.position.set(0.09, 0, 0);
            handle.rotation.z = Math.PI / 2;
            coffeeGroup.add(handle);
            
            // Add to scene
            coffeeGroup.position.set(
                decoration.position.x,
                decoration.position.y,
                decoration.position.z
            );
            coffeeGroup.castShadow = true;
            coffeeGroup.receiveShadow = true;
            coffeeGroup.userData.type = 'decoration';
            coffeeGroup.userData.decorationId = decoration.id;
            const coffeeScale = decoration.scale || 1;
            coffeeGroup.scale.set(coffeeScale, coffeeScale, coffeeScale);
            
            scene.add(coffeeGroup);
            decorations.push(coffeeGroup);
            return coffeeGroup;
            
        case 'candle':
            // Candle with flame
            const candleGroup = new THREE.Group();
            
            // Candle body
            const candleBodyGeometry = new THREE.CylinderGeometry(0.05, 0.05, 0.2, 12);
            const candleBodyMaterial = new THREE.MeshStandardMaterial({ 
                color: 0xF5DEB3,
                roughness: 0.7
            });
            const candleBody = new THREE.Mesh(candleBodyGeometry, candleBodyMaterial);
            candleGroup.add(candleBody);
            
            // Flame
            const flameGeometry = new THREE.ConeGeometry(0.03, 0.08, 6);
            const flameMaterial = new THREE.MeshStandardMaterial({ 
                color: 0xFF6600,
                emissive: 0xFF6600,
                emissiveIntensity: 0.5
            });
            const flame = new THREE.Mesh(flameGeometry, flameMaterial);
            flame.position.y = 0.14;
            candleGroup.add(flame);
            
            // Add to scene
            candleGroup.position.set(
                decoration.position.x,
                decoration.position.y,
                decoration.position.z
            );
            candleGroup.castShadow = true;
            candleGroup.receiveShadow = true;
            candleGroup.userData.type = 'decoration';
            candleGroup.userData.decorationId = decoration.id;
            const candleScale = decoration.scale || 1;
            candleGroup.scale.set(candleScale, candleScale, candleScale);
            
            scene.add(candleGroup);
            decorations.push(candleGroup);
            return candleGroup;
        case 'statue':
            // Decorative statue (simplified humanoid or abstract)
            const statueGroup = new THREE.Group();
            
            // Base
            const statueBaseGeometry = new THREE.CylinderGeometry(0.12, 0.12, 0.05, 8);
            const statueBaseMaterial = new THREE.MeshStandardMaterial({ 
                color: 0x654321,
                metalness: 0.3,
                roughness: 0.6
            });
            const statueBase = new THREE.Mesh(statueBaseGeometry, statueBaseMaterial);
            statueGroup.add(statueBase);
            
            // Body (torso)
            const bodyGeometry = new THREE.BoxGeometry(0.15, 0.2, 0.1);
            const bodyMaterial = new THREE.MeshStandardMaterial({ 
                color: 0x8B7355,
                roughness: 0.6,
                metalness: 0.2
            });
            const body = new THREE.Mesh(bodyGeometry, bodyMaterial);
            body.position.y = 0.15;
            statueGroup.add(body);
            
            // Head
            const headGeometry = new THREE.SphereGeometry(0.08, 12, 12);
            const head = new THREE.Mesh(headGeometry, bodyMaterial);
            head.position.y = 0.35;
            statueGroup.add(head);
            
            // Add to scene
            statueGroup.position.set(
                decoration.position.x,
                decoration.position.y,
                decoration.position.z
            );
            statueGroup.castShadow = true;
            statueGroup.receiveShadow = true;
            statueGroup.userData.type = 'decoration';
            statueGroup.userData.decorationId = decoration.id;
            const statueScale = decoration.scale || 1;
            statueGroup.scale.set(statueScale, statueScale, statueScale);
            
            scene.add(statueGroup);
            decorations.push(statueGroup);
            return statueGroup;
        default:
            geometry = new THREE.BoxGeometry(0.2, 0.2, 0.2);
            material = new THREE.MeshStandardMaterial({ 
                color: 0x888888,
                roughness: 0.5
            });
            scale = 1;
    }
    
    const mesh = new THREE.Mesh(geometry, material);
    
    // Use scale from decoration object if provided, otherwise use default
    const finalScale = decoration.scale || scale;
    mesh.scale.set(finalScale, finalScale, finalScale);
    
    mesh.position.set(
        decoration.position.x,
        decoration.position.y,
        decoration.position.z
    );
    mesh.castShadow = true;
    mesh.receiveShadow = true;
    mesh.userData.type = 'decoration';
    mesh.userData.decorationId = decoration.id;
    
    scene.add(mesh);
    decorations.push(mesh);
    
    return mesh; // Return mesh for further manipulation
}

// Setup mouse interaction
function setupMouseInteraction() {
    const raycaster = new THREE.Raycaster();
    const mouse = new THREE.Vector2();
    let hoveredBook = null;
    
    function onMouseMove(event) {
        mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
        mouse.y = -(event.clientY / window.innerHeight) * 2 + 1;
        
        raycaster.setFromCamera(mouse, camera);
        const intersects = raycaster.intersectObjects(books, true);
        
        // Reset previous hover
        if (hoveredBook) {
            hoveredBook.scale.set(1, 1, 1);
            hoveredBook = null;
        }
        
        // Highlight hovered book
        if (intersects.length > 0) {
            const book = intersects[0].object.parent;
            if (book && book.userData.type === 'book') {
                hoveredBook = book;
                book.scale.set(1.1, 1.1, 1.1);
                renderer.domElement.style.cursor = 'pointer';
            }
        } else {
            renderer.domElement.style.cursor = 'default';
        }
    }
    
    function onMouseClick(event) {
        mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
        mouse.y = -(event.clientY / window.innerHeight) * 2 + 1;
        
        raycaster.setFromCamera(mouse, camera);
        const intersects = raycaster.intersectObjects(books, true);
        
        if (intersects.length > 0) {
            const book = intersects[0].object.parent;
            if (book && book.userData.type === 'book') {
                const bookData = book.userData.bookData;
                if (bookData) {
                    showBookInfo(bookData);
                }
            }
        } else {
            closeBookInfo();
        }
    }
    
    renderer.domElement.addEventListener('mousemove', onMouseMove);
    renderer.domElement.addEventListener('click', onMouseClick);
}

// Show book info panel
function showBookInfo(bookData) {
    selectedBook = bookData;
    const panel = document.getElementById('book-info-panel');
    document.getElementById('info-book-title').textContent = bookData.title;
    document.getElementById('info-book-author').textContent = bookData.author || 'Tác giả không xác định';
    document.getElementById('info-book-link').href = `book-info.html?id=${bookData.id}`;
    panel.style.display = 'block';
}

// Close book info panel
function closeBookInfo() {
    const panel = document.getElementById('book-info-panel');
    panel.style.display = 'none';
    selectedBook = null;
}

// Control functions
function resetCamera() {
    // Reset camera to initial position
    if (controls && controls.target) {
        // Using OrbitControls
        camera.position.set(6, 4, 8);
        controls.target.set(0, 1, 0);
        controls.update();
    } else {
        // Using custom controls
        camera.position.set(6, 4, 8);
        camera.lookAt(0, 1, 0);
    }
}

function toggleDecorations() {
    // Toggle visibility state
    decorationsVisible = !decorationsVisible;
    
    // Apply visibility to all decorations
    decorations.forEach(decoration => {
        decoration.visible = decorationsVisible;
    });
    
    // Update button text
    const btn = document.getElementById('toggle-decorations-btn');
    if (btn) {
        btn.innerHTML = decorationsVisible 
            ? '<i class="fas fa-eye-slash mr-2"></i>Ẩn trang trí'
            : '<i class="fas fa-eye mr-2"></i>Hiện trang trí';
    }
}

function toggleAutoRotate() {
    autoRotate = !autoRotate;
    const btn = document.getElementById('toggle-rotate-btn');
    btn.innerHTML = autoRotate
        ? '<i class="fas fa-pause mr-2"></i>Dừng xoay'
        : '<i class="fas fa-sync mr-2"></i>Tự động xoay';
}

function updateRotationSpeed(value) {
    rotationSpeed = parseFloat(value);
}

// Make functions globally available
if (typeof window !== 'undefined') {
    window.updateRotationSpeed = updateRotationSpeed;
    window.toggleDecorations = toggleDecorations;
    window.toggleAutoRotate = toggleAutoRotate;
    window.resetCamera = resetCamera;
}

// Animation loop
function animate() {
    requestAnimationFrame(animate);
    
    // Update OrbitControls if available
    if (controls && controls.update) {
        controls.update();
    }
    
    // Auto rotate camera (only if not using OrbitControls or if explicitly enabled)
    if (autoRotate && (!controls || !controls.enableDamping)) {
        const time = Date.now() * 0.0005 * rotationSpeed;
        camera.position.x = Math.sin(time) * 12;
        camera.position.z = Math.cos(time) * 12;
        camera.lookAt(0, 1, 0);
    }
    
    // Animate books (subtle floating effect)
    books.forEach((book, index) => {
        if (book && book.rotation) {
            const time = Date.now() * 0.001;
            book.rotation.y += 0.001;
            if (book.position) {
                book.position.y += Math.sin(time + index) * 0.0005;
            }
        }
    });
    
    renderer.render(scene, camera);
}

// Handle window resize
function onWindowResize() {
    camera.aspect = window.innerWidth / window.innerHeight;
    camera.updateProjectionMatrix();
    renderer.setSize(window.innerWidth, window.innerHeight);
}

// Initialize when page loads
window.addEventListener('DOMContentLoaded', () => {
    // Check if Three.js is loaded
    if (typeof THREE === 'undefined') {
        alert('Three.js không được tải. Vui lòng kiểm tra kết nối mạng.');
        return;
    }
    
    // Check authentication
    if (!window.Auth || !window.Auth.isLoggedIn()) {
        alert('Bạn cần đăng nhập để xem kệ sách 3D');
        window.location.href = 'login.html?redirect=bookshelf-3d.html';
        return;
    }
    
    // Initialize 3D scene
    init();
});

