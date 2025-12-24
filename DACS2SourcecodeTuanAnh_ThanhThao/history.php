<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$auth = new Auth();
$auth->requireLogin(); // Protect page
$auth->requireVerifiedEmail(); // Yêu cầu email đã được xác nhận

$currentUser = $auth->getCurrentUser();

// Get filter from URL
$filter = $_GET['filter'] ?? 'all';
$search = $_GET['search'] ?? '';

// Map filter values
$statusMap = [
    'reading' => 'reading',
    'read' => 'completed',
    'completed' => 'completed',
    'saved' => 'want_to_read',
    'want_to_read' => 'want_to_read',
    'all' => 'all'
];
$status = $statusMap[$filter] ?? 'all';

$pageTitle = 'Kho sách - BookOnline';
include __DIR__ . '/includes/header.php';
?>

    <!-- Main Content -->
    <main class="pt-24 pb-16 min-h-screen bg-gradient-to-br from-[#FFB347]/5 via-white to-[#4A7856]/5">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-7xl">
            <!-- Header Section - Improved -->
            <div class="mb-10 reveal">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                    <div class="flex-1">
                        <h1 class="text-4xl md:text-5xl font-bold mb-3">
                            <span class="animated-gradient bg-clip-text text-transparent bg-gradient-to-r from-[#FFB347] via-[#FF9500] to-[#FFB347]">
                                Sách của tôi
                            </span>
                        </h1>
                        <p class="text-gray-600 text-lg">Quản lý và theo dõi các cuốn sách bạn đã thêm vào thư viện</p>
                    </div>
                    <a href="new-books.php" class="px-6 py-3 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-xl font-semibold hover:shadow-xl hover:scale-105 transition-all duration-300 btn-modern flex items-center justify-center gap-2 whitespace-nowrap">
                        <i class="fas fa-plus"></i>
                        <span>Thêm sách</span>
                    </a>
                </div>
            </div>

            <!-- Search and Filters - Improved Layout -->
            <div class="mb-8 space-y-4">
                <!-- Search Bar - Enhanced -->
                <div class="relative group">
                    <i class="fas fa-search absolute left-5 top-1/2 -translate-y-1/2 text-gray-400 group-focus-within:text-[#FFB347] transition-colors"></i>
                    <input
                        type="text"
                        id="book-search"
                        placeholder="Tìm kiếm trong kho sách của bạn..."
                        value="<?php echo htmlspecialchars($search); ?>"
                        class="w-full pl-14 pr-5 py-4 rounded-xl glass border-2 border-gray-200 bg-white/80 backdrop-blur-sm text-gray-900 placeholder:text-gray-400 focus:border-[#FFB347] focus:outline-none focus:ring-4 focus:ring-[#FFB347]/20 transition-all shadow-sm hover:shadow-md"
                    />
                </div>

                <!-- Filter Buttons - Enhanced -->
                <div class="flex flex-wrap gap-3">
                    <a href="history.php" class="filter-btn px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300 <?php echo $filter === 'all' ? 'bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white shadow-lg scale-105' : 'glass border border-gray-200 text-gray-700 hover:bg-gray-50 hover:border-[#FFB347]/50 hover:scale-105'; ?>" data-filter="all">
                        <i class="fas fa-list mr-2"></i>Tất cả
                    </a>
                    <a href="history.php?filter=reading" class="filter-btn px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300 <?php echo $filter === 'reading' ? 'bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white shadow-lg scale-105' : 'glass border border-gray-200 text-gray-700 hover:bg-gray-50 hover:border-[#FFB347]/50 hover:scale-105'; ?>" data-filter="reading">
                        <i class="fas fa-book-open mr-2"></i>Đang đọc
                    </a>
                    <a href="history.php?filter=completed" class="filter-btn px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300 <?php echo $filter === 'completed' ? 'bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white shadow-lg scale-105' : 'glass border border-gray-200 text-gray-700 hover:bg-gray-50 hover:border-[#FFB347]/50 hover:scale-105'; ?>" data-filter="completed">
                        <i class="fas fa-check-circle mr-2"></i>Đã đọc
                    </a>
                    <a href="history.php?filter=want_to_read" class="filter-btn px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300 <?php echo $filter === 'want_to_read' ? 'bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white shadow-lg scale-105' : 'glass border border-gray-200 text-gray-700 hover:bg-gray-50 hover:border-[#FFB347]/50 hover:scale-105'; ?>" data-filter="want_to_read">
                        <i class="fas fa-bookmark mr-2"></i>Muốn đọc
                    </a>
                </div>
            </div>

                <!-- Loading State - Enhanced -->
                <div id="loading-state" class="text-center py-16">
                    <div class="inline-block animate-spin rounded-full h-16 w-16 border-4 border-[#FFB347]/20 border-t-[#FFB347] mb-6"></div>
                    <p class="text-gray-600 text-lg font-medium">Đang tải sách...</p>
                    <p class="text-gray-400 text-sm mt-2">Vui lòng đợi trong giây lát</p>
                </div>

                <!-- Error State - Enhanced -->
                <div id="error-state" class="hidden text-center py-16">
                    <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-red-100 mb-6">
                        <i class="fas fa-exclamation-triangle text-4xl text-red-500"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-2">Không thể tải dữ liệu</h3>
                    <p class="text-gray-600 mb-6">Vui lòng kiểm tra kết nối và thử lại sau</p>
                    <button onclick="loadMyBooks()" class="px-8 py-3 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-xl font-semibold hover:shadow-xl hover:scale-105 transition-all duration-300">
                        <i class="fas fa-redo mr-2"></i>Thử lại
                    </button>
                </div>

                <!-- Empty State - Enhanced -->
                <div id="empty-state" class="hidden text-center py-16">
                    <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-gradient-to-br from-[#FFB347]/20 to-[#4A7856]/20 mb-6">
                        <i class="fas fa-book-open text-5xl text-[#FFB347]"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 mb-2">Chưa có sách nào</h3>
                    <p class="text-gray-600 mb-2">Kho sách của bạn đang trống</p>
                    <p class="text-sm text-gray-500 mb-8">Khám phá <a href="new-books.php" class="text-[#FFB347] hover:text-[#FF9500] font-semibold underline">sách miễn phí</a> hoặc thêm sách mới để bắt đầu hành trình đọc sách</p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="new-books.php" class="px-8 py-3 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-xl font-semibold hover:shadow-xl hover:scale-105 transition-all duration-300 flex items-center justify-center gap-2">
                            <i class="fas fa-star"></i>
                            <span>Khám phá sách mới</span>
                        </a>
                        <a href="history.php?action=add" class="px-8 py-3 glass border-2 border-gray-200 rounded-xl font-semibold hover:bg-gray-50 hover:border-[#FFB347]/50 transition-all text-gray-700 flex items-center justify-center gap-2 hover:scale-105">
                            <i class="fas fa-plus"></i>
                            <span>Thêm sách thủ công</span>
                        </a>
                    </div>
                </div>

                <!-- Books Grid - Enhanced -->
                <div id="books-grid" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 lg:gap-8" style="display: none;">
                    <!-- Books will be loaded here dynamically -->
                </div>
        </div>
    </main>

    <!-- Add Book Modal -->
    <div id="add-book-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center">
        <div class="glass rounded-2xl p-8 max-w-2xl w-full mx-4 relative z-10 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold text-gray-900">Thêm sách mới</h3>
                <button onclick="closeAddBookModal()" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <!-- Tabs -->
            <div class="flex gap-2 mb-6 border-b border-gray-200">
                <button onclick="switchAddTab('google')" id="tab-google" class="px-4 py-2 font-medium text-[#FFB347] border-b-2 border-[#FFB347]">
                    <i class="fab fa-google mr-2"></i>Tìm qua Google Books
                </button>
                <button onclick="switchAddTab('manual')" id="tab-manual" class="px-4 py-2 font-medium text-gray-600 hover:text-[#FFB347]">
                    <i class="fas fa-edit mr-2"></i>Thêm thủ công
                </button>
            </div>
            
            <!-- Google Books Tab -->
            <div id="add-tab-google" class="space-y-4">
                <div class="relative">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input
                        type="text"
                        id="google-search-input"
                        placeholder="Tìm kiếm sách trên Google Books..."
                        class="w-full pl-12 pr-4 py-3 rounded-lg border border-gray-200 bg-white text-gray-900 placeholder:text-gray-400 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50 transition-all"
                    />
                </div>
                <button onclick="searchGoogleBooks()" class="w-full px-6 py-3 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg transition-all">
                    <i class="fas fa-search mr-2"></i>Tìm kiếm
                </button>
                
                <div id="google-books-results" class="space-y-3 max-h-96 overflow-y-auto">
                    <!-- Results will be loaded here -->
                </div>
            </div>
            
            <!-- Manual Add Tab -->
            <div id="add-tab-manual" class="hidden space-y-4">
                <form id="manual-add-form" onsubmit="addBookManually(event)">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tên sách *</label>
                            <input type="text" id="manual-title" required class="w-full px-4 py-2 rounded-lg border border-gray-200 bg-white text-gray-900 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Tác giả *</label>
                            <input type="text" id="manual-author" required class="w-full px-4 py-2 rounded-lg border border-gray-200 bg-white text-gray-900 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50 transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Mô tả</label>
                            <textarea id="manual-description" rows="3" class="w-full px-4 py-2 rounded-lg border border-gray-200 bg-white text-gray-900 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50 transition-all"></textarea>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Số trang</label>
                                <input type="number" id="manual-pages" class="w-full px-4 py-2 rounded-lg border border-gray-200 bg-white text-gray-900 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50 transition-all">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">URL ảnh bìa</label>
                                <input type="url" id="manual-cover" class="w-full px-4 py-2 rounded-lg border border-gray-200 bg-white text-gray-900 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50 transition-all">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Trạng thái</label>
                            <select id="manual-status" class="w-full px-4 py-2 rounded-lg border border-gray-200 bg-white text-gray-900 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50 transition-all">
                                <option value="want_to_read">Muốn đọc</option>
                                <option value="reading">Đang đọc</option>
                                <option value="completed">Đã đọc</option>
                            </select>
                        </div>
                        <button type="submit" class="w-full px-6 py-3 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg transition-all">
                            <i class="fas fa-plus mr-2"></i>Thêm sách
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="js/api-client.js"></script>
    <script src="js/auth.js"></script>
    <script src="js/books-api-simple.js"></script>
    <script>
        // Current filter and search
        let currentFilter = '<?php echo $status; ?>';
        let currentSearch = '<?php echo htmlspecialchars($search); ?>';
        
        // Load my books from API
        async function loadMyBooks(searchQuery = '', filterType = 'all') {
            const loadingState = document.getElementById('loading-state');
            const errorState = document.getElementById('error-state');
            const emptyState = document.getElementById('empty-state');
            const booksGrid = document.getElementById('books-grid');
            
            // Show loading
            if (loadingState) loadingState.style.display = 'block';
            if (errorState) errorState.classList.add('hidden');
            if (emptyState) emptyState.classList.add('hidden');
            if (booksGrid) booksGrid.style.display = 'none';
            
            try {
                const filters = {
                    search: searchQuery,
                    status: filterType === 'all' ? 'all' : filterType
                };
                
                const books = await window.APIClient.getBooks(filters.status, filters);
                
                console.log(`Loaded ${books ? books.length : 0} books with status: ${filters.status}`);
                if (books && books.length > 0) {
                    console.log('Books:', books);
                }
                
                if (loadingState) loadingState.style.display = 'none';
                
                if (!books || books.length === 0) {
                    if (emptyState) {
                        emptyState.classList.remove('hidden');
                    }
                    return;
                }
                
                // Display books
                if (booksGrid) {
                    booksGrid.style.display = 'grid';
                    displayMyBooks(books);
                }
                
            } catch (error) {
                console.error('Error loading books:', error);
                if (loadingState) loadingState.style.display = 'none';
                if (errorState) {
                    errorState.classList.remove('hidden');
                    // Show more detailed error message
                    const errorMsg = errorState.querySelector('p');
                    if (errorMsg) {
                        errorMsg.textContent = `Không thể tải dữ liệu sách: ${error.message || 'Lỗi không xác định'}`;
                    }
                }
            }
        }
        
        function displayMyBooks(books) {
            const booksGrid = document.getElementById('books-grid');
            if (!booksGrid) {
                console.error('❌ books-grid element not found');
                return;
            }
            
            // Filter out invalid books
            const validBooks = books.filter(book => book && book.id);
            
            if (validBooks.length === 0) {
                console.warn('⚠️ No valid books to display');
                booksGrid.innerHTML = '';
                const emptyState = document.getElementById('empty-state');
                if (emptyState) emptyState.classList.remove('hidden');
                return;
            }
            
            // Hide empty state
            const emptyState = document.getElementById('empty-state');
            if (emptyState) emptyState.classList.add('hidden');
            
            console.log(`📚 Displaying ${validBooks.length} books`);
            
            const html = validBooks.map((book, index) => {
                const coverUrl = book.cover_url || book.cover || '';
                // Ensure progress is a number and between 0-100
                let progress = parseFloat(book.progress || book.progress_percent || 0);
                progress = isNaN(progress) ? 0 : Math.max(0, Math.min(100, progress));
                const status = book.status || 'want_to_read';
                
                // Status badge with better styling
                let statusClass = '';
                let statusText = '';
                if (status === 'reading') {
                    statusClass = 'bg-blue-500';
                    statusText = 'Đang đọc';
                } else if (status === 'completed') {
                    statusClass = 'bg-green-500';
                    statusText = 'Đã đọc';
                } else {
                    statusClass = 'bg-yellow-500';
                    statusText = 'Muốn đọc';
                }
                const statusBadgeHtml = `<span class="absolute top-2 right-2 px-2.5 py-1 rounded-md text-xs ${statusClass} text-white font-semibold shadow-lg z-10">${statusText}</span>`;

                return `
                    <div class="book-card card-modern glass rounded-2xl overflow-hidden reveal relative z-10 flex flex-col h-full shadow-lg hover:shadow-2xl transition-all duration-500 bg-white group border border-gray-100 hover:border-[#FFB347]/30" 
                         style="transition-delay: ${index * 0.05}s;">
                        <a href="book-info.php?id=${escapeHtml(book.id)}" class="block flex-shrink-0 relative group/cover">
                            <div class="relative h-80 w-full overflow-hidden bg-gradient-to-br from-[#FFB347]/10 via-[#FFB347]/5 to-[#4A7856]/10 rounded-t-2xl">
                                ${coverUrl ? 
                                    `<img src="${escapeHtml(coverUrl)}" alt="${escapeHtml(book.title)}" class="h-full w-full object-cover group-hover/cover:scale-110 transition-transform duration-700 ease-out" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">` :
                                    ''
                                }
                                <div class="absolute inset-0 bg-gradient-to-br from-[#FFB347]/20 via-[#FFB347]/10 to-[#4A7856]/20 ${coverUrl ? 'hidden' : 'flex'} items-center justify-center">
                                    <i class="fas fa-book text-6xl text-[#FFB347] opacity-40"></i>
                                </div>
                                ${statusBadgeHtml}
                                <div class="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent opacity-0 group-hover/cover:opacity-100 transition-opacity duration-300"></div>
                                <div class="absolute bottom-0 left-0 right-0 p-3 bg-gradient-to-t from-black/60 to-transparent opacity-0 group-hover/cover:opacity-100 transition-opacity duration-300">
                                    <p class="text-white text-xs font-medium line-clamp-1">${escapeHtml(book.author || 'Tác giả không xác định')}</p>
                                </div>
                            </div>
                        </a>
                        <div class="p-6 space-y-4 flex-grow flex flex-col bg-white rounded-b-2xl">
                            <div class="flex-grow">
                                <h3 class="font-bold text-xl mb-2 line-clamp-2 text-gray-900 min-h-[3.5rem] leading-tight group-hover:text-[#FFB347] transition-colors duration-300">${escapeHtml(book.title)}</h3>
                                <p class="text-gray-600 text-sm line-clamp-1 mb-3 flex items-center gap-2">
                                    <i class="fas fa-user text-xs text-gray-400"></i>
                                    <span>${escapeHtml(book.author || 'Tác giả không xác định')}</span>
                                </p>
                            </div>
                            ${progress > 0 ? `
                                <div class="flex-shrink-0 pt-3 border-t border-gray-100">
                                    <div class="flex justify-between items-center text-xs text-gray-600 mb-2.5">
                                        <span class="font-semibold flex items-center gap-1.5">
                                            <i class="fas fa-chart-line text-[#FFB347]"></i>
                                            Tiến độ đọc
                                        </span>
                                        <span class="font-bold text-[#FFB347] text-sm">${Math.round(progress)}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden shadow-inner">
                                        <div class="bg-gradient-to-r from-[#FFB347] via-[#FF9500] to-[#FFB347] h-full rounded-full transition-all duration-700 shadow-sm relative overflow-hidden" style="width: ${Math.min(progress, 100)}%;">
                                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent animate-shimmer"></div>
                                        </div>
                                    </div>
                                </div>
                            ` : '<div class="flex-shrink-0 pt-3 border-t border-gray-100"></div>'}
                            ${book.rating ? `
                                <div class="flex items-center gap-2 text-[#FFB347] flex-shrink-0">
                                    ${generateStars(book.rating)}
                                    <span class="text-gray-700 text-sm font-semibold">${book.rating.toFixed(1)}</span>
                                </div>
                            ` : ''}
                            <div class="flex gap-3 flex-shrink-0 mt-auto pt-3">
                                <a href="book-info.php?id=${escapeHtml(book.id)}" class="flex-1 px-5 py-3 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-xl text-center text-sm font-semibold hover:shadow-xl transition-all hover:scale-105 active:scale-95 flex items-center justify-center gap-2">
                                    <i class="fas fa-eye"></i>
                                    <span>Chi tiết</span>
                                </a>
                                <button onclick="deleteBook('${escapeHtml(book.id)}')" class="px-4 py-3 glass border-2 border-red-200 rounded-xl text-red-600 hover:bg-red-50 hover:border-red-300 transition-all hover:scale-105 active:scale-95 flex items-center justify-center" title="Xóa sách">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            }).filter(html => html !== '').join('');
            
            if (!html || html.trim() === '') {
                console.warn('⚠️ No valid books to display');
                booksGrid.innerHTML = '';
                return;
            }
            
            booksGrid.innerHTML = html;
            console.log('✓ Books displayed successfully');
            
            // Trigger reveal animation with staggered delay
            const revealElements = booksGrid.querySelectorAll('.reveal');
            revealElements.forEach((el, idx) => {
                setTimeout(() => {
                    el.classList.add('active');
                }, idx * 50);
            });
            
            console.log('✓ Books displayed successfully:', validBooks.length, 'books');
        }
        
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Search functionality
        let searchTimeout;
        const searchInput = document.getElementById('book-search');
        
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                const query = e.target.value.trim();
                currentSearch = query;
                
                if (query.length >= 2) {
                    searchTimeout = setTimeout(() => {
                        loadMyBooks(query, currentFilter);
                    }, 500);
                } else if (query.length === 0) {
                    loadMyBooks('', currentFilter);
                }
            });
        }
        
        // Delete book
        async function deleteBook(bookId) {
            if (!confirm('Bạn có chắc muốn xóa sách này khỏi kho sách?')) {
                return;
            }
            
            try {
                await window.APIClient.deleteBook(bookId);
                alert('Đã xóa sách thành công!');
                loadMyBooks(currentSearch, currentFilter);
            } catch (error) {
                console.error('Error deleting book:', error);
                alert('Lỗi khi xóa sách: ' + error.message);
            }
        }
        
        // Add book modal functions
        function showAddBookModal() {
            const modal = document.getElementById('add-book-modal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }
        
        function closeAddBookModal() {
            const modal = document.getElementById('add-book-modal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }
        
        function switchAddTab(tab) {
            const googleTab = document.getElementById('add-tab-google');
            const manualTab = document.getElementById('add-tab-manual');
            const googleBtn = document.getElementById('tab-google');
            const manualBtn = document.getElementById('tab-manual');
            
            if (tab === 'google') {
                googleTab.classList.remove('hidden');
                manualTab.classList.add('hidden');
                googleBtn.classList.add('text-[#FFB347]', 'border-b-2', 'border-[#FFB347]');
                googleBtn.classList.remove('text-gray-600');
                manualBtn.classList.remove('text-[#FFB347]', 'border-b-2', 'border-[#FFB347]');
                manualBtn.classList.add('text-gray-600');
            } else {
                googleTab.classList.add('hidden');
                manualTab.classList.remove('hidden');
                manualBtn.classList.add('text-[#FFB347]', 'border-b-2', 'border-[#FFB347]');
                manualBtn.classList.remove('text-gray-600');
                googleBtn.classList.remove('text-[#FFB347]', 'border-b-2', 'border-[#FFB347]');
                googleBtn.classList.add('text-gray-600');
            }
        }
        
        // Search Google Books (for modal)
        async function searchGoogleBooks() {
            const query = document.getElementById('google-search-input').value.trim();
            const resultsDiv = document.getElementById('google-books-results');
            
            if (!query) {
                alert('Vui lòng nhập từ khóa tìm kiếm');
                return;
            }
            
            if (!window.BooksAPI) {
                alert('BooksAPI chưa được tải');
                return;
            }
            
            resultsDiv.innerHTML = '<div class="text-center py-4"><div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-[#FFB347]"></div></div>';
            
            try {
                const books = await window.BooksAPI.searchBooks(query, 10);
                
                if (books.length === 0) {
                    resultsDiv.innerHTML = '<p class="text-center text-gray-500 py-4">Không tìm thấy sách nào</p>';
                    return;
                }
                
                const html = books.map(book => {
                    const coverUrl = book.cover || '';
                    return `
                        <div class="flex gap-4 p-4 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                            <div class="w-16 h-24 rounded bg-gradient-to-br from-[#FFB347]/20 to-[#FFB347]/10 border border-[#FFB347]/30 flex items-center justify-center flex-shrink-0 overflow-hidden">
                                ${coverUrl ? 
                                    `<img src="${escapeHtml(coverUrl)}" alt="${escapeHtml(book.title)}" class="w-full h-full object-cover">` :
                                    `<i class="fas fa-book text-2xl text-[#FFB347]"></i>`
                                }
                            </div>
                            <div class="flex-1">
                                <h4 class="font-semibold text-gray-900 mb-1">${escapeHtml(book.title)}</h4>
                                <p class="text-sm text-gray-600 mb-2">${escapeHtml(book.author)}</p>
                                <button onclick="addBookFromGoogle('${book.id}')" class="px-4 py-2 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg text-sm font-semibold hover:shadow-lg transition-all">
                                    <i class="fas fa-plus mr-1"></i>Thêm vào kho sách
                                </button>
                            </div>
                        </div>
                    `;
                }).join('');
                
                resultsDiv.innerHTML = html;
            } catch (error) {
                console.error('Error searching Google Books:', error);
                resultsDiv.innerHTML = '<p class="text-center text-red-500 py-4">Lỗi khi tìm kiếm: ' + error.message + '</p>';
            }
        }
        
        // Add book from Google Books (for modal)
        async function addBookFromGoogle(googleBookId) {
            try {
                if (!window.BooksAPI) {
                    throw new Error('BooksAPI chưa được tải');
                }
                
                const bookDetails = await window.BooksAPI.getBookDetails(googleBookId);
                
                const bookData = {
                    id: googleBookId,
                    title: bookDetails.title,
                    author: bookDetails.authors?.join(', ') || 'Unknown',
                    description: bookDetails.description || '',
                    cover_url: bookDetails.imageLinks?.thumbnail || bookDetails.imageLinks?.smallThumbnail || '',
                    page_count: bookDetails.pageCount || 0,
                    published_date: bookDetails.publishedDate || null,
                    isbn: bookDetails.industryIdentifiers?.[0]?.identifier || '',
                    categories: bookDetails.categories || [],
                    source: 'google_books',
                    status: 'want_to_read'
                };
                
                await window.APIClient.addBook(bookData);
                
                alert('Đã thêm sách thành công!');
                closeAddBookModal();
                loadMyBooks(currentSearch, currentFilter);
            } catch (error) {
                console.error('Error adding book:', error);
                alert('Lỗi khi thêm sách: ' + error.message);
            }
        }
        
        // Add book manually
        async function addBookManually(event) {
            event.preventDefault();
            
            const bookData = {
                id: 'book_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9),
                title: document.getElementById('manual-title').value,
                author: document.getElementById('manual-author').value,
                description: document.getElementById('manual-description').value,
                cover_url: document.getElementById('manual-cover').value,
                page_count: parseInt(document.getElementById('manual-pages').value) || 0,
                source: 'manual',
                status: document.getElementById('manual-status').value
            };
            
            try {
                await window.APIClient.addBook(bookData);
                alert('Đã thêm sách thành công!');
                closeAddBookModal();
                document.getElementById('manual-add-form').reset();
                loadMyBooks(currentSearch, currentFilter);
            } catch (error) {
                console.error('Error adding book:', error);
                alert('Lỗi khi thêm sách: ' + error.message);
            }
        }
        
        // Check if action=add in URL
        document.addEventListener('DOMContentLoaded', () => {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('action') === 'add') {
                showAddBookModal();
            }
            
            // Load my books
            loadMyBooks(currentSearch, currentFilter);
        });
        
        function generateStars(rating) {
            const fullStars = Math.floor(rating);
            const hasHalfStar = rating % 1 >= 0.5;
            let stars = '';
            
            for (let i = 0; i < fullStars && i < 5; i++) {
                stars += '<i class="fas fa-star text-xs"></i>';
            }
            
            if (hasHalfStar && fullStars < 5) {
                stars += '<i class="fas fa-star-half-alt text-xs"></i>';
            }
            
            const emptyStars = 5 - Math.ceil(rating);
            for (let i = 0; i < emptyStars; i++) {
                stars += '<i class="far fa-star text-xs"></i>';
            }
            
            return stars || '<i class="far fa-star text-xs"></i>'.repeat(5);
        }
        
        // Make functions global
        window.deleteBook = deleteBook;
        window.showAddBookModal = showAddBookModal;
        window.closeAddBookModal = closeAddBookModal;
        window.switchAddTab = switchAddTab;
        window.searchGoogleBooks = searchGoogleBooks;
        window.addBookFromGoogle = addBookFromGoogle;
        window.addBookManually = addBookManually;
        window.loadMyBooks = loadMyBooks;
    </script>

    <style>
        /* Shimmer animation for progress bar */
        @keyframes shimmer {
            0% {
                transform: translateX(-100%);
            }
            100% {
                transform: translateX(100%);
            }
        }
        
        .animate-shimmer {
            animation: shimmer 2s infinite;
        }
        
        /* Enhanced card hover effects */
        .book-card {
            transform: translateY(0);
            transition: transform 0.3s ease-out, box-shadow 0.3s ease-out;
        }
        
        .book-card:hover {
            transform: translateY(-8px);
        }
        
        /* Smooth reveal animation */
        .reveal {
            opacity: 0;
            transform: translateY(20px);
            transition: opacity 0.6s ease-out, transform 0.6s ease-out;
        }
        
        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }
        
        /* Enhanced filter button transitions */
        .filter-btn {
            position: relative;
            overflow: hidden;
        }
        
        .filter-btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 179, 71, 0.1);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        .filter-btn:hover::before {
            width: 300px;
            height: 300px;
        }
    </style>

<?php include __DIR__ . '/includes/footer.php'; ?>
