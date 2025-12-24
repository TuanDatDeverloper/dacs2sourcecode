// ============================================
// NEW SIMPLE BOOKSHELF 3D - THREE.JS
// Simple, clean, and interactive 3D bookshelf
// ============================================

// Scene variables
let scene, camera, renderer, controls;
let bookshelf, books = [];
let decorations = [];
let userBooks = [];
let selectedBook = null;
let decorationsVisible = true;

// Initialize Three.js scene
function init() {
    console.log('Initializing bookshelf 3D...');
    
    // Check if THREE is available
    if (typeof THREE === 'undefined') {
        console.error('THREE.js is not loaded!');
        return;
    }
    
    // Create scene
    scene = new THREE.Scene();
    scene.background = new THREE.Color(0xf0f0f0); // Light gray background
    
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
        renderer = new THREE.WebGLRenderer({ antialias: true });
        renderer.setSize(window.innerWidth, window.innerHeight);
        renderer.shadowMap.enabled = true;
        renderer.shadowMap.type = THREE.PCFSoftShadowMap;
        container.appendChild(renderer.domElement);
        console.log('✓ Renderer created and added to container');
    } catch (error) {
        console.error('Error creating renderer:', error);
        return;
    }
    
    // Setup controls - try OrbitControls first
    setupControls();
    
    // Add lights
    addLights();
    
    // Create simple bookshelf
    createSimpleBookshelf();
    
    // Load books and decorations asynchronously (don't block initialization)
    // Use requestIdleCallback for better performance
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

// Add lights
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
    scene.add(directionalLight);
    
    // Fill light
    const fillLight = new THREE.DirectionalLight(0xffffff, 0.3);
    fillLight.position.set(-5, 5, -5);
    scene.add(fillLight);
}

// Create simple, clean bookshelf
function createSimpleBookshelf() {
    const bookshelfGroup = new THREE.Group();
    
    // Wood material
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
    
    const maxBooks = Math.min(userBooks.length, 40); // Max 40 books
    const booksPerShelf = 10;
    
    for (let i = 0; i < maxBooks; i++) {
        const book = userBooks[i];
        const shelfIndex = Math.floor(i / booksPerShelf);
        const bookIndex = i % booksPerShelf;
        
        createBook(book, shelfIndex, bookIndex, booksPerShelf);
    }
}

// Create a book
function createBook(bookData, shelfIndex, bookIndex, booksPerShelf) {
    const bookGroup = new THREE.Group();
    
    // Random book color
    const colors = [0x8B4513, 0x654321, 0x4A4A4A, 0x2C2C2C, 0x8B0000, 0x006400, 0x00008B];
    const color = colors[Math.floor(Math.random() * colors.length)];
    
    const bookMaterial = new THREE.MeshStandardMaterial({
        color: color,
        roughness: 0.5
    });
    
    // Book dimensions
    const width = 0.8;
    const height = 1.2;
    const depth = 0.15;
    
    const bookGeometry = new THREE.BoxGeometry(width, height, depth);
    const bookMesh = new THREE.Mesh(bookGeometry, bookMaterial);
    bookMesh.castShadow = true;
    bookMesh.receiveShadow = true;
    
    // Position on shelf
    const shelfY = 1.6 + shelfIndex * 1.8;
    const totalWidth = booksPerShelf * width;
    const startX = -totalWidth / 2 + width / 2;
    const x = startX + bookIndex * width;
    const z = 0.9 + depth / 2;
    
    bookMesh.position.set(x, shelfY, z);
    
    // Add title texture or label (simplified)
    bookMesh.userData.bookData = bookData;
    bookMesh.userData.type = 'book';
    
    bookGroup.add(bookMesh);
    books.push(bookGroup);
    scene.add(bookGroup);
}

// Load decorations
async function loadDecorations() {
    try {
        // Get equipped decorations from localStorage
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
        
        // Load inventory to verify
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

// Create decoration
function createDecoration(item, itemId) {
    const decorationGroup = new THREE.Group();
    
    // Simple decoration based on item type
    const type = (item.type || '').toLowerCase();
    
    let geometry, material, position, scale = 1;
    
    switch(type) {
        case 'plant':
            geometry = new THREE.ConeGeometry(0.5, 1.5, 8);
            material = new THREE.MeshStandardMaterial({ color: 0x4CAF50 });
            position = { x: -5, y: 0.75, z: 3 };
            break;
        case 'lamp':
            geometry = new THREE.CylinderGeometry(0.3, 0.3, 1.5, 8);
            material = new THREE.MeshStandardMaterial({ color: 0xFFD700, emissive: 0xFFD700, emissiveIntensity: 0.5 });
            position = { x: 7, y: 1.5, z: 3 };
            break;
        case 'vase':
            geometry = new THREE.CylinderGeometry(0.4, 0.3, 1, 8);
            material = new THREE.MeshStandardMaterial({ color: 0x8B4513 });
            position = { x: 6, y: 0.5, z: 3 };
            break;
        case 'frame':
            geometry = new THREE.PlaneGeometry(1, 1.5);
            material = new THREE.MeshStandardMaterial({ color: 0x8B4513 });
            position = { x: 0, y: 6, z: -0.2 };
            break;
        default:
            geometry = new THREE.SphereGeometry(0.5, 16, 16);
            material = new THREE.MeshStandardMaterial({ color: 0xFF6B6B });
            position = { x: 0, y: 2, z: 3 };
    }
    
    const mesh = new THREE.Mesh(geometry, material);
    mesh.position.set(position.x, position.y, position.z);
    mesh.scale.set(scale, scale, scale);
    mesh.castShadow = true;
    mesh.receiveShadow = true;
    
    decorationGroup.add(mesh);
    decorationGroup.userData.type = 'decoration';
    decorationGroup.userData.itemId = itemId;
    
    decorations.push(decorationGroup);
    scene.add(decorationGroup);
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

// Animation loop
function animate() {
    requestAnimationFrame(animate);
    
    if (controls && controls.update) {
        controls.update();
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
}

