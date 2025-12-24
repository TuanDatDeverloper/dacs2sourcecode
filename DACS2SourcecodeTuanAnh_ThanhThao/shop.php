<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$auth = new Auth();
$auth->requireLogin(); // Protect page
$auth->requireVerifiedEmail(); // Yêu cầu email đã được xác nhận

$currentUser = $auth->getCurrentUser();
$pageTitle = 'Cửa hàng - BookOnline';
include __DIR__ . '/includes/header.php';
?>

    <!-- Main Content -->
    <main class="pt-24 pb-12">
        <div class="container mx-auto px-6">
            <!-- Header -->
            <div class="text-center mb-8 reveal">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    <span class="animated-gradient">Cửa hàng ảo</span>
                </h1>
                <p class="text-lg text-gray-600">Sử dụng Book Coins để mua vật phẩm trang trí cho kệ sách 3D của bạn!</p>
            </div>

            <!-- Coins Display -->
            <div class="glass rounded-2xl p-6 card-modern mb-8 text-center reveal">
                <div class="flex items-center justify-center gap-4">
                    <i class="fas fa-coins text-5xl text-[#FFB347]"></i>
                    <div>
                        <p class="text-sm text-gray-600 mb-1">Số dư Book Coins</p>
                        <p id="coins-balance" class="text-4xl font-bold gradient-text"><?php echo htmlspecialchars($currentUser['coins'] ?? 0); ?></p>
                    </div>
                </div>
                <p class="text-sm text-gray-600 mt-4">
                    <i class="fas fa-info-circle mr-1"></i>
                    Kiếm thêm coins bằng cách hoàn thành sách và làm quiz!
                </p>
            </div>

            <!-- Filter Tabs -->
            <div class="flex flex-wrap gap-2 mb-6 reveal">
                <button onclick="filterCategory('all')" class="filter-tab active px-4 py-2 rounded-lg glass text-sm font-medium hover:bg-gray-50 transition-all" data-category="all">
                    Tất cả
                </button>
                <button onclick="filterCategory('decoration')" class="filter-tab px-4 py-2 rounded-lg glass text-sm font-medium hover:bg-gray-50 transition-all" data-category="decoration">
                    <i class="fas fa-star mr-2"></i>Trang trí
                </button>
                <button onclick="filterCategory('theme')" class="filter-tab px-4 py-2 rounded-lg glass text-sm font-medium hover:bg-gray-50 transition-all" data-category="theme">
                    <i class="fas fa-palette mr-2"></i>Giao diện
                </button>
                <button onclick="filterCategory('bookshelf')" class="filter-tab px-4 py-2 rounded-lg glass text-sm font-medium hover:bg-gray-50 transition-all" data-category="bookshelf">
                    <i class="fas fa-cube mr-2"></i>Kệ sách
                </button>
                <button onclick="filterCategory('badge')" class="filter-tab px-4 py-2 rounded-lg glass text-sm font-medium hover:bg-gray-50 transition-all" data-category="badge">
                    <i class="fas fa-award mr-2"></i>Huy hiệu
                </button>
            </div>

            <!-- Loading State -->
            <div id="loading-state" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-[#FFB347]"></div>
                <p class="mt-4 text-gray-600">Đang tải cửa hàng...</p>
            </div>

            <!-- Shop Items Grid -->
            <div id="shop-items" class="grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" style="display: none;">
                <!-- Items will be loaded here -->
            </div>
        </div>
    </main>

    <!-- Purchase Modal -->
    <div id="purchase-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center">
        <div class="glass rounded-2xl p-8 max-w-md w-full mx-4 relative z-10">
            <div class="text-center mb-6">
                <div class="w-20 h-20 rounded-xl bg-gradient-to-br from-[#FFB347]/20 to-[#FFB347]/10 border border-[#FFB347]/30 flex items-center justify-center mx-auto mb-4">
                    <i id="modal-item-icon" class="fas fa-star text-4xl text-[#FFB347]"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-2" id="modal-item-name">Tên vật phẩm</h3>
                <p class="text-gray-600 mb-4" id="modal-item-description">Mô tả vật phẩm</p>
                <div class="flex items-center justify-center gap-2 text-xl font-bold text-[#FFB347]">
                    <i class="fas fa-coins"></i>
                    <span id="modal-item-price">0</span> Coins
                </div>
            </div>
            <div class="flex flex-col gap-3">
                <button onclick="confirmPurchase()" class="w-full px-6 py-3 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg glow-hover transition-all">
                    Xác nhận mua
                </button>
                <button onclick="closePurchaseModal()" class="w-full px-6 py-2 text-gray-600 hover:text-gray-900 transition-colors">
                    Hủy
                </button>
            </div>
        </div>
    </div>

    <script src="js/api-client.js"></script>
    <script src="js/auth.js"></script>
    <script>
        let shopItems = [];
        let userInventory = [];
        let selectedItem = null;
        let currentCategory = 'all';

        // Load shop items from API
        async function loadShopItems() {
            const loadingEl = document.getElementById('loading-state');
            const itemsGrid = document.getElementById('shop-items');
            
            try {
                // Load shop items
                const items = await window.APIClient.getShopItems();
                shopItems = items.items || items || [];
                
                // Load user inventory
                const inventory = await window.APIClient.getInventory();
                userInventory = inventory.items || inventory || [];
                
                // Update coins balance
                await updateCoinsBalance();
                
                // Hide loading, show grid
                if (loadingEl) loadingEl.style.display = 'none';
                if (itemsGrid) {
                    itemsGrid.style.display = 'grid';
                    displayShopItems();
                }
            } catch (error) {
                console.error('Error loading shop:', error);
                if (loadingEl) {
                    loadingEl.innerHTML = `
                        <div class="text-center py-12">
                            <i class="fas fa-exclamation-triangle text-4xl text-red-400 mb-4"></i>
                            <p class="text-gray-600 mb-2">Không thể tải cửa hàng</p>
                            <button onclick="loadShopItems()" class="mt-4 px-6 py-2 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg transition-all">
                                Thử lại
                            </button>
                        </div>
                    `;
                }
            }
        }

        // Display shop items
        function displayShopItems() {
            const container = document.getElementById('shop-items');
            if (!container) return;
            
            const filteredItems = currentCategory === 'all' 
                ? shopItems 
                : shopItems.filter(item => item.category === currentCategory);
            
            if (filteredItems.length === 0) {
                container.innerHTML = '<p class="text-center text-gray-500 col-span-full py-12">Không có vật phẩm nào trong danh mục này</p>';
                return;
            }
            
            const ownedItemIds = userInventory.map(item => item.item_id || item.id);
            
            container.innerHTML = filteredItems.map(item => {
                const isOwned = ownedItemIds.includes(item.id);
                const itemIcon = item.icon || 'fa-star';
                const itemImage = item.image || item.icon_emoji || '📦';
                
                return `
                    <div class="bg-white rounded-2xl overflow-hidden shadow-lg border border-gray-200 hover:shadow-xl transition-shadow">
                        <div class="p-6">
                            <div class="text-center mb-4">
                                <div class="text-6xl mb-2">${escapeHtml(itemImage)}</div>
                                <h3 class="text-xl font-bold text-gray-900 mb-2">${escapeHtml(item.name)}</h3>
                                <p class="text-sm text-gray-600 mb-4">${escapeHtml(item.description || '')}</p>
                            </div>
                            <div class="flex items-center justify-center gap-2 mb-4">
                                <i class="fas fa-coins text-[#FFB347]"></i>
                                <span class="text-lg font-bold text-[#FFB347]">${item.price || 0} Coins</span>
                            </div>
                            ${isOwned 
                                ? '<button class="w-full px-4 py-2 bg-green-100 text-green-700 rounded-lg font-semibold cursor-default"><i class="fas fa-check mr-2"></i>Đã sở hữu</button>'
                                : `<button onclick="openPurchaseModal(${item.id})" class="w-full px-4 py-2 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg transition-all">Mua ngay</button>`
                            }
                        </div>
                    </div>
                `;
            }).join('');
        }

        // Filter by category
        function filterCategory(category) {
            currentCategory = category;
            
            // Update active tab
            document.querySelectorAll('.filter-tab').forEach(tab => {
                tab.classList.remove('active');
                if (tab.getAttribute('data-category') === category) {
                    tab.classList.add('active');
                }
            });
            
            displayShopItems();
        }

        // Open purchase modal
        function openPurchaseModal(itemId) {
            const item = shopItems.find(i => i.id === itemId);
            if (!item) return;
            
            selectedItem = item;
            document.getElementById('modal-item-name').textContent = item.name;
            document.getElementById('modal-item-description').textContent = item.description || '';
            document.getElementById('modal-item-price').textContent = item.price || 0;
            
            const iconClass = item.icon || 'fa-star';
            document.getElementById('modal-item-icon').className = `fas ${iconClass} text-4xl text-[#FFB347]`;
            
            const modal = document.getElementById('purchase-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        // Close purchase modal
        function closePurchaseModal() {
            const modal = document.getElementById('purchase-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            selectedItem = null;
        }

        // Confirm purchase
        async function confirmPurchase() {
            if (!selectedItem) return;
            
            try {
                const response = await window.APIClient.purchaseItem(selectedItem.id);
                
                if (!response.success) {
                    throw new Error(response.message || 'Không thể mua vật phẩm');
                }
                
                // Update coins balance
                await updateCoinsBalance();
                
                // Reload inventory
                const inventory = await window.APIClient.getInventory();
                userInventory = inventory.items || inventory || [];
                
                // Show success
                alert(`Mua thành công "${selectedItem.name}"! Vật phẩm đã được thêm vào kho của bạn. Vào kệ sách 3D để xem!`);
                
                closePurchaseModal();
                displayShopItems();
            } catch (error) {
                console.error('Error purchasing item:', error);
                alert('Lỗi khi mua vật phẩm: ' + error.message);
            }
        }

        // Update coins balance
        async function updateCoinsBalance() {
            try {
                const authCheck = await window.APIClient.checkAuth();
                if (authCheck.logged_in && authCheck.user) {
                    const coins = authCheck.user.coins || 0;
                    document.getElementById('coins-balance').textContent = coins;
                    
                    // Update in Auth
                    window.Auth.setUser(authCheck.user);
                }
            } catch (error) {
                console.error('Error updating coins:', error);
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
            loadShopItems();
        });
        
        // Make functions global
        window.filterCategory = filterCategory;
        window.openPurchaseModal = openPurchaseModal;
        window.closePurchaseModal = closePurchaseModal;
        window.confirmPurchase = confirmPurchase;
    </script>

<?php include __DIR__ . '/includes/footer.php'; ?>

