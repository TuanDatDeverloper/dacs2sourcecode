<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$auth = new Auth();
$auth->requireLogin(); // Protect page
$auth->requireVerifiedEmail(); // Yêu cầu email đã được xác nhận

$currentUser = $auth->getCurrentUser();
$pageTitle = 'Bảng điều khiển - BookOnline';
include __DIR__ . '/includes/header.php';
?>

    <!-- Main Content -->
    <main class="pt-24 pb-12">
        <div class="container mx-auto px-6">
            <!-- Header -->
            <div class="mb-8 reveal">
                <h1 class="text-3xl md:text-4xl font-bold mb-2">
                    <span class="animated-gradient">Bảng điều khiển</span>
                </h1>
                <p class="text-gray-600">Xem tổng quan hoạt động đọc sách và tương tác của bạn</p>
            </div>

            <!-- Loading State -->
            <div id="stats-loading" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-[#FFB347]"></div>
                <p class="mt-4 text-gray-600">Đang tải thống kê...</p>
            </div>

            <!-- Stats Cards -->
            <div id="stats-cards" class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8" style="display: none;">
                <div class="glass rounded-2xl p-6 card-modern reveal relative z-10">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#FFB347]/20 to-[#FFB347]/10 border border-[#FFB347]/30 flex items-center justify-center">
                            <i class="fas fa-book-open text-2xl text-[#FFB347]"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-1" id="stat-total-books">0</div>
                    <div class="text-sm text-gray-600">Tổng số sách</div>
                </div>

                <div class="glass rounded-2xl p-6 card-modern reveal relative z-10" style="transition-delay: 0.1s;">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#4A7856]/20 to-[#4A7856]/10 border border-[#4A7856]/30 flex items-center justify-center">
                            <i class="fas fa-check-circle text-2xl text-[#4A7856]"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-1" id="stat-completed-books">0</div>
                    <div class="text-sm text-gray-600">Sách đã đọc</div>
                </div>

                <div class="glass rounded-2xl p-6 card-modern reveal relative z-10" style="transition-delay: 0.2s;">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#FFB347]/20 to-[#FFB347]/10 border border-[#FFB347]/30 flex items-center justify-center">
                            <i class="fas fa-coins text-2xl text-[#FFB347]"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-1" id="stat-coins"><?php echo htmlspecialchars($currentUser['coins'] ?? 0); ?></div>
                    <div class="text-sm text-gray-600">Book Coins</div>
                </div>

                <div class="glass rounded-2xl p-6 card-modern reveal relative z-10" style="transition-delay: 0.3s;">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#FFB347]/20 to-[#FFB347]/10 border border-[#FFB347]/30 flex items-center justify-center">
                            <i class="fas fa-fire text-2xl text-[#FFB347]"></i>
                        </div>
                    </div>
                    <div class="text-3xl font-bold text-gray-900 mb-1" id="stat-streak">0</div>
                    <div class="text-sm text-gray-600">Ngày đọc liên tiếp</div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid lg:grid-cols-3 gap-8">
                <!-- Left Column - Currently Reading & Recent Books -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Currently Reading -->
                    <div class="glass rounded-2xl p-6 card-modern reveal relative z-10">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-bold text-gray-900">
                                <i class="fas fa-book-open text-[#FFB347] mr-2"></i>
                                Đang đọc
                            </h2>
                            <a href="history.php?filter=reading" class="text-sm text-[#FFB347] hover:text-[#FF9500] font-medium">
                                Xem tất cả <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>

                        <div id="currently-reading-list" class="space-y-4">
                            <div class="text-center py-8 text-gray-500">
                                <i class="fas fa-book-open text-4xl mb-4"></i>
                                <p>Chưa có sách đang đọc</p>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Books -->
                    <div class="glass rounded-2xl p-6 card-modern reveal relative z-10">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-2xl font-bold text-gray-900">
                                <i class="fas fa-history text-[#FFB347] mr-2"></i>
                                Sách gần đây
                            </h2>
                            <a href="history.php" class="text-sm text-[#FFB347] hover:text-[#FF9500] font-medium">
                                Xem tất cả <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>

                        <div id="recent-books-list" class="space-y-4">
                            <div class="text-center py-8 text-gray-500">
                                <i class="fas fa-book text-4xl mb-4"></i>
                                <p>Chưa có sách nào</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Sidebar -->
                <div class="space-y-8">
                    <!-- Quick Stats -->
                    <div class="glass rounded-2xl p-6 card-modern reveal relative z-10">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">
                            <i class="fas fa-chart-line text-[#FFB347] mr-2"></i>
                            Thống kê nhanh
                        </h2>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Sách đang đọc</span>
                                <span class="font-semibold text-gray-900" id="quick-stat-reading">0</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Sách muốn đọc</span>
                                <span class="font-semibold text-gray-900" id="quick-stat-want-to-read">0</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Trang đã đọc</span>
                                <span class="font-semibold text-gray-900" id="quick-stat-pages">0</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Quiz đã làm</span>
                                <span class="font-semibold text-gray-900" id="quick-stat-quizzes">0</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Charts Section -->
                    <div class="glass rounded-2xl p-6 card-modern reveal relative z-10">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">
                            <i class="fas fa-chart-pie text-[#FFB347] mr-2"></i>
                            Biểu đồ thống kê
                        </h2>
                        <div class="space-y-6">
                            <!-- Books by Category Chart -->
                            <div>
                                <h3 class="text-sm font-semibold text-gray-700 mb-2">Sách theo thể loại</h3>
                                <canvas id="category-chart" height="200"></canvas>
                            </div>
                            
                            <!-- Books by Month Chart -->
                            <div>
                                <h3 class="text-sm font-semibold text-gray-700 mb-2">Sách đã đọc theo tháng</h3>
                                <canvas id="monthly-chart" height="200"></canvas>
                            </div>
                            
                            <!-- Coins History Chart -->
                            <div>
                                <h3 class="text-sm font-semibold text-gray-700 mb-2">Lịch sử Book Coins</h3>
                                <canvas id="coins-chart" height="200"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="glass rounded-2xl p-6 card-modern reveal relative z-10">
                        <h2 class="text-xl font-bold text-gray-900 mb-4">
                            <i class="fas fa-bolt text-[#FFB347] mr-2"></i>
                            Thao tác nhanh
                        </h2>
                        <div class="space-y-3">
                            <a href="history.php" class="block w-full px-4 py-2 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg text-center font-semibold hover:shadow-lg transition-all">
                                <i class="fas fa-plus mr-2"></i>Thêm sách mới
                            </a>
                            <a href="quiz.php" class="block w-full px-4 py-2 border border-gray-200 rounded-lg text-gray-700 text-center font-medium hover:bg-gray-50 transition-colors">
                                <i class="fas fa-robot mr-2"></i>Làm Quiz
                            </a>
                            <a href="shop.php" class="block w-full px-4 py-2 border border-gray-200 rounded-lg text-gray-700 text-center font-medium hover:bg-gray-50 transition-colors">
                                <i class="fas fa-store mr-2"></i>Vào cửa hàng
                            </a>
                            <a href="bookshelf-3d.php" class="block w-full px-4 py-2 border border-gray-200 rounded-lg text-gray-700 text-center font-medium hover:bg-gray-50 transition-colors">
                                <i class="fas fa-cube mr-2"></i>Kệ sách 3D
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="js/api-client.js"></script>
    <script src="js/auth.js"></script>
    <script>
        // Load dashboard stats
        async function loadDashboardStats() {
            const loadingEl = document.getElementById('stats-loading');
            const statsCards = document.getElementById('stats-cards');
            
            try {
                const stats = await window.APIClient.getStats();
                
                // Update stats cards
                document.getElementById('stat-total-books').textContent = stats.books?.total || 0;
                document.getElementById('stat-completed-books').textContent = stats.books?.completed || 0;
                document.getElementById('stat-coins').textContent = stats.coins?.current || 0;
                document.getElementById('stat-streak').textContent = stats.reading?.reading_streak || 0;
                
                // Update quick stats
                document.getElementById('quick-stat-reading').textContent = stats.books?.reading || 0;
                document.getElementById('quick-stat-want-to-read').textContent = stats.books?.want_to_read || 0;
                document.getElementById('quick-stat-pages').textContent = (stats.reading?.total_pages_read || 0).toLocaleString();
                document.getElementById('quick-stat-quizzes').textContent = stats.quizzes?.total_attempts || 0;
                
                // Hide loading, show stats
                if (loadingEl) loadingEl.style.display = 'none';
                if (statsCards) statsCards.style.display = 'grid';
                
                // Load currently reading books
                await loadCurrentlyReading();
                
                // Load recent books
                await loadRecentBooks();
                
                // Load charts
                await loadCharts(stats);
                
            } catch (error) {
                console.error('Error loading stats:', error);
                
                // Check if it's because user has no books/data
                try {
                    const books = await window.APIClient.getBooks('all');
                    if (!books || books.length === 0) {
                        // User has no books - show friendly message
                        if (loadingEl) {
                            loadingEl.innerHTML = `
                                <div class="text-center py-12">
                                    <i class="fas fa-book-open text-6xl text-[#FFB347] mb-6"></i>
                                    <h2 class="text-2xl font-bold text-gray-900 mb-3">Hãy cùng khám phá sách nhé!</h2>
                                    <p class="text-gray-600 mb-6 max-w-md mx-auto">Bạn chưa có sách nào trong thư viện. Hãy bắt đầu hành trình đọc sách của bạn ngay hôm nay!</p>
                                    <a href="history.php" class="inline-block px-8 py-3 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg transition-all btn-modern">
                                        <i class="fas fa-compass mr-2"></i>Khám phá sách
                                    </a>
                                </div>
                            `;
                        }
                        return;
                    }
                } catch (e) {
                    // If error getting books, show error message
                }
                
                // Real error - show error message
                if (loadingEl) {
                    loadingEl.innerHTML = `
                        <div class="text-center py-12">
                            <i class="fas fa-exclamation-triangle text-4xl text-red-400 mb-4"></i>
                            <p class="text-gray-600 mb-2">Không thể tải thống kê. Vui lòng thử lại sau.</p>
                            <button onclick="loadDashboardStats()" class="mt-4 px-6 py-2 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg transition-all">
                                Thử lại
                            </button>
                        </div>
                    `;
                }
            }
        }
        
        // Load currently reading books
        async function loadCurrentlyReading() {
            try {
                const books = await window.APIClient.getBooks('reading');
                const container = document.getElementById('currently-reading-list');
                
                if (!books || books.length === 0) {
                    container.innerHTML = `
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-book-open text-4xl mb-4"></i>
                            <p>Chưa có sách đang đọc</p>
                            <a href="history.php" class="mt-4 inline-block px-4 py-2 text-[#FFB347] hover:text-[#FF9500] font-medium">
                                Thêm sách mới <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    `;
                    return;
                }
                
                const html = books.slice(0, 5).map(book => {
                    const progress = book.progress || book.progress_percent || 0;
                    return `
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-16 rounded bg-gradient-to-br from-[#FFB347]/20 to-[#FFB347]/10 border border-[#FFB347]/30 flex items-center justify-center flex-shrink-0 overflow-hidden">
                                ${book.cover_url ? 
                                    `<img src="${book.cover_url}" alt="${escapeHtml(book.title)}" class="w-full h-full object-cover">` :
                                    `<i class="fas fa-book text-lg text-[#FFB347]"></i>`
                                }
                            </div>
                            <div class="flex-1">
                                <a href="book-info.php?id=${book.id}" class="font-semibold text-gray-900 text-sm mb-1 hover:text-[#FFB347] transition-colors">
                                    ${escapeHtml(book.title)}
                                </a>
                                <div class="progress-bar mb-1">
                                    <div class="progress-fill" style="width: ${progress}%;"></div>
                                </div>
                                <span class="text-xs text-gray-600">${Math.round(progress)}%</span>
                            </div>
                        </div>
                    `;
                }).join('');
                
                container.innerHTML = html;
            } catch (error) {
                console.error('Error loading currently reading:', error);
            }
        }
        
        // Load recent books
        async function loadRecentBooks() {
            try {
                const books = await window.APIClient.getBooks('all');
                const container = document.getElementById('recent-books-list');
                
                if (!books || books.length === 0) {
                    container.innerHTML = `
                        <div class="text-center py-8 text-gray-500">
                            <i class="fas fa-book text-4xl mb-4"></i>
                            <p>Chưa có sách nào</p>
                            <a href="history.php" class="mt-4 inline-block px-4 py-2 text-[#FFB347] hover:text-[#FF9500] font-medium">
                                Thêm sách mới <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    `;
                    return;
                }
                
                // Sort by added_at (most recent first)
                const sortedBooks = books.sort((a, b) => {
                    const dateA = new Date(a.added_at || 0);
                    const dateB = new Date(b.added_at || 0);
                    return dateB - dateA;
                });
                
                const html = sortedBooks.slice(0, 5).map(book => {
                    return `
                        <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="w-10 h-14 rounded bg-gradient-to-br from-[#FFB347]/20 to-[#FFB347]/10 border border-[#FFB347]/30 flex items-center justify-center flex-shrink-0 overflow-hidden">
                                ${book.cover_url ? 
                                    `<img src="${book.cover_url}" alt="${escapeHtml(book.title)}" class="w-full h-full object-cover">` :
                                    `<i class="fas fa-book text-sm text-[#FFB347]"></i>`
                                }
                            </div>
                            <div class="flex-1">
                                <a href="book-info.php?id=${book.id}" class="font-semibold text-gray-900 text-sm hover:text-[#FFB347] transition-colors">
                                    ${escapeHtml(book.title)}
                                </a>
                                <p class="text-xs text-gray-600">${escapeHtml(book.author)}</p>
                            </div>
                        </div>
                    `;
                }).join('');
                
                container.innerHTML = html;
            } catch (error) {
                console.error('Error loading recent books:', error);
            }
        }
        
        // Chart instances
        let categoryChart = null;
        let monthlyChart = null;
        let coinsChart = null;
        
        // Load charts
        async function loadCharts(stats) {
            // Books by Category Chart (Pie Chart)
            const categoryCtx = document.getElementById('category-chart');
            if (categoryCtx && stats.charts?.books_by_category) {
                const categoryData = stats.charts.books_by_category;
                const labels = categoryData.map(item => {
                    // Parse categories JSON if needed
                    let category = item.category || 'Khác';
                    if (category.startsWith('[') || category.startsWith('{')) {
                        try {
                            const parsed = JSON.parse(category);
                            category = Array.isArray(parsed) ? parsed[0] : (parsed.name || 'Khác');
                        } catch (e) {
                            category = 'Khác';
                        }
                    }
                    return category;
                });
                const data = categoryData.map(item => item.count || 0);
                
                if (categoryChart) {
                    categoryChart.destroy();
                }
                
                categoryChart = new Chart(categoryCtx, {
                    type: 'pie',
                    data: {
                        labels: labels,
                        datasets: [{
                            data: data,
                            backgroundColor: [
                                '#FFB347',
                                '#4A7856',
                                '#FF6B6B',
                                '#4ECDC4',
                                '#95E1D3',
                                '#F38181',
                                '#AA96DA',
                                '#FCBAD3'
                            ]
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    boxWidth: 12,
                                    padding: 10,
                                    font: {
                                        size: 11
                                    }
                                }
                            }
                        }
                    }
                });
            }
            
            // Books by Month Chart (Line Chart)
            const monthlyCtx = document.getElementById('monthly-chart');
            if (monthlyCtx && stats.charts?.books_by_month) {
                const monthlyData = stats.charts.books_by_month;
                const labels = monthlyData.map(item => {
                    const date = new Date(item.month + '-01');
                    return date.toLocaleDateString('vi-VN', { month: 'short', year: 'numeric' });
                });
                const data = monthlyData.map(item => item.count || 0);
                
                if (monthlyChart) {
                    monthlyChart.destroy();
                }
                
                monthlyChart = new Chart(monthlyCtx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Sách đã đọc',
                            data: data,
                            borderColor: '#FFB347',
                            backgroundColor: 'rgba(255, 179, 71, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }
            
            // Coins History Chart (Line Chart)
            const coinsCtx = document.getElementById('coins-chart');
            if (coinsCtx && stats.charts?.coins_history) {
                const coinsData = stats.charts.coins_history;
                const labels = coinsData.map(item => {
                    const date = new Date(item.date);
                    return date.toLocaleDateString('vi-VN', { day: 'numeric', month: 'short' });
                });
                const data = coinsData.map(item => item.daily_coins || 0);
                
                if (coinsChart) {
                    coinsChart.destroy();
                }
                
                coinsChart = new Chart(coinsCtx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Book Coins',
                            data: data,
                            borderColor: '#4A7856',
                            backgroundColor: 'rgba(74, 120, 86, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
        }
        
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Load stats when page loads
        document.addEventListener('DOMContentLoaded', () => {
            loadDashboardStats();
        });
    </script>

<?php include __DIR__ . '/includes/footer.php'; ?>

