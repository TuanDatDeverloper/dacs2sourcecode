// ============================================
// BOOKSHELF 3D V2 - Clean & Professional
// Phiên bản mới, sạch sẽ và chỉnh chu
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

// Model paths
const MODEL_PATHS = {
    bookshelf: {
        classic: 'assets/models/bookshelf/bookshelf-classic.glb',
        modern: 'assets/models/bookshelf/bookshelf-modern.glb'
    },
    book: {
        base: 'assets/models/books/book-base.glb'
    }
};

// Initialize scene
function init() {
    console.log('Initializing Bookshelf 3D V2...');
    
    if (typeof THREE === 'undefined') {
        console.error('THREE.js is not loaded!');
        return;
    }
    
    // Initialize ModelLoader
    if (typeof ModelLoader !== 'undefined') {
        modelLoader = new ModelLoader();
    }
    
    // Create scene
    scene = new THREE.Scene();
    scene.background = new THREE.Color(0xf5f5f0);
    scene.fog = new THREE.Fog(0xf5f5f0, 20, 50);
    
    // Create camera - adjusted for better view of books on shelf
    camera = new THREE.PerspectiveCamera(
        60,
        window.innerWidth / window.innerHeight,
        0.1,
        1000
    );
    // Camera position: angled view to see both bookshelf and desk
    camera.position.set(6, 6, 10);
    camera.lookAt(0, 2, 1); // Look at center between bookshelf and desk
    
    // Create renderer
    const container = document.getElementById('canvas-container');
    if (!container) {
        console.error('canvas-container not found!');
        return;
    }
    
    renderer = new THREE.WebGLRenderer({ 
        antialias: true,
        powerPreference: "high-performance"
    });
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;
    container.appendChild(renderer.domElement);
    
    // Setup controls
    setupControls();
    
    // Create floor
    createFloor();
    
    // Add lights
    setupLighting();
    
    // Load bookshelf (try 3D model, fallback to procedural)
    loadBookshelf();
    
    // Load books and decorations
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
    
    // Setup interactions
    setupInteractions();
    
    // Hide loading overlay
    hideLoadingOverlay();
    
    // Start animation
    animate();
    
    // Handle resize
    window.addEventListener('resize', onWindowResize);
    
    console.log('✓ Bookshelf 3D V2 initialized');
}

// Setup camera controls
function setupControls() {
    if (typeof THREE !== 'undefined' && THREE.OrbitControls) {
        controls = new THREE.OrbitControls(camera, renderer.domElement);
        controls.enableDamping = true;
        controls.dampingFactor = 0.05;
        controls.minDistance = 8;
        controls.maxDistance = 30;
        controls.maxPolarAngle = Math.PI / 2.2;
        controls.target.set(0, 2, 1); // Look at center between bookshelf and desk
        controls.update();
        console.log('✓ OrbitControls enabled');
        return;
    }
    
    // Fallback controls
    setupCustomControls();
}

function setupCustomControls() {
    let isDragging = false;
    let previousMouse = { x: 0, y: 0 };
    let cameraRotation = { x: 0.3, y: 0 };
    let cameraDistance = 18;
    
    const canvas = renderer.domElement;
    canvas.style.cursor = 'grab';
    
    canvas.addEventListener('mousedown', (e) => {
        if (e.button === 0) {
            isDragging = true;
            canvas.style.cursor = 'grabbing';
            previousMouse = { x: e.clientX, y: e.clientY };
        }
    });
    
    canvas.addEventListener('mousemove', (e) => {
        if (!isDragging) return;
        const deltaX = e.clientX - previousMouse.x;
        const deltaY = e.clientY - previousMouse.y;
        
        cameraRotation.y += deltaX * 0.005;
        cameraRotation.x += deltaY * 0.005;
        cameraRotation.x = Math.max(-Math.PI / 3, Math.min(Math.PI / 2.5, cameraRotation.x));
        
        updateCameraPosition();
        previousMouse = { x: e.clientX, y: e.clientY };
    });
    
    canvas.addEventListener('mouseup', () => {
        isDragging = false;
        canvas.style.cursor = 'grab';
    });
    
    canvas.addEventListener('wheel', (e) => {
        e.preventDefault();
        cameraDistance += e.deltaY * 0.01;
        cameraDistance = Math.max(10, Math.min(35, cameraDistance));
        updateCameraPosition();
    });
    
    function updateCameraPosition() {
        const x = Math.sin(cameraRotation.y) * Math.cos(cameraRotation.x) * cameraDistance;
        const y = Math.sin(cameraRotation.x) * cameraDistance + 6;
        const z = Math.cos(cameraRotation.y) * Math.cos(cameraRotation.x) * cameraDistance;
        camera.position.set(x, y, z);
        camera.lookAt(0, 2, 1); // Look at center between bookshelf and desk
    }
    
    window.updateCameraPosition = updateCameraPosition;
}

// Create floor
function createFloor() {
    const floorGroup = new THREE.Group();
    
    // Main floor
    const floorGeometry = new THREE.PlaneGeometry(30, 30);
    const floorMaterial = new THREE.MeshStandardMaterial({
        color: 0xd4a574,
        roughness: 0.8,
        metalness: 0.1
    });
    const floor = new THREE.Mesh(floorGeometry, floorMaterial);
    floor.rotation.x = -Math.PI / 2;
    floor.position.y = 0;
    floor.receiveShadow = true;
    floorGroup.add(floor);
    
    // Add wood texture
    const woodTexture = createWoodTexture(512, 512);
    floorMaterial.map = woodTexture;
    floorMaterial.needsUpdate = true;
    
    // Rug under bookshelf (moved to back wall position)
    const rugGeometry = new THREE.PlaneGeometry(12, 6);
    const rugMaterial = new THREE.MeshStandardMaterial({
        color: 0x8B7355,
        roughness: 0.9
    });
    const rug = new THREE.Mesh(rugGeometry, rugMaterial);
    rug.rotation.x = -Math.PI / 2;
    rug.position.set(0, 0.01, -2); // Moved back to match bookshelf position
    rug.receiveShadow = true;
    floorGroup.add(rug);
    
    scene.add(floorGroup);
}

// Create wood texture
function createWoodTexture(width = 256, height = 256) {
    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;
    const ctx = canvas.getContext('2d');
    
    // Base color
    ctx.fillStyle = '#d4a574';
    ctx.fillRect(0, 0, width, height);
    
    // Wood grain
    ctx.strokeStyle = '#b8956a';
    ctx.lineWidth = 2;
    for (let i = 0; i < 25; i++) {
        ctx.beginPath();
        const y = i * (height / 25) + Math.random() * 5;
        ctx.moveTo(0, y);
        ctx.quadraticCurveTo(width / 2, y + Math.random() * 3 - 1.5, width, y);
        ctx.stroke();
    }
    
    const texture = new THREE.CanvasTexture(canvas);
    texture.wrapS = THREE.RepeatWrapping;
    texture.wrapT = THREE.RepeatWrapping;
    texture.repeat.set(4, 4);
    return texture;
}

// Setup lighting
function setupLighting() {
    // Ambient light
    const ambientLight = new THREE.AmbientLight(0xffffff, 0.5);
    scene.add(ambientLight);
    
    // Main directional light (window light)
    const mainLight = new THREE.DirectionalLight(0xfff8e1, 0.9);
    mainLight.position.set(8, 12, 6);
    mainLight.castShadow = true;
    mainLight.shadow.mapSize.width = 2048;
    mainLight.shadow.mapSize.height = 2048;
    mainLight.shadow.camera.near = 0.5;
    mainLight.shadow.camera.far = 50;
    mainLight.shadow.camera.left = -15;
    mainLight.shadow.camera.right = 15;
    mainLight.shadow.camera.top = 15;
    mainLight.shadow.camera.bottom = -15;
    mainLight.shadow.bias = -0.0001;
    scene.add(mainLight);
    
    // Fill light
    const fillLight = new THREE.DirectionalLight(0xffffff, 0.3);
    fillLight.position.set(-6, 8, -4);
    scene.add(fillLight);
    
    // Point light for atmosphere
    const pointLight = new THREE.PointLight(0xffaa44, 0.4, 15);
    pointLight.position.set(-4, 5, 4);
    scene.add(pointLight);
}

// Load bookshelf
async function loadBookshelf() {
    // Try to load 3D model first
    if (modelLoader) {
        try {
            const modelPath = MODEL_PATHS.bookshelf.classic;
            const bookshelfModel = await modelLoader.loadModel(modelPath, {
                scale: 1,
                shadows: true,
                onProgress: (percent) => {
                    console.log(`Loading bookshelf: ${percent.toFixed(1)}%`);
                }
            });
            
            // Auto-scale
            const box = new THREE.Box3().setFromObject(bookshelfModel);
            const modelSize = box.getSize(new THREE.Vector3());
            const targetWidth = 10;
            const targetHeight = 7;
            const scaleX = targetWidth / modelSize.x;
            const scaleY = targetHeight / modelSize.y;
            const autoScale = Math.min(scaleX, scaleY) * 0.95;
            
            if (autoScale > 0.1 && autoScale < 10) {
                bookshelfModel.scale.set(autoScale, autoScale, autoScale);
                console.log(`✓ Auto-scaled: ${autoScale.toFixed(2)}x`);
            }
            
            // Recalculate box after scaling
            box.setFromObject(bookshelfModel);
            const center = box.getCenter(new THREE.Vector3());
            const min = box.min; // Bottom of bounding box
            
            // Set position: center X at origin, Z moved back (against wall), Y so that bottom (min.y) is at floor (y=0)
            // Position = -center để center ở origin, nhưng Y cần offset để đáy ở y=0
            // Move bookshelf back to simulate it being against the back wall
            bookshelfModel.position.set(-center.x, -min.y, -center.z - 2);
            
            // No rotation - keep original orientation
            bookshelfModel.rotation.set(0, 0, 0);
            
            bookshelf = bookshelfModel;
            scene.add(bookshelf);
            console.log('✓ 3D Bookshelf model loaded');
            
            // Check if model already has books (wait a bit for model to fully load)
            setTimeout(() => {
                const hasBooks = checkIfModelHasBooks(bookshelfModel);
                if (hasBooks) {
                    console.log('✓ Bookshelf model already contains books, will skip book generation');
                    // Set flag to prevent book generation
                    window.bookshelfHasBooks = true;
                    // Remove any books that were already created
                    if (books.length > 0) {
                        books.forEach(book => {
                            scene.remove(book);
                        });
                        books = [];
                        console.log('✓ Removed duplicate books');
                    }
                } else {
                    window.bookshelfHasBooks = false;
                    console.log('✓ Bookshelf model does not have books, will generate from API');
                }
            }, 100);
            return;
        } catch (error) {
            console.warn('3D model not available, using procedural:', error);
        }
    }
    
    // Use procedural bookshelf
    createProceduralBookshelf();
}

// Check if model already has books
function checkIfModelHasBooks(model) {
    let bookCount = 0;
    let meshCount = 0;
    
    model.traverse((child) => {
        if (child.isMesh) {
            meshCount++;
            const name = (child.name || '').toLowerCase();
            
            // Check by name first (most reliable)
            if (name.includes('book') || name.includes('sách') || name.includes('spine') || 
                name.includes('cover') || name.includes('page')) {
                bookCount++;
                return;
            }
            
            // Check by geometry - books are usually small rectangular boxes
            if (child.geometry) {
                try {
                    // Ensure bounding box is computed
                    if (!child.geometry.boundingBox) {
                        child.geometry.computeBoundingBox();
                    }
                    
                    const size = new THREE.Vector3();
                    child.geometry.boundingBox.getSize(size);
                    
                    // Books are typically:
                    // - Small objects (not too large)
                    // - Rectangular (width/height ratio reasonable)
                    // - Thin (depth is small compared to width/height)
                    const maxDimension = Math.max(size.x, size.y, size.z);
                    const minDimension = Math.min(size.x, size.y, size.z);
                    
                    // Book characteristics:
                    // - Max dimension < 1 unit (not huge)
                    // - Min dimension > 0.01 (not too tiny)
                    // - Depth is usually smallest (spine thickness)
                    // - Width/height ratio reasonable (not too flat or tall)
                    if (maxDimension < 1 && minDimension > 0.01) {
                        // Check if it's book-like (thin rectangular)
                        const isThin = minDimension / maxDimension < 0.3; // Thin object
                        const isRectangular = maxDimension / minDimension < 10; // Not too extreme
                        
                        if (isThin && isRectangular && size.z < 0.3) {
                            bookCount++;
                        }
                    }
                } catch (e) {
                    // Skip if geometry check fails
                }
            }
        }
    });
    
    console.log(`Model has ${meshCount} meshes, ${bookCount} book-like objects`);
    
    // If we found more than 2 book-like objects, assume model has books
    // (Lower threshold because some models might have fewer visible books)
    const hasBooks = bookCount > 2;
    if (hasBooks) {
        console.log(`✓ Detected ${bookCount} book-like objects in model, skipping book generation`);
    } else {
        console.log(`Model does not appear to have books (found ${bookCount} book-like objects)`);
    }
    return hasBooks;
}

// Create procedural bookshelf
function createProceduralBookshelf() {
    const bookshelfGroup = new THREE.Group();
    
    const woodMaterial = createWoodMaterial();
    const darkWoodMaterial = createDarkWoodMaterial();
    
    const width = 10;
    const height = 7;
    const depth = 2;
    const shelfCount = 4;
    const sideThickness = 0.25;
    
    // Back panel
    const backGeometry = new THREE.BoxGeometry(width, height, 0.15);
    const backPanel = new THREE.Mesh(backGeometry, darkWoodMaterial);
    backPanel.position.set(0, height / 2, -depth / 2 + 0.075);
    backPanel.castShadow = true;
    backPanel.receiveShadow = true;
    bookshelfGroup.add(backPanel);
    
    // Side panels
    const sideGeometry = new THREE.BoxGeometry(sideThickness, height, depth);
    const leftSide = new THREE.Mesh(sideGeometry, woodMaterial);
    leftSide.position.set(-width / 2 + sideThickness / 2, height / 2, 0);
    leftSide.castShadow = true;
    leftSide.receiveShadow = true;
    bookshelfGroup.add(leftSide);
    
    const rightSide = new THREE.Mesh(sideGeometry, woodMaterial);
    rightSide.position.set(width / 2 - sideThickness / 2, height / 2, 0);
    rightSide.castShadow = true;
    rightSide.receiveShadow = true;
    bookshelfGroup.add(rightSide);
    
    // Shelves
    const shelfThickness = 0.15;
    const shelfGeometry = new THREE.BoxGeometry(width - sideThickness * 2, shelfThickness, depth - 0.1);
    
    for (let i = 0; i < shelfCount; i++) {
        const shelfY = (i + 1) * (height / (shelfCount + 1));
        const shelf = new THREE.Mesh(shelfGeometry, woodMaterial);
        shelf.position.set(0, shelfY, 0);
        shelf.castShadow = true;
        shelf.receiveShadow = true;
        bookshelfGroup.add(shelf);
    }
    
    // Top
    const topGeometry = new THREE.BoxGeometry(width, shelfThickness, depth);
    const top = new THREE.Mesh(topGeometry, woodMaterial);
    top.position.set(0, height, 0);
    top.castShadow = true;
    bookshelfGroup.add(top);
    
    // Bottom
    const bottom = new THREE.Mesh(topGeometry, woodMaterial);
    bottom.position.set(0, 0, 0);
    bottom.castShadow = true;
    bottom.receiveShadow = true;
    bookshelfGroup.add(bottom);
    
    // Position bookshelf: center X, moved back to simulate against back wall
    bookshelfGroup.position.set(0, 0, -2);
    // No rotation - keep original orientation
    bookshelfGroup.rotation.set(0, 0, 0);
    
    bookshelf = bookshelfGroup;
    scene.add(bookshelf);
    
    // Procedural bookshelf doesn't have books, so allow book generation
    window.bookshelfHasBooks = false;
    
    console.log('✓ Procedural bookshelf created');
}

// Create wood material
function createWoodMaterial() {
    const material = new THREE.MeshStandardMaterial({
        color: 0x8B4513,
        roughness: 0.7,
        metalness: 0.1
    });
    
    const texture = createWoodTexture(256, 256);
    material.map = texture;
    material.needsUpdate = true;
    
    return material;
}

// Create dark wood material
function createDarkWoodMaterial() {
    const material = new THREE.MeshStandardMaterial({
        color: 0x654321,
        roughness: 0.8,
        metalness: 0.1
    });
    
    const texture = createWoodTexture(256, 256);
    // Darken texture
    const canvas = texture.image;
    const ctx = canvas.getContext('2d');
    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    for (let i = 0; i < imageData.data.length; i += 4) {
        imageData.data[i] *= 0.7;     // R
        imageData.data[i + 1] *= 0.7; // G
        imageData.data[i + 2] *= 0.7; // B
    }
    ctx.putImageData(imageData, 0, 0);
    texture.needsUpdate = true;
    
    material.map = texture;
    material.needsUpdate = true;
    
    return material;
}

// Load user books - DISABLED: Don't create books from API
async function loadUserBooks() {
    // Skip book generation - bookshelf model already has books
    console.log('✓ Skipping book generation - using books from 3D model');
    window.bookshelfHasBooks = true;
    return;
    
    // Old code (disabled):
    /*
    // Wait a bit for bookshelf to load and check
    await new Promise(resolve => setTimeout(resolve, 200));
    
    // Check flag first (set during bookshelf loading)
    if (window.bookshelfHasBooks === true) {
        console.log('✓ Bookshelf model has books, skipping book generation');
        return;
    }
    
    // Also check bookshelf directly if flag not set yet
    if (bookshelf) {
        const hasBooks = checkIfModelHasBooks(bookshelf);
        if (hasBooks) {
            console.log('✓ Bookshelf already has books, skipping book generation');
            window.bookshelfHasBooks = true;
            // Remove any books that were already created
            if (books.length > 0) {
                books.forEach(book => {
                    scene.remove(book);
                });
                books = [];
                console.log('✓ Removed duplicate books');
            }
            return;
        }
    }
    
    // Only load books if bookshelf doesn't have them
    try {
        if (window.APIClient && window.APIClient.getBooks) {
            const booksData = await window.APIClient.getBooks('all');
            userBooks = Array.isArray(booksData) ? booksData : [];
            console.log('Loaded', userBooks.length, 'books from API');
            if (userBooks.length > 0) {
                displayBooks();
            }
        }
    } catch (error) {
        console.error('Error loading books:', error);
    }
    */
}

// Display books
function displayBooks() {
    if (!userBooks || userBooks.length === 0) return;
    
    const maxBooks = Math.min(userBooks.length, 40);
    const booksPerShelf = 8;
    
    for (let i = 0; i < maxBooks; i++) {
        const book = userBooks[i];
        const shelfIndex = Math.floor(i / booksPerShelf);
        const bookIndex = i % booksPerShelf;
        
        createBook(book, shelfIndex, bookIndex, booksPerShelf);
    }
}

// Create book (only if bookshelf doesn't have books)
function createBook(bookData, shelfIndex, bookIndex, booksPerShelf) {
    const bookGroup = new THREE.Group();
    
    const bookWidth = 0.9;
    const bookHeight = 1.1;
    const bookDepth = 0.12;
    
    // Book color
    const colors = [0x8B4513, 0x654321, 0x4A4A4A, 0x2C2C2C, 0x8B0000, 0x006400, 0x00008B, 0x4B0082];
    const color = colors[Math.abs((bookData.title || '').charCodeAt(0)) % colors.length];
    
    const bookMaterial = new THREE.MeshStandardMaterial({
        color: color,
        roughness: 0.5
    });
    
    // Correct orientation: width (x), height (y), depth (z)
    const bookGeometry = new THREE.BoxGeometry(bookWidth, bookHeight, bookDepth);
    const bookMesh = new THREE.Mesh(bookGeometry, bookMaterial);
    bookMesh.castShadow = true;
    bookMesh.receiveShadow = true;
    
    // Try to load cover texture (silently fail on CORS)
    const coverUrl = bookData.cover_url || bookData.cover;
    if (coverUrl && (coverUrl.startsWith('http') || coverUrl.startsWith('data:'))) {
        const textureLoader = new THREE.TextureLoader();
        textureLoader.load(
            coverUrl,
            (texture) => {
                bookMaterial.map = texture;
                bookMaterial.needsUpdate = true;
            },
            undefined,
            () => {} // Silent fail on CORS
        );
    }
    
    bookGroup.add(bookMesh);
    
    // Position on shelf (correct orientation)
    const shelfY = 0.5 + shelfIndex * 1.75; // Shelves at 0.5, 2.25, 4, 5.75
    const totalWidth = booksPerShelf * bookWidth;
    const startX = -totalWidth / 2 + bookWidth / 2;
    const x = startX + bookIndex * bookWidth;
    const z = bookDepth / 2;
    
    bookGroup.position.set(x, shelfY, z);
    bookGroup.userData.bookData = bookData;
    bookGroup.userData.type = 'book';
    
    // Ensure correct orientation (no rotation)
    bookGroup.rotation.set(0, 0, 0);
    
    books.push(bookGroup);
    scene.add(bookGroup);
}

// Load decorations - only show equipped items from inventory
async function loadDecorations() {
    try {
        // Get equipped items from localStorage
        const savedEquipped = localStorage.getItem('bookOnline_equipped_items');
        let equippedItems = {};
        if (savedEquipped) {
            try {
                equippedItems = JSON.parse(savedEquipped);
                console.log('Loaded equipped items:', equippedItems);
            } catch (e) {
                console.error('Error parsing equipped items:', e);
                equippedItems = {};
            }
        }
        
        // Get inventory from API
        let inventory = [];
        if (window.APIClient && window.APIClient.getInventory) {
            try {
                const inventoryData = await window.APIClient.getInventory();
                inventory = Array.isArray(inventoryData) ? inventoryData : (inventoryData.items || []);
                console.log('Loaded inventory:', inventory.length, 'items');
            } catch (error) {
                console.error('Error loading inventory:', error);
            }
        }
        
        // Filter to only decoration and furniture category items
        const decorationItems = inventory.filter(item => {
            const category = (item.category || '').toLowerCase();
            return category === 'decoration' || category === 'furniture';
        });
        
        // Get equipped decoration/furniture IDs
        const equippedDecorationId = equippedItems['decoration'];
        const equippedFurnitureId = equippedItems['furniture'];
        
        // Add room decorations based on equipped items
        addRoomDecorations(decorationItems, equippedDecorationId, equippedFurnitureId);
        
    } catch (error) {
        console.error('Error loading decorations:', error);
        // Fallback: show default decorations if error
        addRoomDecorations([], null, null);
    }
}

// Add room decorations (standard room layout: realistic and functional)
// Only show items that are equipped in inventory
function addRoomDecorations(inventoryItems, equippedDecorationId, equippedFurnitureId) {
    const decorationsGroup = new THREE.Group();
    
    // Bookshelf is at (0, 0, -2) - already added, positioned against back wall
    // In a standard room, bookshelf would be against the back wall
    
    // === STANDARD ROOM LAYOUT ===
    // Bố cục phòng đọc sách tiêu chuẩn: kệ sách ở tường sau, bàn ở giữa phòng
    // Chỉ hiển thị những đồ vật đã được trang bị trong túi đồ
    
    // Mapping: item type -> { createFunction, defaultPosition, category }
    const itemTypeMap = {
        'plant': { 
            create: createPlant, 
            position: { x: -6, y: 0.25, z: 5 },
            category: 'decoration'
        },
        'lamp': { 
            create: createFloorLamp, 
            position: { x: 2, y: 0, z: 3.5 },
            category: 'decoration'
        },
        'desk': { 
            create: createDesk, 
            position: { x: 0, y: 0, z: 4 },
            category: 'furniture'
        },
        'coffee': { 
            create: createCoffeeCup, 
            position: { x: 0.6, y: 0.08, z: -0.4 }, // Relative to desk center
            category: 'decoration',
            requiresDesk: true // Needs desk to be present
        },
        'vase': { 
            create: createVase, 
            position: { x: 0, y: 7, z: -2 }, // On top of bookshelf
            category: 'decoration'
        },
        'statue': { 
            create: createStatue, 
            position: { x: 0, y: 7, z: -2 }, // On top of bookshelf
            category: 'decoration'
        },
        'frame': { 
            create: createPictureFrame, 
            position: { x: 6, y: 4, z: -1.9 }, // On wall
            category: 'decoration'
        },
        'clock': { 
            create: createClock, 
            position: { x: 6, y: 5, z: -1.9 }, // On wall above frame
            category: 'decoration'
        },
        'candle': { 
            create: createCandle, 
            position: { x: -0.6, y: 0.05, z: -0.3 }, // Relative to desk center
            category: 'decoration',
            requiresDesk: true
        }
    };
    
    // Desk is always present (basic furniture)
    // Bàn là đồ vật cơ bản, luôn có sẵn trong kệ sách 3D
    const deskX = 0;
    const deskZ = 4;
    const deskTopY = 1.5;
    const hasDesk = true; // Desk is always available
    
    // Always create desk (basic furniture)
    const desk = createDesk();
    desk.position.set(deskX, 0, deskZ);
    decorationsGroup.add(desk);
    console.log('✓ Desk added (basic furniture)');
    
    // Always add default desk items (for realism)
    // Đồ vật mặc định trên bàn: kính đọc sách, sách nhỏ, hộp bút
    const glasses = createGlasses();
    glasses.position.set(deskX - 0.5, deskTopY + 0.05, deskZ + 0.4);
    decorationsGroup.add(glasses);
    
    const deskBook = createSmallDeskBook();
    deskBook.position.set(deskX, deskTopY + 0.05, deskZ + 0.2);
    decorationsGroup.add(deskBook);
    
    const penHolder = createPenHolder();
    penHolder.position.set(deskX - 0.6, deskTopY + 0.1, deskZ - 0.3);
    decorationsGroup.add(penHolder);
    
    // Add equipped decoration items
    if (equippedDecorationId && inventoryItems.length > 0) {
        const equippedItem = inventoryItems.find(item => {
            const itemId = item.item_id || item.id;
            return itemId == equippedDecorationId;
        });
        
        if (equippedItem) {
            const itemType = (equippedItem.type || '').toLowerCase();
            const itemConfig = itemTypeMap[itemType];
            
            if (itemConfig) {
                // Desk is always available, so items requiring desk can always be placed
                const decoration = itemConfig.create();
                
                // Adjust position if on desk
                let position = { ...itemConfig.position };
                if (itemConfig.requiresDesk) {
                    // Position relative to desk center and top
                    position.x = deskX + position.x;
                    position.y = deskTopY + position.y;
                    position.z = deskZ + position.z;
                }
                
                decoration.position.set(position.x, position.y, position.z);
                decorationsGroup.add(decoration);
                console.log(`✓ Equipped decoration added: ${equippedItem.name} (${itemType})`);
            } else {
                console.warn(`Unknown item type: ${itemType}`);
            }
        } else {
            console.log('Equipped decoration not found in inventory');
        }
    }
    
    scene.add(decorationsGroup);
    decorations.push(decorationsGroup);
    console.log('✓ Room decorations added (only equipped items)');
}

// Create plant (properly positioned on floor)
function createPlant() {
    const group = new THREE.Group();
    
    // Pot (bottom at y=0)
    const potGeometry = new THREE.CylinderGeometry(0.2, 0.18, 0.25, 12);
    const potMaterial = new THREE.MeshStandardMaterial({ color: 0x8B4513, roughness: 0.7 });
    const pot = new THREE.Mesh(potGeometry, potMaterial);
    pot.position.y = 0.125; // Center of pot (height/2)
    pot.castShadow = true;
    pot.receiveShadow = true;
    group.add(pot);
    
    // Leaves (growing from pot)
    const leafMaterial = new THREE.MeshStandardMaterial({ color: 0x4CAF50, roughness: 0.7 });
    for (let i = 0; i < 6; i++) {
        const angle = (i / 6) * Math.PI * 2;
        const leafGeometry = new THREE.ConeGeometry(0.12, 0.35, 8);
        const leaf = new THREE.Mesh(leafGeometry, leafMaterial);
        leaf.position.set(
            Math.cos(angle) * 0.15,
            0.4 + Math.sin(i) * 0.1, // Growing from pot top
            Math.sin(angle) * 0.15
        );
        leaf.rotation.z = angle;
        leaf.castShadow = true;
        group.add(leaf);
    }
    
    // Ensure group bottom is at y=0
    group.position.y = 0;
    
    return group;
}

// Create floor lamp (properly positioned on floor)
function createFloorLamp() {
    const group = new THREE.Group();
    
    const metalMaterial = new THREE.MeshStandardMaterial({ 
        color: 0x2C2C2C, 
        metalness: 0.8, 
        roughness: 0.2 
    });
    
    // Base (on floor, y=0)
    const baseGeometry = new THREE.CylinderGeometry(0.15, 0.18, 0.15, 12);
    const base = new THREE.Mesh(baseGeometry, metalMaterial);
    base.position.y = 0.075; // Center of base
    base.castShadow = true;
    base.receiveShadow = true;
    group.add(base);
    
    // Pole
    const poleGeometry = new THREE.CylinderGeometry(0.04, 0.04, 1.5, 8);
    const pole = new THREE.Mesh(poleGeometry, metalMaterial);
    pole.position.y = 0.9; // From base top
    pole.castShadow = true;
    group.add(pole);
    
    // Shade
    const shadeGeometry = new THREE.CylinderGeometry(0.22, 0.2, 0.3, 12);
    const shadeMaterial = new THREE.MeshStandardMaterial({ 
        color: 0xFFD700,
        emissive: 0xFFD700,
        emissiveIntensity: 0.2
    });
    const shade = new THREE.Mesh(shadeGeometry, shadeMaterial);
    shade.position.y = 1.65; // Top of pole
    shade.castShadow = true;
    group.add(shade);
    
    // Light
    const light = new THREE.PointLight(0xFFD700, 0.7, 10);
    light.position.set(0, 1.8, 0);
    light.castShadow = true;
    group.add(light);
    
    // Ensure group bottom is at y=0
    group.position.y = 0;
    group.rotation.set(0, 0, 0); // No rotation
    
    return group;
}

// Create vase (for top of bookshelf)
function createVase() {
    const group = new THREE.Group();
    
    const vaseGeometry = new THREE.CylinderGeometry(0.1, 0.08, 0.3, 12);
    const vaseMaterial = new THREE.MeshStandardMaterial({ 
        color: 0x8B4513,
        metalness: 0.4,
        roughness: 0.5
    });
    const vase = new THREE.Mesh(vaseGeometry, vaseMaterial);
    vase.position.y = 0.15; // Center of vase
    vase.castShadow = true;
    group.add(vase);
    
    // Small flower
    const flowerGeometry = new THREE.SphereGeometry(0.06, 8, 8);
    const flowerMaterial = new THREE.MeshStandardMaterial({ 
        color: 0xFF69B4,
        emissive: 0xFF69B4,
        emissiveIntensity: 0.3
    });
    const flower = new THREE.Mesh(flowerGeometry, flowerMaterial);
    flower.position.y = 0.35; // Top of vase
    group.add(flower);
    
    // Ensure bottom is at y=0 (will be positioned at shelf top)
    group.position.y = 0;
    group.rotation.set(0, 0, 0); // No rotation
    
    return group;
}

// Create statue (for top of bookshelf)
function createStatue() {
    const group = new THREE.Group();
    
    const material = new THREE.MeshStandardMaterial({ 
        color: 0x654321,
        metalness: 0.3,
        roughness: 0.6
    });
    
    // Base
    const baseGeometry = new THREE.CylinderGeometry(0.1, 0.1, 0.05, 8);
    const base = new THREE.Mesh(baseGeometry, material);
    base.position.y = 0.025; // Center of base
    base.castShadow = true;
    group.add(base);
    
    // Body
    const bodyGeometry = new THREE.BoxGeometry(0.12, 0.25, 0.08);
    const body = new THREE.Mesh(bodyGeometry, material);
    body.position.y = 0.175; // On top of base
    body.castShadow = true;
    group.add(body);
    
    // Head
    const headGeometry = new THREE.SphereGeometry(0.06, 12, 12);
    const head = new THREE.Mesh(headGeometry, material);
    head.position.y = 0.35; // On top of body
    head.castShadow = true;
    group.add(head);
    
    // Ensure bottom is at y=0 (will be positioned at shelf top)
    group.position.y = 0;
    group.rotation.set(0, 0, 0); // No rotation
    
    return group;
}

// Create desk
function createDesk() {
    const group = new THREE.Group();
    
    const woodMaterial = createWoodMaterial();
    
    // Top
    const topGeometry = new THREE.BoxGeometry(2.5, 0.1, 1.2);
    const top = new THREE.Mesh(topGeometry, woodMaterial);
    top.position.y = 1.5;
    top.castShadow = true;
    top.receiveShadow = true;
    group.add(top);
    
    // Legs
    const legGeometry = new THREE.BoxGeometry(0.1, 1.5, 0.1);
    const legPositions = [
        [-1.1, 0.75, -0.5],
        [1.1, 0.75, -0.5],
        [-1.1, 0.75, 0.5],
        [1.1, 0.75, 0.5]
    ];
    
    legPositions.forEach(pos => {
        const leg = new THREE.Mesh(legGeometry, woodMaterial);
        leg.position.set(...pos);
        leg.castShadow = true;
        group.add(leg);
    });
    
    return group;
}

// Create coffee cup (for desk)
function createCoffeeCup() {
    const group = new THREE.Group();
    
    const cupMaterial = new THREE.MeshStandardMaterial({ 
        color: 0xFFFFFF,
        roughness: 0.3
    });
    
    // Saucer (bottom)
    const saucerGeometry = new THREE.CylinderGeometry(0.12, 0.12, 0.02, 16);
    const saucer = new THREE.Mesh(saucerGeometry, cupMaterial);
    saucer.position.y = 0.01; // Bottom of saucer
    saucer.receiveShadow = true;
    group.add(saucer);
    
    // Cup
    const cupGeometry = new THREE.CylinderGeometry(0.08, 0.08, 0.12, 12);
    const cup = new THREE.Mesh(cupGeometry, cupMaterial);
    cup.position.y = 0.08; // On saucer
    cup.castShadow = true;
    group.add(cup);
    
    // Coffee
    const coffeeGeometry = new THREE.CylinderGeometry(0.075, 0.075, 0.1, 12);
    const coffeeMaterial = new THREE.MeshStandardMaterial({ color: 0x6F4E37 });
    const coffee = new THREE.Mesh(coffeeGeometry, coffeeMaterial);
    coffee.position.y = 0.07; // Inside cup
    group.add(coffee);
    
    // Handle
    const handleGeometry = new THREE.TorusGeometry(0.05, 0.01, 8, 16);
    const handle = new THREE.Mesh(handleGeometry, cupMaterial);
    handle.position.set(0.09, 0.08, 0);
    handle.rotation.z = Math.PI / 2;
    group.add(handle);
    
    // Ensure bottom is at y=0 (will be positioned at desk top)
    group.position.y = 0;
    group.rotation.set(0, 0, 0); // No rotation
    
    return group;
}

// Create glasses
function createGlasses() {
    const group = new THREE.Group();
    
    const frameMaterial = new THREE.MeshStandardMaterial({ 
        color: 0x2C2C2C,
        metalness: 0.8
    });
    
    // Left lens
    const leftFrame = new THREE.Mesh(
        new THREE.TorusGeometry(0.08, 0.01, 8, 16),
        frameMaterial
    );
    leftFrame.position.x = -0.09;
    group.add(leftFrame);
    
    // Right lens
    const rightFrame = new THREE.Mesh(
        new THREE.TorusGeometry(0.08, 0.01, 8, 16),
        frameMaterial
    );
    rightFrame.position.x = 0.09;
    group.add(rightFrame);
    
    // Bridge
    const bridge = new THREE.Mesh(
        new THREE.BoxGeometry(0.02, 0.01, 0.01),
        frameMaterial
    );
    group.add(bridge);
    
    // Scale down
    group.scale.set(0.7, 0.7, 0.7);
    
    return group;
}

// Create small book for desk
function createSmallDeskBook() {
    const bookGroup = new THREE.Group();
    
    const bookGeometry = new THREE.BoxGeometry(0.12, 0.18, 0.02);
    const bookMaterial = new THREE.MeshStandardMaterial({ 
        color: 0x8B4513,
        roughness: 0.5
    });
    const book = new THREE.Mesh(bookGeometry, bookMaterial);
    book.rotation.y = Math.PI / 6; // Slight angle
    book.castShadow = true;
    book.receiveShadow = true;
    bookGroup.add(book);
    
    // Ensure bottom is at y=0 (will be positioned at desk top)
    bookGroup.position.y = 0;
    
    return bookGroup;
}

// Create pen holder for desk
function createPenHolder() {
    const group = new THREE.Group();
    
    // Base
    const baseGeometry = new THREE.CylinderGeometry(0.06, 0.06, 0.15, 12);
    const baseMaterial = new THREE.MeshStandardMaterial({ 
        color: 0x2C2C2C,
        metalness: 0.8,
        roughness: 0.2
    });
    const base = new THREE.Mesh(baseGeometry, baseMaterial);
    base.position.y = 0.075; // Center
    base.castShadow = true;
    group.add(base);
    
    // Pens (simple cylinders)
    const penMaterial = new THREE.MeshStandardMaterial({ 
        color: 0x000000,
        metalness: 0.9,
        roughness: 0.1
    });
    
    for (let i = 0; i < 3; i++) {
        const angle = (i / 3) * Math.PI * 2;
        const penGeometry = new THREE.CylinderGeometry(0.005, 0.005, 0.12, 8);
        const pen = new THREE.Mesh(penGeometry, penMaterial);
        pen.position.set(
            Math.cos(angle) * 0.03,
            0.12,
            Math.sin(angle) * 0.03
        );
        pen.castShadow = true;
        group.add(pen);
    }
    
    // Ensure bottom is at y=0
    group.position.y = 0;
    
    return group;
}

// Create picture frame (for wall decoration)
function createPictureFrame() {
    const group = new THREE.Group();
    
    const frameMaterial = new THREE.MeshStandardMaterial({ 
        color: 0xFFD700, // Gold color
        metalness: 0.6,
        roughness: 0.3
    });
    
    // Frame border
    const frameWidth = 1.2;
    const frameHeight = 1.5;
    const frameThickness = 0.05;
    
    // Top border
    const topBorder = new THREE.Mesh(
        new THREE.BoxGeometry(frameWidth, frameThickness, frameThickness),
        frameMaterial
    );
    topBorder.position.set(0, frameHeight / 2, 0);
    group.add(topBorder);
    
    // Bottom border
    const bottomBorder = new THREE.Mesh(
        new THREE.BoxGeometry(frameWidth, frameThickness, frameThickness),
        frameMaterial
    );
    bottomBorder.position.set(0, -frameHeight / 2, 0);
    group.add(bottomBorder);
    
    // Left border
    const leftBorder = new THREE.Mesh(
        new THREE.BoxGeometry(frameThickness, frameHeight, frameThickness),
        frameMaterial
    );
    leftBorder.position.set(-frameWidth / 2, 0, 0);
    group.add(leftBorder);
    
    // Right border
    const rightBorder = new THREE.Mesh(
        new THREE.BoxGeometry(frameThickness, frameHeight, frameThickness),
        frameMaterial
    );
    rightBorder.position.set(frameWidth / 2, 0, 0);
    group.add(rightBorder);
    
    // Picture (simple colored plane)
    const pictureMaterial = new THREE.MeshStandardMaterial({ 
        color: 0x4A90E2, // Blue picture
        roughness: 0.8
    });
    const picture = new THREE.Mesh(
        new THREE.PlaneGeometry(frameWidth - frameThickness * 2, frameHeight - frameThickness * 2),
        pictureMaterial
    );
    picture.position.z = -0.01; // Slightly behind frame
    group.add(picture);
    
    // Rotate to face forward (wall mount)
    group.rotation.y = Math.PI; // Face forward
    
    return group;
}

// Create clock (for wall decoration)
function createClock() {
    const group = new THREE.Group();
    
    const clockMaterial = new THREE.MeshStandardMaterial({ 
        color: 0x2C2C2C,
        metalness: 0.7,
        roughness: 0.3
    });
    
    // Clock face
    const faceGeometry = new THREE.CylinderGeometry(0.4, 0.4, 0.05, 32);
    const face = new THREE.Mesh(faceGeometry, clockMaterial);
    face.rotation.x = Math.PI / 2;
    group.add(face);
    
    // Clock face (white)
    const whiteFaceMaterial = new THREE.MeshStandardMaterial({ 
        color: 0xFFFFFF,
        roughness: 0.9
    });
    const whiteFace = new THREE.Mesh(
        new THREE.CylinderGeometry(0.38, 0.38, 0.06, 32),
        whiteFaceMaterial
    );
    whiteFace.rotation.x = Math.PI / 2;
    whiteFace.position.z = 0.01;
    group.add(whiteFace);
    
    // Hour hand
    const hourHand = new THREE.Mesh(
        new THREE.BoxGeometry(0.02, 0.15, 0.01),
        clockMaterial
    );
    hourHand.position.y = 0.075;
    group.add(hourHand);
    
    // Minute hand
    const minuteHand = new THREE.Mesh(
        new THREE.BoxGeometry(0.015, 0.25, 0.01),
        clockMaterial
    );
    minuteHand.position.y = 0.125;
    minuteHand.rotation.z = Math.PI / 3; // 2 o'clock position
    group.add(minuteHand);
    
    // Rotate to face forward (wall mount)
    group.rotation.y = Math.PI; // Face forward
    
    return group;
}

// Create candle (for desk decoration)
function createCandle() {
    const group = new THREE.Group();
    
    // Candle body
    const candleMaterial = new THREE.MeshStandardMaterial({ 
        color: 0xF5F5DC, // Beige
        roughness: 0.7
    });
    const candleGeometry = new THREE.CylinderGeometry(0.04, 0.04, 0.15, 12);
    const candle = new THREE.Mesh(candleGeometry, candleMaterial);
    candle.position.y = 0.075; // Center
    candle.castShadow = true;
    group.add(candle);
    
    // Candle wick
    const wickMaterial = new THREE.MeshStandardMaterial({ 
        color: 0x1a1a1a
    });
    const wickGeometry = new THREE.CylinderGeometry(0.005, 0.005, 0.02, 8);
    const wick = new THREE.Mesh(wickGeometry, wickMaterial);
    wick.position.y = 0.16; // Top of candle
    group.add(wick);
    
    // Flame
    const flameMaterial = new THREE.MeshStandardMaterial({ 
        color: 0xFF6B35,
        emissive: 0xFF6B35,
        emissiveIntensity: 0.5
    });
    const flameGeometry = new THREE.ConeGeometry(0.015, 0.03, 8);
    const flame = new THREE.Mesh(flameGeometry, flameMaterial);
    flame.position.y = 0.185; // Above wick
    group.add(flame);
    
    // Candle holder/base
    const holderMaterial = new THREE.MeshStandardMaterial({ 
        color: 0x8B4513,
        metalness: 0.3,
        roughness: 0.6
    });
    const holderGeometry = new THREE.CylinderGeometry(0.06, 0.05, 0.02, 12);
    const holder = new THREE.Mesh(holderGeometry, holderMaterial);
    holder.position.y = 0.01; // Bottom
    holder.castShadow = true;
    group.add(holder);
    
    // Ensure bottom is at y=0
    group.position.y = 0;
    
    return group;
}

// Setup interactions
function setupInteractions() {
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

// Reset camera - optimized view for seeing books and room layout
function resetCamera() {
    if (controls && controls.target) {
        camera.position.set(6, 6, 10); // Better angle to see bookshelf and desk
        controls.target.set(0, 2, 1); // Look at center between bookshelf and desk
        controls.update();
    } else {
        camera.position.set(6, 6, 10);
        camera.lookAt(0, 2, 1);
        if (window.updateCameraPosition) window.updateCameraPosition();
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

// Hide loading overlay
function hideLoadingOverlay() {
    const overlay = document.getElementById('loading-overlay');
    if (overlay) {
        setTimeout(() => {
            overlay.style.opacity = '0';
            overlay.style.transition = 'opacity 0.5s ease-out';
            setTimeout(() => {
                overlay.style.display = 'none';
            }, 500);
        }, 500);
    }
}

// Animation loop
function animate() {
    requestAnimationFrame(animate);
    
    if (controls && controls.update) {
        controls.update();
    }
    
    // Auto rotate
    if (autoRotate) {
        const time = Date.now() * 0.0005 * rotationSpeed;
        camera.position.x = Math.sin(time) * 18;
        camera.position.z = Math.cos(time) * 18;
        camera.lookAt(0, 2, 1); // Look at center between bookshelf and desk
    }
    
    // Subtle book animation
    books.forEach((book, index) => {
        if (book && book.rotation) {
            const time = Date.now() * 0.001;
            book.rotation.y += Math.sin(time + index) * 0.0005;
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

// Export functions
if (typeof window !== 'undefined') {
    window.toggleDecorations = toggleDecorations;
    window.resetCamera = resetCamera;
    window.closeBookInfo = closeBookInfo;
    window.toggleAutoRotate = toggleAutoRotate;
    window.updateRotationSpeed = updateRotationSpeed;
}

