// ============================================
// PROCEDURAL ENHANCED BOOKSHELF
// Tạo model kệ sách đẹp hơn bằng code (không cần file 3D)
// ============================================

/**
 * Tạo kệ sách procedural với chi tiết cao hơn
 * Sử dụng khi chưa có model 3D
 */
function createProceduralEnhancedBookshelf() {
    const bookshelfGroup = new THREE.Group();
    
    // Wood material với texture tốt hơn
    const woodMaterial = createWoodMaterial();
    const darkWoodMaterial = createDarkWoodMaterial();
    
    // Kích thước kệ sách
    const width = 12;
    const height = 8;
    const depth = 2;
    const shelfCount = 4;
    
    // Back panel với texture
    const backGeometry = new THREE.BoxGeometry(width, height, 0.2);
    const backPanel = new THREE.Mesh(backGeometry, darkWoodMaterial);
    backPanel.position.set(0, height / 2, -0.1);
    backPanel.castShadow = true;
    backPanel.receiveShadow = true;
    bookshelfGroup.add(backPanel);
    
    // Side panels với chi tiết
    const sideThickness = 0.3;
    const sideGeometry = new THREE.BoxGeometry(sideThickness, height, depth);
    
    // Left side với decorative edge
    const leftSide = new THREE.Mesh(sideGeometry, woodMaterial);
    leftSide.position.set(-width / 2 + sideThickness / 2, height / 2, depth / 2 - 0.1);
    leftSide.castShadow = true;
    leftSide.receiveShadow = true;
    bookshelfGroup.add(leftSide);
    
    // Right side
    const rightSide = new THREE.Mesh(sideGeometry, woodMaterial);
    rightSide.position.set(width / 2 - sideThickness / 2, height / 2, depth / 2 - 0.1);
    rightSide.castShadow = true;
    rightSide.receiveShadow = true;
    bookshelfGroup.add(rightSide);
    
    // Shelves với chi tiết
    const shelfThickness = 0.2;
    const shelfGeometry = new THREE.BoxGeometry(width - sideThickness * 2, shelfThickness, depth - 0.2);
    
    for (let i = 0; i < shelfCount; i++) {
        const shelfY = (i + 1) * (height / (shelfCount + 1));
        
        // Main shelf
        const shelf = new THREE.Mesh(shelfGeometry, woodMaterial);
        shelf.position.set(0, shelfY, depth / 2 - 0.1);
        shelf.castShadow = true;
        shelf.receiveShadow = true;
        bookshelfGroup.add(shelf);
        
        // Front edge (decorative)
        const edgeGeometry = new THREE.BoxGeometry(width - sideThickness * 2, 0.05, 0.05);
        const edgeMaterial = new THREE.MeshStandardMaterial({ color: 0x654321 });
        const frontEdge = new THREE.Mesh(edgeGeometry, edgeMaterial);
        frontEdge.position.set(0, shelfY, depth - 0.15);
        bookshelfGroup.add(frontEdge);
    }
    
    // Top decorative molding
    const topMoldingGeometry = new THREE.BoxGeometry(width, 0.3, 0.2);
    const topMolding = new THREE.Mesh(topMoldingGeometry, woodMaterial);
    topMolding.position.set(0, height, depth / 2 - 0.1);
    bookshelfGroup.add(topMolding);
    
    // Bottom base với chi tiết
    const baseGeometry = new THREE.BoxGeometry(width, 0.3, 0.3);
    const base = new THREE.Mesh(baseGeometry, woodMaterial);
    base.position.set(0, 0, depth / 2 - 0.05);
    base.castShadow = true;
    base.receiveShadow = true;
    bookshelfGroup.add(base);
    
    // Vertical supports (nếu cần)
    const supportGeometry = new THREE.BoxGeometry(0.1, height, 0.1);
    const supportPositions = [
        { x: -width / 4, z: depth / 2 - 0.1 },
        { x: width / 4, z: depth / 2 - 0.1 }
    ];
    
    supportPositions.forEach(pos => {
        const support = new THREE.Mesh(supportGeometry, darkWoodMaterial);
        support.position.set(pos.x, height / 2, pos.z);
        support.castShadow = true;
        bookshelfGroup.add(support);
    });
    
    return bookshelfGroup;
}

/**
 * Tạo wood material với texture tốt hơn
 */
function createWoodMaterial() {
    const material = new THREE.MeshStandardMaterial({
        color: 0x8B4513,
        roughness: 0.7,
        metalness: 0.1
    });
    
    // Tạo wood texture procedural
    const canvas = document.createElement('canvas');
    canvas.width = 512;
    canvas.height = 512;
    const ctx = canvas.getContext('2d');
    
    // Base wood color
    ctx.fillStyle = '#8B4513';
    ctx.fillRect(0, 0, 512, 512);
    
    // Wood grain
    ctx.strokeStyle = '#654321';
    ctx.lineWidth = 2;
    for (let i = 0; i < 30; i++) {
        ctx.beginPath();
        const y = i * 17 + Math.random() * 10;
        ctx.moveTo(0, y);
        ctx.quadraticCurveTo(256, y + Math.random() * 5 - 2.5, 512, y);
        ctx.stroke();
    }
    
    // Add some variation
    ctx.fillStyle = 'rgba(101, 67, 33, 0.3)';
    for (let i = 0; i < 10; i++) {
        ctx.fillRect(Math.random() * 512, Math.random() * 512, 20, 2);
    }
    
    const texture = new THREE.CanvasTexture(canvas);
    texture.wrapS = THREE.RepeatWrapping;
    texture.wrapT = THREE.RepeatWrapping;
    texture.repeat.set(2, 2);
    
    material.map = texture;
    material.needsUpdate = true;
    
    return material;
}

/**
 * Tạo dark wood material
 */
function createDarkWoodMaterial() {
    const material = new THREE.MeshStandardMaterial({
        color: 0x654321,
        roughness: 0.8,
        metalness: 0.1
    });
    
    // Similar texture but darker
    const canvas = document.createElement('canvas');
    canvas.width = 512;
    canvas.height = 512;
    const ctx = canvas.getContext('2d');
    
    ctx.fillStyle = '#654321';
    ctx.fillRect(0, 0, 512, 512);
    
    ctx.strokeStyle = '#4a2c1a';
    ctx.lineWidth = 2;
    for (let i = 0; i < 30; i++) {
        ctx.beginPath();
        const y = i * 17 + Math.random() * 10;
        ctx.moveTo(0, y);
        ctx.quadraticCurveTo(256, y + Math.random() * 5 - 2.5, 512, y);
        ctx.stroke();
    }
    
    const texture = new THREE.CanvasTexture(canvas);
    texture.wrapS = THREE.RepeatWrapping;
    texture.wrapT = THREE.RepeatWrapping;
    texture.repeat.set(2, 2);
    
    material.map = texture;
    material.needsUpdate = true;
    
    return material;
}

// Export functions
if (typeof window !== 'undefined') {
    window.createProceduralEnhancedBookshelf = createProceduralEnhancedBookshelf;
    window.createWoodMaterial = createWoodMaterial;
    window.createDarkWoodMaterial = createDarkWoodMaterial;
}

