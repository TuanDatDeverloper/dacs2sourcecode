// ============================================
// ENHANCED 3D BOOKSHELF - With Professional 3D Models
// Uses GLTF/GLB models for better quality
// ============================================

// Scene variables
let scene, camera, renderer, controls;
let bookshelf, books = [];
let decorations = [];
let userBooks = [];
let selectedBook = null;
let decorationsVisible = true;
let modelLoader = null;
let autoRotate = false;
let rotationSpeed = 0.5;

// Model paths (will be populated with actual models)
const MODEL_PATHS = {
    bookshelf: {
        classic: 'assets/models/bookshelf/bookshelf-classic.glb',
        modern: 'assets/models/bookshelf/bookshelf-modern.glb',
        vintage: 'assets/models/bookshelf/bookshelf-vintage.glb'
    },
    book: {
        base: 'assets/models/books/book-base.glb',
        thick: 'assets/models/books/book-thick.glb'
    },
    furniture: {
        desk: 'assets/models/furniture/desk-reading.glb',
        chair: 'assets/models/furniture/chair-reading.glb',
        lamp: 'assets/models/furniture/lamp-desk.glb',
        plant: 'assets/models/furniture/plant-indoor.glb'
    },
    environment: {
        room: 'assets/models/environment/room-library.glb'
    }
};

// Initialize Three.js scene with enhanced models
function init() {
    console.log('Initializing Enhanced 3D Bookshelf...');
    
    // Check if THREE is available
    if (typeof THREE === 'undefined') {
        console.error('THREE.js is not loaded!');
        return;
    }
    
    // Initialize ModelLoader
    modelLoader = new ModelLoader();
    
    // Create scene
    scene = new THREE.Scene();
    scene.background = new THREE.Color(0xf0f0f0);
    
    // Create camera
    camera = new THREE.PerspectiveCamera(
        75,
        window.innerWidth / window.innerHeight,
        0.1,
        1000
    );
    camera.position.set(0, 5, 15);
    camera.lookAt(0, 0, 0);
    
    // Create renderer
    const container = document.getElementById('canvas-container');
    if (!container) {
        console.error('canvas-container not found!');
        return;
    }
    
    try {
        renderer = new THREE.WebGLRenderer({ 
            antialias: true,
            powerPreference: "high-performance"
        });
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
        renderer.shadowMap.enabled = true;
        renderer.shadowMap.type = THREE.PCFSoftShadowMap;
        container.appendChild(renderer.domElement);
        console.log('✓ Renderer created');
    } catch (error) {
        console.error('Error creating renderer:', error);
        return;
    }
    
    // Setup controls
    setupControls();
    
    // Add lights
    addLights();
    
    // Load models (try to load 3D models, fallback to simple geometry)
    loadBookshelfModel();
    
    // Load books and decorations asynchronously
    if (window.requestIdleCallback) {
        requestIdleCallback(() => {
            loadUserBooks();
            loadDecorations();
        }, { timeout: 500 });
    } else {
        setTimeout(() => {
            loadUserBooks();
            loadDecorations();
        }, 500);
    }
    
    // Setup mouse interaction
    setupMouseInteraction();
    
    // Hide loading overlay
    const loadingOverlay = document.getElementById('loading-overlay');
    if (loadingOverlay) {
        setTimeout(() => {
            loadingOverlay.style.opacity = '0';
            loadingOverlay.style.transition = 'opacity 0.5s ease-out';
            setTimeout(() => {
                loadingOverlay.style.display = 'none';
            }, 500);
        }, 500);
    }
    
    // Start animation
    animate();
    
    // Handle window resize
    window.addEventListener('resize', onWindowResize);
}

// Setup camera controls
function setupControls() {
    // Try OrbitControls
    if (typeof THREE !== 'undefined' && THREE.OrbitControls) {
        controls = new THREE.OrbitControls(camera, renderer.domElement);
        controls.enableDamping = true;
        controls.dampingFactor = 0.05;
        controls.minDistance = 8;
        controls.maxDistance = 30;
        controls.enablePan = true;
        controls.target.set(0, 2, 0);
        controls.update();
        console.log('✓ Using OrbitControls');
        return;
    }
    
    // Fallback: Simple mouse controls
    console.log('Using simple mouse controls');
    let isDragging = false;
    let previousMousePosition = { x: 0, y: 0 };
    let cameraRotation = { x: 0.3, y: 0 };
    let cameraDistance = 15;
    
    const canvas = renderer.domElement;
    canvas.style.cursor = 'grab';
    
    canvas.addEventListener('mousedown', (e) => {
        if (e.button === 0) {
            isDragging = true;
            canvas.style.cursor = 'grabbing';
            previousMousePosition = { x: e.clientX, y: e.clientY };
        }
    });
    
    canvas.addEventListener('mousemove', (e) => {
        if (!isDragging) return;
        const deltaX = e.clientX - previousMousePosition.x;
        const deltaY = e.clientY - previousMousePosition.y;
        
        cameraRotation.y += deltaX * 0.005;
        cameraRotation.x += deltaY * 0.005;
        cameraRotation.x = Math.max(-Math.PI / 3, Math.min(Math.PI / 2.5, cameraRotation.x));
        
        updateCamera();
        previousMousePosition = { x: e.clientX, y: e.clientY };
    });
    
    canvas.addEventListener('mouseup', () => {
        isDragging = false;
        canvas.style.cursor = 'grab';
    });
    
    canvas.addEventListener('wheel', (e) => {
        e.preventDefault();
        cameraDistance += e.deltaY * 0.01;
        cameraDistance = Math.max(8, Math.min(30, cameraDistance));
        updateCamera();
    });
    
    function updateCamera() {
        const x = Math.sin(cameraRotation.y) * Math.cos(cameraRotation.x) * cameraDistance;
        const y = Math.sin(cameraRotation.x) * cameraDistance + 2;
        const z = Math.cos(cameraRotation.y) * Math.cos(cameraRotation.x) * cameraDistance;
        camera.position.set(x, y, z);
        camera.lookAt(0, 2, 0);
    }
    
    window.updateCamera = updateCamera;
}

// Add improved lights
function addLights() {
    // Ambient light
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.6);
    scene.add(ambientLight);
    
    // Main directional light
    const directionalLight = new THREE.DirectionalLight(0xffffff, 0.8);
    directionalLight.position.set(5, 10, 5);
    directionalLight.castShadow = true;
    directionalLight.shadow.mapSize.width = 2048;
    directionalLight.shadow.mapSize.height = 2048;
    directionalLight.shadow.camera.near = 0.5;
    directionalLight.shadow.camera.far = 50;
    directionalLight.shadow.camera.left = -10;
    directionalLight.shadow.camera.right = 10;
    directionalLight.shadow.camera.top = 10;
    directionalLight.shadow.camera.bottom = -10;
    scene.add(directionalLight);
    
    // Fill light
    const fillLight = new THREE.DirectionalLight(0xffffff, 0.3);
    fillLight.position.set(-5, 5, -5);
    scene.add(fillLight);
    
    // Point light for atmosphere
    const pointLight = new THREE.PointLight(0xffaa44, 0.5, 20);
    pointLight.position.set(0, 5, 5);
    scene.add(pointLight);
}

// Load bookshelf model (try 3D model first, fallback to simple geometry)
async function loadBookshelfModel() {
    if (!modelLoader) {
        console.warn('ModelLoader not initialized, using simple bookshelf');
        createSimpleBookshelf();
        return;
    }

    // Try to load 3D model
    const modelPath = MODEL_PATHS.bookshelf.classic;
    
    try {
        console.log('Attempting to load 3D bookshelf model...');
        const bookshelfModel = await modelLoader.loadModel(modelPath, {
            scale: 1, // Will auto-scale based on bounding box
            position: { x: 0, y: 0, z: 0 },
            shadows: true,
            onProgress: (percent) => {
                console.log(`Loading bookshelf: ${percent.toFixed(1)}%`);
            }
        });
        
        // Auto-scale model to fit scene (target size: ~12 units wide, ~8 units tall)
        const box = new THREE.Box3().setFromObject(bookshelfModel);
        const size = box.getSize(new THREE.Vector3());
        const targetWidth = 12;
        const targetHeight = 8;
        
        // Calculate scale based on width or height (whichever is larger)
        const scaleX = targetWidth / size.x;
        const scaleY = targetHeight / size.y;
        const autoScale = Math.min(scaleX, scaleY) * 0.9; // 0.9 để có margin
        
        if (autoScale > 0.1 && autoScale < 10) {
            bookshelfModel.scale.set(autoScale, autoScale, autoScale);
            console.log(`✓ Auto-scaled bookshelf model: ${autoScale.toFixed(2)}x (original size: ${size.x.toFixed(2)} x ${size.y.toFixed(2)} x ${size.z.toFixed(2)})`);
        } else {
            // Fallback scale if calculation seems wrong
            bookshelfModel.scale.set(5, 5, 5);
            console.log('✓ Applied default scale 5x to bookshelf model');
        }
        
        // Center the model
        box.setFromObject(bookshelfModel);
        const center = box.getCenter(new THREE.Vector3());
        bookshelfModel.position.x -= center.x;
        bookshelfModel.position.y -= center.y;
        bookshelfModel.position.z -= center.z;
        
        bookshelf = bookshelfModel;
        scene.add(bookshelf);
        console.log('✓ 3D Bookshelf model loaded successfully');
        
        // Add decorations around bookshelf
        addBookshelfDecorations();
    } catch (error) {
        console.warn('Failed to load 3D bookshelf model, using simple geometry:', error);
        // Fallback to simple bookshelf
        createSimpleBookshelf();
        // Add decorations even for simple bookshelf
        addBookshelfDecorations();
    }
}

// Create simple bookshelf (fallback) - Sử dụng procedural enhanced nếu có
function createSimpleBookshelf() {
    // Try to use enhanced procedural bookshelf if available
    if (typeof createProceduralEnhancedBookshelf === 'function') {
        try {
            bookshelf = createProceduralEnhancedBookshelf();
            scene.add(bookshelf);
            console.log('✓ Enhanced procedural bookshelf created');
            return;
        } catch (error) {
            console.warn('Failed to create enhanced bookshelf, using simple version:', error);
        }
    }
    
    // Fallback to simple bookshelf
    const bookshelfGroup = new THREE.Group();
    
    const woodMaterial = new THREE.MeshStandardMaterial({
        color: 0x8B4513,
        roughness: 0.7,
        metalness: 0.1
    });
    
    // Back panel
    const backGeometry = new THREE.BoxGeometry(12, 8, 0.2);
    const backPanel = new THREE.Mesh(backGeometry, woodMaterial);
    backPanel.position.set(0, 4, -0.1);
    backPanel.castShadow = true;
    backPanel.receiveShadow = true;
    bookshelfGroup.add(backPanel);
    
    // Side panels
    const sideGeometry = new THREE.BoxGeometry(0.2, 8, 2);
    const leftSide = new THREE.Mesh(sideGeometry, woodMaterial);
    leftSide.position.set(-6, 4, 0.9);
    leftSide.castShadow = true;
    bookshelfGroup.add(leftSide);
    
    const rightSide = new THREE.Mesh(sideGeometry, woodMaterial);
    rightSide.position.set(6, 4, 0.9);
    rightSide.castShadow = true;
    bookshelfGroup.add(rightSide);
    
    // Shelves (4 shelves)
    const shelfGeometry = new THREE.BoxGeometry(11.6, 0.2, 2);
    for (let i = 0; i < 4; i++) {
        const shelf = new THREE.Mesh(shelfGeometry, woodMaterial);
        shelf.position.set(0, 1.5 + i * 1.8, 0.9);
        shelf.castShadow = true;
        shelf.receiveShadow = true;
        bookshelfGroup.add(shelf);
    }
    
    // Top and bottom
    const topGeometry = new THREE.BoxGeometry(12, 0.2, 2);
    const top = new THREE.Mesh(topGeometry, woodMaterial);
    top.position.set(0, 8, 0.9);
    top.castShadow = true;
    bookshelfGroup.add(top);
    
    const bottomGeometry = new THREE.BoxGeometry(12, 0.2, 2);
    const bottom = new THREE.Mesh(bottomGeometry, woodMaterial);
    bottom.position.set(0, 0, 0.9);
    bottom.castShadow = true;
    bottom.receiveShadow = true;
    bookshelfGroup.add(bottom);
    
    bookshelf = bookshelfGroup;
    scene.add(bookshelf);
    console.log('✓ Simple bookshelf created');
}

// Load user books
async function loadUserBooks() {
    try {
        if (window.APIClient && window.APIClient.getBooks) {
            const booksData = await window.APIClient.getBooks('all');
            userBooks = Array.isArray(booksData) ? booksData : [];
            console.log('Loaded', userBooks.length, 'books for bookshelf');
            if (userBooks.length > 0) {
                displayBooks();
            }
        } else {
            console.warn('APIClient or getBooks not available');
        }
    } catch (error) {
        console.error('Error loading books:', error);
    }
}

// Display books on shelf
function displayBooks() {
    if (!userBooks || userBooks.length === 0) return;
    
    const maxBooks = Math.min(userBooks.length, 40);
    const booksPerShelf = 10;
    
    for (let i = 0; i < maxBooks; i++) {
        const book = userBooks[i];
        const shelfIndex = Math.floor(i / booksPerShelf);
        const bookIndex = i % booksPerShelf;
        
        createBook(book, shelfIndex, bookIndex, booksPerShelf);
    }
}

// Create a book (try 3D model, fallback to simple geometry)
async function createBook(bookData, shelfIndex, bookIndex, booksPerShelf) {
    const bookGroup = new THREE.Group();
    
    // Try to load 3D book model
    if (modelLoader) {
        try {
            const bookModel = await modelLoader.loadModel(MODEL_PATHS.book.base, {
                scale: 0.3,
                shadows: true
            });
            
            // Apply book cover texture if available (with CORS handling)
            const coverUrl = bookData.cover_url || bookData.cover;
            if (coverUrl && (coverUrl.startsWith('http') || coverUrl.startsWith('data:'))) {
                // Try to load with CORS proxy or direct
                loadBookCoverTexture(coverUrl, bookModel);
            }
            
            bookGroup.add(bookModel);
        } catch (error) {
            console.warn('Failed to load 3D book model, using simple geometry:', error);
            createSimpleBook(bookGroup, bookData);
        }
    } else {
        createSimpleBook(bookGroup, bookData);
    }
    
    // Position on shelf
    const shelfY = 1.6 + shelfIndex * 1.8;
    const width = 0.8;
    const totalWidth = booksPerShelf * width;
    const startX = -totalWidth / 2 + width / 2;
    const x = startX + bookIndex * width;
    const z = 0.9 + 0.15 / 2;
    
    bookGroup.position.set(x, shelfY, z);
    bookGroup.userData.bookData = bookData;
    bookGroup.userData.type = 'book';
    
    books.push(bookGroup);
    scene.add(bookGroup);
}

// Create simple book (fallback)
function createSimpleBook(bookGroup, bookData) {
    const colors = [0x8B4513, 0x654321, 0x4A4A4A, 0x2C2C2C, 0x8B0000, 0x006400, 0x00008B];
    const color = colors[Math.floor(Math.random() * colors.length)];
    
    const bookMaterial = new THREE.MeshStandardMaterial({
        color: color,
        roughness: 0.5
    });
    
    const width = 0.8;
    const height = 1.2;
    const depth = 0.15;
    
    const bookGeometry = new THREE.BoxGeometry(width, height, depth);
    const bookMesh = new THREE.Mesh(bookGeometry, bookMaterial);
    bookMesh.castShadow = true;
    bookMesh.receiveShadow = true;
    
    // Try to load cover texture (with CORS handling)
    const coverUrl = bookData.cover_url || bookData.cover;
    if (coverUrl && (coverUrl.startsWith('http') || coverUrl.startsWith('data:'))) {
        loadBookCoverTexture(coverUrl, null, bookMaterial);
    }
    
    bookGroup.add(bookMesh);
}

// Load decorations
async function loadDecorations() {
    // Similar to original, but can use 3D models
    try {
        const savedEquipped = localStorage.getItem('bookOnline_equipped_items');
        let equippedItems = {};
        if (savedEquipped) {
            equippedItems = JSON.parse(savedEquipped);
        }
        
        const equippedDecorationId = equippedItems['decoration'];
        if (!equippedDecorationId) {
            console.log('No decoration equipped');
            return;
        }
        
        if (window.APIClient) {
            const inventory = await window.APIClient.getInventory();
            const inventoryItems = Array.isArray(inventory) ? inventory : [];
            
            const decorationItem = inventoryItems.find(item => {
                const itemId = item.item_id || item.id;
                return itemId == equippedDecorationId && (item.category || '').toLowerCase() === 'decoration';
            });
            
            if (decorationItem) {
                createDecoration(decorationItem, equippedDecorationId);
            }
        }
    } catch (error) {
        console.error('Error loading decorations:', error);
    }
}

// Create decoration (can use 3D models)
async function createDecoration(item, itemId) {
    const decorationGroup = new THREE.Group();
    const type = (item.type || '').toLowerCase();
    
    // Try to load 3D model for decoration
    let modelPath = null;
    switch(type) {
        case 'plant':
            modelPath = MODEL_PATHS.furniture.plant;
            break;
        case 'lamp':
            modelPath = MODEL_PATHS.furniture.lamp;
            break;
        case 'desk':
            modelPath = MODEL_PATHS.furniture.desk;
            break;
        case 'chair':
            modelPath = MODEL_PATHS.furniture.chair;
            break;
    }
    
    if (modelPath && modelLoader) {
        try {
            const decorationModel = await modelLoader.loadModel(modelPath, {
                scale: 1,
                shadows: true
            });
            decorationGroup.add(decorationModel);
        } catch (error) {
            console.warn('Failed to load 3D decoration model, using simple geometry:', error);
            createSimpleDecoration(decorationGroup, type);
        }
    } else {
        createSimpleDecoration(decorationGroup, type);
    }
    
    // Position based on type
    const positions = {
        plant: { x: -5, y: 0.75, z: 3 },
        lamp: { x: 7, y: 1.5, z: 3 },
        vase: { x: 6, y: 0.5, z: 3 },
        frame: { x: 0, y: 6, z: -0.2 }
    };
    
    const pos = positions[type] || { x: 0, y: 2, z: 3 };
    decorationGroup.position.set(pos.x, pos.y, pos.z);
    decorationGroup.userData.type = 'decoration';
    decorationGroup.userData.itemId = itemId;
    
    decorations.push(decorationGroup);
    scene.add(decorationGroup);
}

// Load book cover texture with CORS handling
function loadBookCoverTexture(coverUrl, bookModel, bookMaterial) {
    const textureLoader = new THREE.TextureLoader();
    
    // Try direct load first
    textureLoader.load(
        coverUrl,
        (texture) => {
            if (bookModel) {
                // Apply to 3D model
                bookModel.traverse((child) => {
                    if (child.isMesh && child.material) {
                        if (Array.isArray(child.material)) {
                            child.material.forEach(mat => {
                                if (mat.name === 'cover' || mat.name.includes('cover') || !mat.map) {
                                    mat.map = texture;
                                    mat.needsUpdate = true;
                                }
                            });
                        } else {
                            if (child.material.name === 'cover' || child.material.name.includes('cover') || !child.material.map) {
                                child.material.map = texture;
                                child.material.needsUpdate = true;
                            }
                        }
                    }
                });
            } else if (bookMaterial) {
                // Apply to simple material
                bookMaterial.map = texture;
                bookMaterial.needsUpdate = true;
            }
        },
        undefined,
        (error) => {
            // CORS error or other error - silently fail, use color material
            console.debug('Could not load book cover (CORS or other issue), using color material');
        }
    );
}

// Add decorative items around bookshelf (properly positioned)
function addBookshelfDecorations() {
    const decorationsGroup = new THREE.Group();
    
    // Get bookshelf position and size for reference
    // Bookshelf is centered at (0, 4, 0.9) with size ~12 wide x 8 tall
    
    // Plant on the floor, left side of bookshelf
    const plant = createDecorativePlant();
    plant.position.set(-7, 0.25, 1.5); // On floor (y = 0.25 = half plant height)
    decorationsGroup.add(plant);
    
    // Lamp on the floor, right side of bookshelf
    const lamp = createDecorativeLamp();
    lamp.position.set(7, 0.1, 1.5); // On floor
    decorationsGroup.add(lamp);
    
    // Small decorative items on top of bookshelf (y = 8 for top shelf)
    const vase = createDecorativeVase();
    vase.position.set(4, 8.2, 0.9); // On top of bookshelf
    decorationsGroup.add(vase);
    
    const statue = createDecorativeStatue();
    statue.position.set(-4, 8.2, 0.9); // On top of bookshelf
    decorationsGroup.add(statue);
    
    // Reading desk setup (to the left, on floor)
    // Desk surface at y = 1.5
    const deskY = 1.5;
    
    // Reading glasses on desk
    const glasses = createReadingGlasses();
    glasses.position.set(-3, deskY + 0.05, 2.5); // On desk surface
    decorationsGroup.add(glasses);
    
    // Coffee cup on desk
    const coffee = createCoffeeCup();
    coffee.position.set(-2.5, deskY + 0.08, 2.5); // On desk surface
    decorationsGroup.add(coffee);
    
    // Small book on desk
    const deskBook = createSmallDeskBook();
    deskBook.position.set(-3.5, deskY + 0.05, 2.3);
    decorationsGroup.add(deskBook);
    
    // Add floor rug under bookshelf area
    const rug = createRug();
    rug.position.set(0, 0.01, 0);
    decorationsGroup.add(rug);
    
    scene.add(decorationsGroup);
    decorations.push(decorationsGroup);
    console.log('✓ Bookshelf decorations added (properly positioned)');
}

// Create decorative plant (properly sized and positioned)
function createDecorativePlant() {
    const plantGroup = new THREE.Group();
    
    // Pot (larger, more realistic)
    const potGeometry = new THREE.CylinderGeometry(0.2, 0.18, 0.25, 12);
    const potMaterial = new THREE.MeshStandardMaterial({ 
        color: 0x8B4513,
        roughness: 0.7
    });
    const pot = new THREE.Mesh(potGeometry, potMaterial);
    pot.position.y = 0.125; // Center pot at y=0.125
    pot.castShadow = true;
    pot.receiveShadow = true;
    plantGroup.add(pot);
    
    // Leaves (more natural arrangement)
    const leavesMaterial = new THREE.MeshStandardMaterial({ 
        color: 0x4CAF50,
        roughness: 0.7
    });
    
    // Main stem/trunk
    const trunkGeometry = new THREE.CylinderGeometry(0.03, 0.03, 0.3, 8);
    const trunk = new THREE.Mesh(trunkGeometry, leavesMaterial);
    trunk.position.y = 0.4;
    plantGroup.add(trunk);
    
    // Leaves arranged naturally
    const leafPositions = [
        { x: 0, y: 0.5, z: 0, size: 0.2 },
        { x: 0.15, y: 0.55, z: 0.1, size: 0.18 },
        { x: -0.12, y: 0.52, z: -0.08, size: 0.19 },
        { x: 0.08, y: 0.48, z: -0.15, size: 0.17 },
        { x: -0.1, y: 0.58, z: 0.12, size: 0.21 }
    ];
    
    leafPositions.forEach(pos => {
        const leafGeometry = new THREE.ConeGeometry(pos.size * 0.4, pos.size, 8);
        const leaf = new THREE.Mesh(leafGeometry, leavesMaterial);
        leaf.position.set(pos.x, pos.y, pos.z);
        leaf.rotation.z = (Math.random() - 0.5) * 0.3;
        leaf.rotation.x = Math.random() * 0.2;
        leaf.castShadow = true;
        plantGroup.add(leaf);
    });
    
    // Adjust plant group so bottom is at y=0
    plantGroup.position.y = 0;
    
    return plantGroup;
}

// Create decorative lamp (floor lamp, properly sized)
function createDecorativeLamp() {
    const lampGroup = new THREE.Group();
    
    // Base (on floor)
    const baseGeometry = new THREE.CylinderGeometry(0.15, 0.18, 0.15, 12);
    const baseMaterial = new THREE.MeshStandardMaterial({ 
        color: 0x2C2C2C,
        metalness: 0.8,
        roughness: 0.2
    });
    const base = new THREE.Mesh(baseGeometry, baseMaterial);
    base.position.y = 0.075; // Half height
    base.castShadow = true;
    base.receiveShadow = true;
    lampGroup.add(base);
    
    // Stem (taller for floor lamp)
    const stemGeometry = new THREE.CylinderGeometry(0.04, 0.04, 1.2, 8);
    const stem = new THREE.Mesh(stemGeometry, baseMaterial);
    stem.position.y = 0.75; // Center at 0.75
    stem.castShadow = true;
    lampGroup.add(stem);
    
    // Shade (larger for floor lamp)
    const shadeGeometry = new THREE.CylinderGeometry(0.2, 0.18, 0.3, 12);
    const shadeMaterial = new THREE.MeshStandardMaterial({ 
        color: 0xFFD700,
        metalness: 0.3,
        roughness: 0.4,
        emissive: 0xFFD700,
        emissiveIntensity: 0.2
    });
    const shade = new THREE.Mesh(shadeGeometry, shadeMaterial);
    shade.position.y = 1.35;
    shade.castShadow = true;
    lampGroup.add(shade);
    
    // Light source
    const light = new THREE.PointLight(0xFFD700, 0.6, 8);
    light.position.set(0, 1.5, 0);
    light.castShadow = true;
    lampGroup.add(light);
    
    // Adjust so bottom is at y=0
    lampGroup.position.y = 0;
    
    return lampGroup;
}

// Create decorative vase
function createDecorativeVase() {
    const vaseGroup = new THREE.Group();
    
    const vaseGeometry = new THREE.CylinderGeometry(0.08, 0.06, 0.3, 12);
    const vaseMaterial = new THREE.MeshStandardMaterial({ 
        color: 0x8B4513,
        metalness: 0.4,
        roughness: 0.5
    });
    const vase = new THREE.Mesh(vaseGeometry, vaseMaterial);
    vase.position.y = 0.15;
    vase.castShadow = true;
    vaseGroup.add(vase);
    
    // Small flower
    const flowerGeometry = new THREE.SphereGeometry(0.05, 8, 8);
    const flowerMaterial = new THREE.MeshStandardMaterial({ 
        color: 0xFF69B4,
        emissive: 0xFF69B4,
        emissiveIntensity: 0.3
    });
    const flower = new THREE.Mesh(flowerGeometry, flowerMaterial);
    flower.position.y = 0.35;
    vaseGroup.add(flower);
    
    return vaseGroup;
}

// Create decorative statue
function createDecorativeStatue() {
    const statueGroup = new THREE.Group();
    
    // Base
    const baseGeometry = new THREE.CylinderGeometry(0.1, 0.1, 0.05, 8);
    const baseMaterial = new THREE.MeshStandardMaterial({ 
        color: 0x654321,
        metalness: 0.3,
        roughness: 0.6
    });
    const base = new THREE.Mesh(baseGeometry, baseMaterial);
    statueGroup.add(base);
    
    // Body (simplified)
    const bodyGeometry = new THREE.BoxGeometry(0.12, 0.25, 0.08);
    const body = new THREE.Mesh(bodyGeometry, baseMaterial);
    body.position.y = 0.175;
    statueGroup.add(body);
    
    // Head
    const headGeometry = new THREE.SphereGeometry(0.06, 12, 12);
    const head = new THREE.Mesh(headGeometry, baseMaterial);
    head.position.y = 0.35;
    statueGroup.add(head);
    
    statueGroup.castShadow = true;
    return statueGroup;
}

// Create reading glasses
function createReadingGlasses() {
    const glassesGroup = new THREE.Group();
    
    const frameMaterial = new THREE.MeshStandardMaterial({ 
        color: 0x2C2C2C,
        metalness: 0.8,
        roughness: 0.2
    });
    
    // Left lens frame
    const leftFrameGeometry = new THREE.TorusGeometry(0.08, 0.01, 8, 16);
    const leftFrame = new THREE.Mesh(leftFrameGeometry, frameMaterial);
    leftFrame.position.x = -0.09;
    glassesGroup.add(leftFrame);
    
    // Right lens frame
    const rightFrame = new THREE.Mesh(leftFrameGeometry.clone(), frameMaterial);
    rightFrame.position.x = 0.09;
    glassesGroup.add(rightFrame);
    
    // Bridge
    const bridgeGeometry = new THREE.BoxGeometry(0.02, 0.01, 0.01);
    const bridge = new THREE.Mesh(bridgeGeometry, frameMaterial);
    glassesGroup.add(bridge);
    
    // Temples (arms)
    const templeGeometry = new THREE.BoxGeometry(0.15, 0.01, 0.01);
    const leftTemple = new THREE.Mesh(templeGeometry, frameMaterial);
    leftTemple.position.set(-0.15, 0, 0);
    leftTemple.rotation.z = Math.PI / 6;
    glassesGroup.add(leftTemple);
    
    const rightTemple = new THREE.Mesh(templeGeometry, frameMaterial);
    rightTemple.position.set(0.15, 0, 0);
    rightTemple.rotation.z = -Math.PI / 6;
    glassesGroup.add(rightTemple);
    
    glassesGroup.scale.set(0.8, 0.8, 0.8);
    return glassesGroup;
}

// Create coffee cup (properly sized for desk)
function createCoffeeCup() {
    const cupGroup = new THREE.Group();
    
    // Cup body
    const cupGeometry = new THREE.CylinderGeometry(0.08, 0.08, 0.12, 12);
    const cupMaterial = new THREE.MeshStandardMaterial({ 
        color: 0xFFFFFF,
        roughness: 0.3,
        metalness: 0.1
    });
    const cup = new THREE.Mesh(cupGeometry, cupMaterial);
    cup.position.y = 0.06; // Center at half height
    cup.castShadow = true;
    cupGroup.add(cup);
    
    // Coffee inside
    const coffeeGeometry = new THREE.CylinderGeometry(0.075, 0.075, 0.1, 12);
    const coffeeMaterial = new THREE.MeshStandardMaterial({ 
        color: 0x6F4E37,
        roughness: 0.8
    });
    const coffee = new THREE.Mesh(coffeeGeometry, coffeeMaterial);
    coffee.position.y = 0.055;
    cupGroup.add(coffee);
    
    // Handle
    const handleGeometry = new THREE.TorusGeometry(0.05, 0.01, 8, 16);
    const handle = new THREE.Mesh(handleGeometry, cupMaterial);
    handle.position.set(0.09, 0.06, 0);
    handle.rotation.z = Math.PI / 2;
    cupGroup.add(handle);
    
    // Small saucer
    const saucerGeometry = new THREE.CylinderGeometry(0.12, 0.12, 0.02, 16);
    const saucer = new THREE.Mesh(saucerGeometry, cupMaterial);
    saucer.position.y = 0.01;
    saucer.receiveShadow = true;
    cupGroup.add(saucer);
    
    // Adjust so bottom is at y=0
    cupGroup.position.y = 0;
    
    return cupGroup;
}

// Create small book for desk
function createSmallDeskBook() {
    const bookGroup = new THREE.Group();
    
    const bookGeometry = new THREE.BoxGeometry(0.15, 0.2, 0.02);
    const bookMaterial = new THREE.MeshStandardMaterial({ 
        color: 0x8B4513,
        roughness: 0.5
    });
    const book = new THREE.Mesh(bookGeometry, bookMaterial);
    book.rotation.y = Math.PI / 6; // Slight angle
    book.castShadow = true;
    book.receiveShadow = true;
    bookGroup.add(book);
    
    // Adjust so bottom is at y=0
    bookGroup.position.y = 0;
    
    return bookGroup;
}

// Create rug under bookshelf area
function createRug() {
    const rugGeometry = new THREE.PlaneGeometry(10, 6);
    const rugMaterial = new THREE.MeshStandardMaterial({ 
        color: 0x8B7355,
        roughness: 0.9
    });
    const rug = new THREE.Mesh(rugGeometry, rugMaterial);
    rug.rotation.x = -Math.PI / 2; // Lay flat on floor
    rug.receiveShadow = true;
    return rug;
}

// Create simple decoration (fallback)
function createSimpleDecoration(group, type) {
    let geometry, material, position;
    
    switch(type) {
        case 'plant':
            geometry = new THREE.ConeGeometry(0.5, 1.5, 8);
            material = new THREE.MeshStandardMaterial({ color: 0x4CAF50 });
            break;
        case 'lamp':
            geometry = new THREE.CylinderGeometry(0.3, 0.3, 1.5, 8);
            material = new THREE.MeshStandardMaterial({ 
                color: 0xFFD700, 
                emissive: 0xFFD700, 
                emissiveIntensity: 0.5 
            });
            break;
        default:
            geometry = new THREE.SphereGeometry(0.5, 16, 16);
            material = new THREE.MeshStandardMaterial({ color: 0xFF6B6B });
    }
    
    const mesh = new THREE.Mesh(geometry, material);
    mesh.castShadow = true;
    mesh.receiveShadow = true;
    group.add(mesh);
}

// Setup mouse interaction
function setupMouseInteraction() {
    const raycaster = new THREE.Raycaster();
    const mouse = new THREE.Vector2();
    
    renderer.domElement.addEventListener('click', (event) => {
        mouse.x = (event.clientX / window.innerWidth) * 2 - 1;
        mouse.y = -(event.clientY / window.innerHeight) * 2 + 1;
        
        raycaster.setFromCamera(mouse, camera);
        const intersects = raycaster.intersectObjects(books, true);
        
        if (intersects.length > 0) {
            const object = intersects[0].object;
            const bookData = object.userData.bookData || object.parent.userData.bookData;
            if (bookData) {
                showBookInfo(bookData);
            }
        }
    });
}

// Show book info
function showBookInfo(bookData) {
    const panel = document.getElementById('book-info-panel');
    if (!panel) return;
    
    document.getElementById('info-book-title').textContent = bookData.title || 'Unknown';
    document.getElementById('info-book-author').textContent = bookData.author || 'Unknown Author';
    document.getElementById('info-book-link').href = `reading.php?id=${bookData.id}`;
    panel.style.display = 'block';
    selectedBook = bookData;
}

// Close book info
function closeBookInfo() {
    const panel = document.getElementById('book-info-panel');
    if (panel) {
        panel.style.display = 'none';
    }
    selectedBook = null;
}

// Toggle decorations
function toggleDecorations() {
    decorationsVisible = !decorationsVisible;
    decorations.forEach(decoration => {
        decoration.visible = decorationsVisible;
    });
    
    const btn = document.getElementById('toggle-decorations-btn');
    if (btn) {
        btn.innerHTML = decorationsVisible 
            ? '<i class="fas fa-eye-slash mr-2"></i>Ẩn trang trí'
            : '<i class="fas fa-eye mr-2"></i>Hiện trang trí';
    }
}

// Reset camera
function resetCamera() {
    if (controls && controls.target) {
        camera.position.set(0, 5, 15);
        controls.target.set(0, 2, 0);
        controls.update();
    } else {
        camera.position.set(0, 5, 15);
        camera.lookAt(0, 2, 0);
        if (window.updateCamera) window.updateCamera();
    }
}

// Toggle auto rotate
function toggleAutoRotate() {
    autoRotate = !autoRotate;
    const btn = document.getElementById('toggle-rotate-btn');
    if (btn) {
        btn.innerHTML = autoRotate
            ? '<i class="fas fa-pause mr-2"></i>Dừng xoay'
            : '<i class="fas fa-sync mr-2"></i>Tự động xoay';
    }
}

// Update rotation speed
function updateRotationSpeed(value) {
    rotationSpeed = parseFloat(value);
}

// Animation loop
function animate() {
    requestAnimationFrame(animate);
    
    if (controls && controls.update) {
        controls.update();
    }
    
    // Auto rotate camera
    if (autoRotate) {
        const time = Date.now() * 0.0005 * rotationSpeed;
        camera.position.x = Math.sin(time) * 15;
        camera.position.z = Math.cos(time) * 15;
        camera.lookAt(0, 2, 0);
    }
    
    // Subtle book animation
    books.forEach((book, index) => {
        if (book && book.rotation) {
            const time = Date.now() * 0.001;
            book.rotation.y = Math.sin(time + index) * 0.02;
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

// Make functions globally available
if (typeof window !== 'undefined') {
    window.toggleDecorations = toggleDecorations;
    window.resetCamera = resetCamera;
    window.closeBookInfo = closeBookInfo;
    window.toggleAutoRotate = toggleAutoRotate;
    window.updateRotationSpeed = updateRotationSpeed;
}

