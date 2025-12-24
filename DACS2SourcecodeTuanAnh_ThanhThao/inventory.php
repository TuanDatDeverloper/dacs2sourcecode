<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$auth = new Auth();
$auth->requireLogin(); // Protect page
$auth->requireVerifiedEmail(); // Yêu cầu email đã được xác nhận

$currentUser = $auth->getCurrentUser();
$pageTitle = 'Túi đồ - BookOnline';
include __DIR__ . '/includes/header.php';
?>

    <!-- Main Content -->
    <main class="pt-24 pb-12">
        <div class="container mx-auto px-6 max-w-6xl">
            <!-- Header -->
            <div class="text-center mb-8 reveal">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    <span class="animated-gradient">Túi đồ</span>
                </h1>
                <p class="text-lg text-gray-600">Quản lý và trang bị các vật phẩm bạn đã mua</p>
            </div>

            <!-- Tabs -->
            <div class="flex flex-wrap gap-2 mb-6 reveal">
                <button onclick="switchTab('theme')" class="tab-btn active px-6 py-3 rounded-lg glass text-sm font-medium hover:bg-gray-50 transition-all" data-tab="theme">
                    <i class="fas fa-palette mr-2"></i>Giao diện
                </button>
                <button onclick="switchTab('bookshelf')" class="tab-btn px-6 py-3 rounded-lg glass text-sm font-medium hover:bg-gray-50 transition-all" data-tab="bookshelf">
                    <i class="fas fa-cube mr-2"></i>Kệ sách
                </button>
                <button onclick="switchTab('decoration')" class="tab-btn px-6 py-3 rounded-lg glass text-sm font-medium hover:bg-gray-50 transition-all" data-tab="decoration">
                    <i class="fas fa-star mr-2"></i>Trang trí
                </button>
            </div>

            <!-- Loading State -->
            <div id="loading-state" class="text-center py-12" style="display: block;">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-[#FFB347]"></div>
                <p class="mt-4 text-gray-600">Đang tải túi đồ...</p>
            </div>

            <!-- Items Grid -->
            <div id="items-container" class="grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" style="display: none;">
                <!-- Items will be loaded here -->
            </div>

            <!-- Empty State -->
            <div id="empty-state" class="hidden text-center py-16">
                <i class="fas fa-box-open text-6xl text-gray-300 mb-4"></i>
                <p class="text-xl font-semibold text-gray-600 mb-2">Chưa có vật phẩm nào</p>
                <p class="text-gray-500 mb-6">Hãy đến cửa hàng để mua vật phẩm!</p>
                <a href="shop.php" class="inline-block px-6 py-3 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg transition-all">
                    <i class="fas fa-store mr-2"></i>Đến cửa hàng
                </a>
            </div>
        </div>
    </main>

    <style>
        .tab-btn.active {
            background: linear-gradient(135deg, #FFB347, #FF9500);
            color: white;
        }
        .item-card {
            transition: all 0.3s ease;
        }
        .item-card:hover {
            transform: translateY(-4px);
        }
        .equipped-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 32px;
            height: 32px;
            background: #10B981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        /* Force items container to be visible - only when it has content */
        #items-container:not(:empty) {
            display: grid !important;
            visibility: visible !important;
            opacity: 1 !important;
        }
        #loading-state {
            position: relative;
            z-index: 1;
        }
    </style>

    <script src="js/api-client.js"></script>
    <script src="js/auth.js"></script>
    <script>
        let currentTab = 'theme';
        let userInventory = [];
        let equippedItems = {};

        // Load inventory from API
        async function loadInventory() {
            const loadingEl = document.getElementById('loading-state');
            const itemsContainer = document.getElementById('items-container');
            const emptyState = document.getElementById('empty-state');
            
            try {
                // Load inventory from API
                const inventory = await window.APIClient.getInventory();
                userInventory = Array.isArray(inventory) ? inventory : (inventory.items || []);
                
                console.log('Loaded inventory:', userInventory);
                
                // Load equipped items from localStorage (or can be moved to API later)
                const savedEquipped = localStorage.getItem('bookOnline_equipped_items');
                if (savedEquipped) {
                    equippedItems = JSON.parse(savedEquipped);
                }
                
            // Hide loading, show items
            if (loadingEl) {
                loadingEl.style.display = 'none';
                loadingEl.classList.add('hidden');
            }
            displayItems();
            } catch (error) {
                console.error('Error loading inventory:', error);
                console.error('Error loading inventory:', error);
                if (loadingEl) {
                    loadingEl.innerHTML = `
                        <div class="text-center py-12">
                            <i class="fas fa-exclamation-triangle text-4xl text-red-400 mb-4"></i>
                            <p class="text-gray-600 mb-2">Không thể tải túi đồ</p>
                            <button onclick="loadInventory()" class="mt-4 px-6 py-2 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg transition-all">
                                Thử lại
                            </button>
                        </div>
                    `;
                }
            }
        }

        // Switch tab
        function switchTab(tab) {
            currentTab = tab;
            
            // Update active tab
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
                if (btn.getAttribute('data-tab') === tab) {
                    btn.classList.add('active');
                }
            });
            
            displayItems();
        }

        // Display items
        function displayItems() {
            const container = document.getElementById('items-container');
            const emptyState = document.getElementById('empty-state');
            
            if (!container || !emptyState) return;
            
            console.log('Displaying items for tab:', currentTab);
            console.log('All inventory items:', userInventory);
            
            // ALWAYS clear container first when switching tabs
            container.innerHTML = '';
            
            // Filter items by category
            const filteredItems = userInventory.filter(item => {
                const category = (item.category || '').toLowerCase();
                console.log('Checking item:', item.name, 'category:', category, 'tab:', currentTab);
                if (currentTab === 'theme') return category === 'theme';
                if (currentTab === 'bookshelf') return category === 'bookshelf';
                if (currentTab === 'decoration') return category === 'decoration';
                return false;
            });
            
            console.log('Filtered items:', filteredItems);
            console.log('Filtered items count:', filteredItems.length);
            
            if (filteredItems.length === 0) {
                container.style.display = 'none';
                container.style.visibility = 'hidden';
                emptyState.classList.remove('hidden');
                emptyState.style.display = 'block';
                return;
            }
            
            // Show container and hide empty state
            container.style.display = 'grid';
            container.style.visibility = 'visible';
            emptyState.classList.add('hidden');
            emptyState.style.display = 'none';
            
            const htmlContent = filteredItems.map(item => {
                const itemId = item.item_id || item.id;
                // Use == for type coercion when comparing IDs
                const isEquipped = equippedItems[currentTab] == itemId;
                const itemImage = item.image || item.icon_emoji || '📦';
                const itemName = item.name || 'Vật phẩm';
                const itemDescription = item.description || '';
                
                return `
                    <div class="item-card glass rounded-2xl overflow-hidden card-modern reveal relative">
                        ${isEquipped ? '<div class="equipped-badge"><i class="fas fa-check text-white text-sm"></i></div>' : ''}
                        <div class="p-6">
                            <div class="text-center mb-4">
                                <div class="text-6xl mb-2">${escapeHtml(itemImage)}</div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">${escapeHtml(itemName)}</h3>
                                <p class="text-sm text-gray-600 mb-4">${escapeHtml(itemDescription)}</p>
                            </div>
                            ${isEquipped 
                                ? '<button class="w-full px-4 py-2 bg-green-100 text-green-700 rounded-lg font-semibold cursor-default"><i class="fas fa-check mr-2"></i>Đang trang bị</button>'
                                : `<button onclick="equipItem(${itemId}, '${currentTab}')" class="w-full px-4 py-2 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg glow-hover transition-all">Trang bị</button>`
                            }
                        </div>
                    </div>
                `;
            }).join('');
            
            console.log('Rendering HTML for', filteredItems.length, 'items');
            console.log('HTML length:', htmlContent.length);
            
            // Set HTML content (container was already cleared at the start)
            container.innerHTML = htmlContent;
            
            // FIRST: Completely hide loading state
            const loadingEl = document.getElementById('loading-state');
            if (loadingEl) {
                loadingEl.style.display = 'none';
                loadingEl.style.visibility = 'hidden';
                loadingEl.style.height = '0';
                loadingEl.style.overflow = 'hidden';
                loadingEl.classList.add('hidden');
            }
            
            // Hide empty state
            emptyState.style.display = 'none';
            emptyState.style.visibility = 'hidden';
            emptyState.classList.add('hidden');
            
            // THEN: Force display container with all properties
            container.classList.remove('hidden');
            container.style.display = 'grid';
            container.style.visibility = 'visible';
            container.style.opacity = '1';
            container.style.position = 'relative';
            container.style.zIndex = '10';
            container.style.height = 'auto';
            container.style.minHeight = '200px';
            container.style.width = '100%';
            
            console.log('Items rendered to container');
            console.log('Container display:', container.style.display);
            console.log('Container visibility:', container.style.visibility);
            console.log('Container opacity:', container.style.opacity);
            console.log('Container classes:', container.className);
            console.log('Container children count:', container.children.length);
            console.log('Container offsetHeight:', container.offsetHeight);
            console.log('Container offsetWidth:', container.offsetWidth);
            
            // Activate reveal animation for items
            container.querySelectorAll('.reveal').forEach(el => {
                el.classList.add('active');
            });
            
            // Check if container is actually visible in viewport
            const rect = container.getBoundingClientRect();
            console.log('Container getBoundingClientRect:', {
                top: rect.top,
                left: rect.left,
                width: rect.width,
                height: rect.height,
                visible: rect.width > 0 && rect.height > 0
            });
            
            // Force a repaint to ensure visibility
            container.offsetHeight; // Trigger reflow
        }

        // Equip item
        function equipItem(itemId, category) {
            equippedItems[category] = itemId;
            localStorage.setItem('bookOnline_equipped_items', JSON.stringify(equippedItems));
            displayItems();
            
            // Show notification
            const item = userInventory.find(i => (i.item_id || i.id) == itemId);
            if (item) {
                alert(`Đã trang bị "${item.name}"! Vào kệ sách 3D để xem trang trí.`);
                // Optionally call API to save equipped status
                if (window.APIClient && window.APIClient.equipItem) {
                    window.APIClient.equipItem(itemId).catch(err => {
                        console.error('Error saving equipped status:', err);
                    });
                }
            }
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', () => {
            loadInventory();
        });
        
        // Make functions global
        window.switchTab = switchTab;
        window.equipItem = equipItem;
    </script>

<?php include __DIR__ . '/includes/footer.php'; ?>

