// ============================================
// BOOKSHELF 3D V2 - IMPROVED LAYOUT
// Bố cục được cải thiện và tối ưu hóa
// ============================================

// Copy toàn bộ từ bookshelf-3d-v2.js và chỉ sửa phần addRoomDecorations

// Improved room decorations layout
function addRoomDecorations() {
    const decorationsGroup = new THREE.Group();
    
    // Bookshelf is at center (0, 0, 0) - already added
    
    // === LAYOUT IMPROVEMENTS ===
    // Khoảng cách hợp lý hơn giữa các vật
    
    // 1. Plant on floor, left side of bookshelf
    // Khoảng cách: 5 units từ center (đủ xa để không che kệ sách)
    const plant = createPlant();
    plant.position.set(-5.5, 0.25, 0); // Left of bookshelf
    decorationsGroup.add(plant);
    
    // 2. Floor lamp, right side of bookshelf
    // Khoảng cách: 5 units từ center (đối xứng với cây)
    const lamp = createFloorLamp();
    lamp.position.set(5.5, 0, 0); // Right of bookshelf
    decorationsGroup.add(lamp);
    
    // 3. Desk far right, thụt lùi
    // Vị trí: cách đèn 1 bàn (2.5 units), thụt lùi về sau 1 bàn (2.5 units)
    // Desk width = 2.5, center = width/2 = 1.25
    const desk = createDesk();
    const deskX = 5.5 + 1.25 + 2.5; // Right of lamp + half desk + gap
    const deskZ = 2.5; // Thụt lùi về sau
    desk.position.set(deskX, 0, deskZ);
    decorationsGroup.add(desk);
    
    // 4. Items on desk (desk top is at y = 1.5)
    const deskTopY = 1.5;
    
    // Coffee cup - center-right of desk
    const coffee = createCoffeeCup();
    coffee.position.set(deskX + 0.4, deskTopY + 0.08, deskZ + 0.15);
    decorationsGroup.add(coffee);
    
    // Reading glasses - center-left of desk
    const glasses = createGlasses();
    glasses.position.set(deskX - 0.4, deskTopY + 0.05, deskZ + 0.1);
    decorationsGroup.add(glasses);
    
    // Small book - front of desk
    const deskBook = createSmallDeskBook();
    deskBook.position.set(deskX, deskTopY + 0.05, deskZ + 0.3);
    decorationsGroup.add(deskBook);
    
    // Pen holder - back-left of desk
    const penHolder = createPenHolder();
    penHolder.position.set(deskX - 0.5, deskTopY + 0.1, deskZ - 0.3);
    decorationsGroup.add(penHolder);
    
    scene.add(decorationsGroup);
    decorations.push(decorationsGroup);
    console.log('✓ Room decorations added (improved layout)');
}

