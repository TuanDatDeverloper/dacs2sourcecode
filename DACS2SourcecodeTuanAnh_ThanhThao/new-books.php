<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$auth = new Auth();
$auth->requireLogin(); // Protect page
$auth->requireVerifiedEmail(); // Yêu cầu email đã được xác nhận

$currentUser = $auth->getCurrentUser();

// Get filter from URL
$category = $_GET['category'] ?? 'all';
$search = $_GET['search'] ?? '';
$special = $_GET['special'] ?? '';

$pageTitle = 'Sách mới - BookOnline';
include __DIR__ . '/includes/header.php';
?>

    <!-- Main Content -->
    <main class="pt-24 pb-12">
        <div class="container mx-auto px-6">
            <!-- Header -->
            <div class="mb-8 reveal">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h1 class="text-3xl md:text-4xl font-bold mb-2">
                            <span class="animated-gradient">Sách mới</span>
                        </h1>
                        <p class="text-gray-600">Khám phá hàng nghìn cuốn sách miễn phí từ Google Books</p>
                    </div>
                    <button onclick="showUploadModal()" class="px-6 py-3 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg glow-hover transition-all btn-modern">
                        <i class="fas fa-upload mr-2"></i>Tải sách lên
                    </button>
                </div>
            </div>
            
            <!-- Upload Book Modal -->
            <div id="upload-modal" class="fixed inset-0 bg-black/50 z-50 hidden items-center justify-center p-4">
                <div class="glass rounded-2xl p-8 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">Tải sách lên</h2>
                        <button onclick="hideUploadModal()" class="w-10 h-10 rounded-lg glass flex items-center justify-center hover:bg-gray-50 transition-colors text-gray-700">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    
                    <form id="upload-book-form" enctype="multipart/form-data" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-file mr-2"></i>File sách (PDF, EPUB, TXT, HTML)
                            </label>
                            <input type="file" name="book_file" id="book_file" accept=".pdf,.epub,.txt,.html,.htm" required
                                class="w-full px-4 py-2 rounded-lg glass border border-gray-200 bg-white text-gray-900 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50 transition-all">
                            <p class="text-xs text-gray-500 mt-1">Tối đa 50MB. Hỗ trợ: PDF, EPUB, TXT, HTML</p>
                        </div>
                        
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-book mr-2"></i>Tiêu đề sách *
                                </label>
                                <input type="text" name="title" id="upload-title" required
                                    class="w-full px-4 py-2 rounded-lg glass border border-gray-200 bg-white text-gray-900 placeholder:text-gray-400 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50 transition-all"
                                    placeholder="Nhập tiêu đề sách">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-user mr-2"></i>Tác giả *
                                </label>
                                <input type="text" name="author" id="upload-author" required
                                    class="w-full px-4 py-2 rounded-lg glass border border-gray-200 bg-white text-gray-900 placeholder:text-gray-400 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50 transition-all"
                                    placeholder="Nhập tên tác giả">
                            </div>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-tag mr-2"></i>Thể loại *
                            </label>
                            <select name="category" id="upload-category" required
                                class="w-full px-4 py-2 rounded-lg glass border border-gray-200 bg-white text-gray-900 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50 transition-all">
                                <option value="fiction">Tiểu thuyết</option>
                                <option value="literature">Văn học</option>
                                <option value="history">Lịch sử</option>
                                <option value="science">Khoa học</option>
                                <option value="philosophy">Triết học</option>
                                <option value="General">Khác</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                <i class="fas fa-align-left mr-2"></i>Mô tả
                            </label>
                            <textarea name="description" id="upload-description" rows="3"
                                class="w-full px-4 py-2 rounded-lg glass border border-gray-200 bg-white text-gray-900 placeholder:text-gray-400 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50 transition-all resize-none"
                                placeholder="Mô tả ngắn về nội dung sách..."></textarea>
                        </div>
                        
                        <div class="grid md:grid-cols-3 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-file-alt mr-2"></i>Số trang
                                </label>
                                <input type="number" name="page_count" id="upload-page-count" min="0"
                                    class="w-full px-4 py-2 rounded-lg glass border border-gray-200 bg-white text-gray-900 placeholder:text-gray-400 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50 transition-all"
                                    placeholder="0">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-calendar mr-2"></i>Năm xuất bản
                                </label>
                                <input type="number" name="published_date" id="upload-published-date" min="1000" max="9999"
                                    class="w-full px-4 py-2 rounded-lg glass border border-gray-200 bg-white text-gray-900 placeholder:text-gray-400 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50 transition-all"
                                    placeholder="2024">
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    <i class="fas fa-barcode mr-2"></i>ISBN (tùy chọn)
                                </label>
                                <input type="text" name="isbn" id="upload-isbn"
                                    class="w-full px-4 py-2 rounded-lg glass border border-gray-200 bg-white text-gray-900 placeholder:text-gray-400 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50 transition-all"
                                    placeholder="ISBN">
                            </div>
                        </div>
                        
                        <div class="flex gap-4 pt-4">
                            <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg glow-hover transition-all btn-modern">
                                <i class="fas fa-upload mr-2"></i>Tải lên
                            </button>
                            <button type="button" onclick="hideUploadModal()" class="px-6 py-3 glass border border-gray-200 rounded-lg font-semibold hover:bg-gray-50 transition-all text-gray-700">
                                Hủy
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Search and Category Filters -->
            <div class="mb-6 space-y-4 reveal" style="transition-delay: 0.1s;">
                <!-- Search Bar -->
                <div class="relative">
                    <i class="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    <input
                        type="text"
                        id="book-search"
                        placeholder="Tìm kiếm sách miễn phí..."
                        value="<?php echo htmlspecialchars($search); ?>"
                        class="w-full pl-12 pr-4 py-3 rounded-lg glass border border-gray-200 bg-white text-gray-900 placeholder:text-gray-400 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50 transition-all"
                    />
                </div>

                <!-- Category Filter Buttons -->
                <div class="flex flex-wrap gap-2">
                    <a href="new-books.php" class="filter-btn px-4 py-2 rounded-lg glass text-sm font-medium hover:bg-gray-50 transition-all <?php echo $category === 'all' ? 'bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white' : ''; ?>" data-filter="all">
                        Tất cả
                    </a>
                    <a href="new-books.php?category=fiction" class="filter-btn px-4 py-2 rounded-lg glass text-sm font-medium hover:bg-gray-50 transition-all <?php echo $category === 'fiction' ? 'bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white' : ''; ?>" data-filter="fiction">
                        <i class="fas fa-book mr-1"></i>Tiểu thuyết
                    </a>
                    <a href="new-books.php?category=literature" class="filter-btn px-4 py-2 rounded-lg glass text-sm font-medium hover:bg-gray-50 transition-all <?php echo $category === 'literature' ? 'bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white' : ''; ?>" data-filter="literature">
                        <i class="fas fa-scroll mr-1"></i>Văn học
                    </a>
                    <a href="new-books.php?category=history" class="filter-btn px-4 py-2 rounded-lg glass text-sm font-medium hover:bg-gray-50 transition-all <?php echo $category === 'history' ? 'bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white' : ''; ?>" data-filter="history">
                        <i class="fas fa-landmark mr-1"></i>Lịch sử
                    </a>
                    <a href="new-books.php?category=science" class="filter-btn px-4 py-2 rounded-lg glass text-sm font-medium hover:bg-gray-50 transition-all <?php echo $category === 'science' ? 'bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white' : ''; ?>" data-filter="science">
                        <i class="fas fa-flask mr-1"></i>Khoa học
                    </a>
                    <a href="new-books.php?category=philosophy" class="filter-btn px-4 py-2 rounded-lg glass text-sm font-medium hover:bg-gray-50 transition-all <?php echo $category === 'philosophy' ? 'bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white' : ''; ?>" data-filter="philosophy">
                        <i class="fas fa-brain mr-1"></i>Triết học
                    </a>
                </div>
            </div>

            <!-- Loading State -->
            <div id="loading-state" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-[#FFB347]"></div>
                <p class="mt-4 text-gray-600">Đang tải sách miễn phí...</p>
            </div>

            <!-- Error State -->
            <div id="error-state" class="hidden text-center py-12">
                <i class="fas fa-exclamation-triangle text-4xl text-red-500 mb-4"></i>
                <p class="text-gray-600">Không thể tải sách. Vui lòng thử lại sau.</p>
                <button onclick="loadFreeBooks()" class="mt-4 px-6 py-2 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg transition-all">
                    Thử lại
                </button>
            </div>

            <!-- Empty State -->
            <div id="empty-state" class="hidden text-center py-12">
                <i class="fas fa-book text-4xl text-gray-400 mb-4"></i>
                <p class="text-gray-600 mb-2">Không tìm thấy sách nào</p>
                <p class="text-sm text-gray-500">Thử tìm kiếm với từ khóa khác hoặc chọn thể loại khác</p>
            </div>

            <!-- Books Grid -->
            <div id="books-grid" class="grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" style="display: none;">
                <!-- Books will be loaded here dynamically -->
            </div>

            <!-- Load More Button -->
            <div id="load-more-container" class="text-center mt-8 hidden">
                <button onclick="loadMoreBooks()" id="load-more-btn" class="px-8 py-3 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg glow-hover transition-all btn-modern">
                    <i class="fas fa-arrow-down mr-2"></i>Tải thêm sách
                </button>
            </div>
        </div>
    </main>

    <script src="js/api-client.js"></script>
    <script src="js/auth.js"></script>
    <script src="js/books-api-simple.js"></script>
    <script>
        // Wait for BooksAPI to be fully loaded
        function waitForBooksAPI(callback, maxAttempts = 50) {
            let attempts = 0;
            const checkInterval = setInterval(() => {
                attempts++;
                if (window.BooksAPI && typeof window.BooksAPI.getFreeBooksByCategory === 'function') {
                    clearInterval(checkInterval);
                    console.log('BooksAPI is ready!');
                    callback();
                } else if (attempts >= maxAttempts) {
                    clearInterval(checkInterval);
                    console.error('BooksAPI failed to load after', maxAttempts, 'attempts');
                    const errorState = document.getElementById('error-state');
                    if (errorState) {
                        errorState.classList.remove('hidden');
                        const errorText = errorState.querySelector('p');
                        if (errorText) {
                            errorText.textContent = 'Lỗi: Không thể tải BooksAPI. Vui lòng làm mới trang (Ctrl+F5).';
                        }
                    }
                    const loadingState = document.getElementById('loading-state');
                    if (loadingState) loadingState.style.display = 'none';
                }
            }, 100);
        }
        // Current filter and search
        let currentCategory = '<?php echo htmlspecialchars($category); ?>';
        let currentSearch = '<?php echo htmlspecialchars($search); ?>';
        let currentPage = 0;
        let isLoading = false;
        let hasMoreBooks = true;
        let loadedBookIds = new Set(); // Track loaded books to avoid duplicates
        const booksPerPage = 20;
        
        // Load free books from API
        async function loadFreeBooks(reset = false) {
            if (isLoading) {
                console.log('Already loading, skipping...');
                return;
            }
            
            const loadingState = document.getElementById('loading-state');
            const errorState = document.getElementById('error-state');
            const emptyState = document.getElementById('empty-state');
            const booksGrid = document.getElementById('books-grid');
            const loadMoreContainer = document.getElementById('load-more-container');
            const loadMoreBtn = document.getElementById('load-more-btn');
            
            if (reset) {
                currentPage = 0;
                hasMoreBooks = true;
                loadedBookIds.clear(); // Clear loaded books tracking
                if (booksGrid) booksGrid.innerHTML = '';
                console.log('Reset: cleared loaded books, page reset to 0');
            }
            
            // Show loading
            isLoading = true;
            if (loadingState && currentPage === 0) loadingState.style.display = 'block';
            if (errorState) errorState.classList.add('hidden');
            if (emptyState) emptyState.classList.add('hidden');
            if (loadMoreContainer) loadMoreContainer.classList.add('hidden');
            
            console.log('Loading books:', {
                category: currentCategory,
                search: currentSearch,
                page: currentPage,
                startIndex: currentPage * booksPerPage
            });
            
            try {
                let books = [];
                const startIndex = currentPage * booksPerPage;
                
                // FIRST: Always load uploaded books (from khosach) and show them first
                // Load uploaded books on first page only (to avoid duplicates on pagination)
                if (reset || currentPage === 0) {
                    try {
                        console.log('📚 Loading uploaded books from khosach first...');
                        // Load more uploaded books to ensure they're visible
                        const uploadedResponse = await fetch(`api/public-books.php?limit=50&offset=0`);
                        if (uploadedResponse.ok) {
                            const uploadedBooks = await uploadedResponse.json();
                            console.log('✓ Loaded', uploadedBooks.length, 'uploaded books from database');
                            
                            // Filter by category if needed
                            let filteredUploaded = uploadedBooks;
                            if (currentCategory !== 'all') {
                                filteredUploaded = uploadedBooks.filter(book => {
                                    const categories = book.categories || [];
                                    const categoryStr = book.category || '';
                                    return categories.some(cat => 
                                        cat.toLowerCase() === currentCategory.toLowerCase() ||
                                        cat.toLowerCase().includes(currentCategory.toLowerCase())
                                    ) || categoryStr.toLowerCase() === currentCategory.toLowerCase();
                                });
                                console.log('✓ Filtered to', filteredUploaded.length, 'uploaded books for category:', currentCategory);
                            }
                            
                            // Filter by search if needed
                            if (currentSearch && currentSearch.trim().length >= 2) {
                                const searchLower = currentSearch.toLowerCase();
                                filteredUploaded = filteredUploaded.filter(book => 
                                    book.title.toLowerCase().includes(searchLower) ||
                                    book.author.toLowerCase().includes(searchLower) ||
                                    (book.description && book.description.toLowerCase().includes(searchLower))
                                );
                                console.log('✓ Filtered to', filteredUploaded.length, 'uploaded books for search:', currentSearch);
                            }
                            
                            // Add uploaded books FIRST (they have highest priority)
                            for (const book of filteredUploaded) {
                                if (!loadedBookIds.has(book.id)) {
                                    book.source = 'uploaded';
                                    book.isFree = true;
                                    // Mark as uploaded for display
                                    book.isUploaded = true;
                                    books.push(book);
                                    loadedBookIds.add(book.id);
                                }
                            }
                            console.log('✓ Added', books.length, 'uploaded books to display (will show first)');
                        } else {
                            console.warn('⚠️ Failed to load uploaded books, status:', uploadedResponse.status);
                        }
                    } catch (uploadError) {
                        console.error('❌ Error loading uploaded books:', uploadError);
                    }
                }
                
                if (currentSearch && currentSearch.trim().length >= 2) {
                    // Search books
                    if (!window.BooksAPI) {
                        throw new Error('BooksAPI chưa được tải');
                    }
                    
                    console.log('Searching books with query:', currentSearch);
                    const searchResults = await window.BooksAPI.searchBooks(currentSearch, booksPerPage);
                    console.log('Search results:', searchResults.length, 'books');
                    // Filter for free books and remove duplicates
                    const filteredSearch = searchResults.filter(book => {
                        if (loadedBookIds.has(book.id)) return false;
                        loadedBookIds.add(book.id);
                        return book.isFree || book.previewLink;
                    });
                    books = books.concat(filteredSearch);
                    console.log('After filtering:', filteredSearch.length, 'free books from search');
                } else if (currentCategory !== 'all') {
                    // Get books by category
                    if (!window.BooksAPI) {
                        throw new Error('BooksAPI chưa được tải');
                    }
                    
                    // Check if function exists, if not use fallback
                    if (typeof window.BooksAPI.getFreeBooksByCategory !== 'function') {
                        console.warn('getFreeBooksByCategory is not a function! Using fallback with searchBooks.');
                        console.log('Available methods:', Object.keys(window.BooksAPI));
                        
                        // Fallback: Use searchBooks with category as query
                        const categoryQueries = {
                            'fiction': 'fiction novel',
                            'literature': 'literature',
                            'history': 'history',
                            'science': 'science',
                            'philosophy': 'philosophy'
                        };
                        
                        const searchQuery = categoryQueries[currentCategory] || currentCategory;
                        const searchResults = await window.BooksAPI.searchBooks(searchQuery, booksPerPage * 2);
                        
                        // Filter for free books
                        books = searchResults.filter(book => {
                            if (loadedBookIds.has(book.id)) return false;
                            loadedBookIds.add(book.id);
                            return book.isFree || book.previewLink;
                        }).slice(0, booksPerPage);
                        
                        console.log('Fallback search results:', books.length, 'books');
                    } else {
                        console.log('Loading books by category:', currentCategory, 'startIndex:', startIndex);
                        const allBooks = await window.BooksAPI.getFreeBooksByCategory(currentCategory, booksPerPage * 2, startIndex);
                        console.log('Category results:', allBooks.length, 'books');
                        // Filter out already loaded books
                        books = allBooks.filter(book => {
                            if (loadedBookIds.has(book.id)) return false;
                            loadedBookIds.add(book.id);
                            return true;
                        }).slice(0, booksPerPage);
                        console.log('After filtering duplicates:', books.length, 'books');
                    }
                } else {
                    // Get all free books
                    if (!window.BooksAPI) {
                        throw new Error('BooksAPI chưa được tải');
                    }
                    
                    console.log('Loading all free books, startIndex:', startIndex);
                    const allBooks = await window.BooksAPI.getFreeBooks(booksPerPage * 2, startIndex);
                    console.log('All books results:', allBooks.length, 'books');
                    // Filter out already loaded books
                    const filteredApiBooks = allBooks.filter(book => {
                        if (loadedBookIds.has(book.id)) return false;
                        loadedBookIds.add(book.id);
                        return true;
                    }).slice(0, booksPerPage);
                    books = books.concat(filteredApiBooks);
                    console.log('After filtering duplicates:', filteredApiBooks.length, 'books from API');
                }
                
                if (loadingState) loadingState.style.display = 'none';
                
                if (!books || books.length === 0) {
                    console.log('No books found');
                    if (currentPage === 0) {
                        if (emptyState) {
                            emptyState.classList.remove('hidden');
                        }
                    } else {
                        // No more books - show end message
                        showEndOfBooksMessage();
                    }
                    hasMoreBooks = false;
                    if (loadMoreContainer) loadMoreContainer.classList.add('hidden');
                    return;
                }
                
                // Sort books: uploaded books first, then others
                books.sort((a, b) => {
                    const aIsUploaded = a.source === 'uploaded' || a.isUploaded;
                    const bIsUploaded = b.source === 'uploaded' || b.isUploaded;
                    if (aIsUploaded && !bIsUploaded) return -1;
                    if (!aIsUploaded && bIsUploaded) return 1;
                    return 0; // Keep original order for same type
                });
                
                console.log('Displaying', books.length, 'books (uploaded books first)');
                // Display books
                if (booksGrid) {
                    booksGrid.style.display = 'grid';
                    displayBooks(books, !reset);
                }
                
                // Check if we got fewer books than requested (means no more available)
                if (books.length < booksPerPage) {
                    console.log('Got fewer books than requested, no more available');
                    hasMoreBooks = false;
                    if (loadMoreContainer) loadMoreContainer.classList.add('hidden');
                    // Show end message after a short delay
                    setTimeout(() => {
                        showEndOfBooksMessage();
                    }, 500);
                } else {
                    // Show load more button
                    console.log('More books available, showing load more button');
                    hasMoreBooks = true;
                    if (loadMoreContainer) {
                        loadMoreContainer.classList.remove('hidden');
                        if (loadMoreBtn) {
                            loadMoreBtn.disabled = false;
                        }
                        // Remove any existing end message
                        const existingEndMsg = document.getElementById('end-of-books-message');
                        if (existingEndMsg) existingEndMsg.remove();
                    }
                }
                
            } catch (error) {
                console.error('Error loading free books:', error);
                if (loadingState) loadingState.style.display = 'none';
                if (errorState) {
                    errorState.classList.remove('hidden');
                    const errorText = errorState.querySelector('p');
                    if (errorText) {
                        errorText.textContent = 'Không thể tải sách: ' + error.message;
                    }
                }
            } finally {
                isLoading = false;
            }
        }
        
        // Show end of books message
        function showEndOfBooksMessage() {
            // Remove existing message if any
            const existingMsg = document.getElementById('end-of-books-message');
            if (existingMsg) existingMsg.remove();
            
            // Check if message already exists
            const container = document.querySelector('.container.mx-auto.px-6');
            if (!container) return;
            
            const endMessage = document.createElement('div');
            endMessage.id = 'end-of-books-message';
            endMessage.className = 'text-center py-12 mt-8';
            endMessage.innerHTML = `
                <div class="glass rounded-2xl p-8 max-w-2xl mx-auto">
                    <i class="fas fa-book-open text-5xl text-[#FFB347] mb-4"></i>
                    <h3 class="text-2xl font-bold text-gray-900 mb-3">Đã hết sách trong kho</h3>
                    <p class="text-gray-600 mb-2">Chúng tôi đã hiển thị tất cả sách miễn phí hiện có.</p>
                    <p class="text-gray-500 text-sm">Hãy chờ chúng tôi cập nhật thêm sách mới nhé! 📚✨</p>
                    <div class="mt-6 flex gap-4 justify-center">
                        <a href="history.php" class="px-6 py-2 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg transition-all">
                            <i class="fas fa-bookmark mr-2"></i>Xem sách của tôi
                        </a>
                        <button onclick="loadFreeBooks(true)" class="px-6 py-2 glass border border-gray-200 rounded-lg font-semibold hover:bg-gray-50 transition-all text-gray-700">
                            <i class="fas fa-redo mr-2"></i>Tải lại
                        </button>
                    </div>
                </div>
            `;
            
            container.appendChild(endMessage);
        }
        
        function displayBooks(books, append = false) {
            const booksGrid = document.getElementById('books-grid');
            if (!booksGrid) return;
            
            const html = books.map((book, index) => {
                // Lấy cover từ nhiều field có thể có
                const coverUrl = book.cover || book.cover_url || book.imageLinks?.thumbnail || '';
                const currentIndex = append ? booksGrid.children.length + index : index;
                
                return `
                    <div class="book-card card-modern glass rounded-2xl overflow-hidden reveal relative z-10" 
                         style="transition-delay: ${(currentIndex % 20) * 0.05}s;"
                         data-book-id="${escapeHtml(book.id)}"
                         data-book-title="${escapeHtml(book.title)}"
                         data-book-author="${escapeHtml(book.author)}"
                         data-book-cover="${escapeHtml(coverUrl)}"
                         data-book-description="${escapeHtml(book.description || '')}">
                        <a href="${book.previewLink || book.infoLink || 'book-info.php?id=' + escapeHtml(book.id)}" ${book.previewLink ? 'target="_blank"' : ''} class="block">
                            <div class="relative h-64 w-full overflow-hidden bg-gradient-to-br from-[#FFB347]/10 to-[#4A7856]/10">
                                ${coverUrl ? 
                                    `<img src="${escapeHtml(coverUrl)}" alt="${escapeHtml(book.title)}" class="h-full w-full object-cover hover:scale-105 transition-transform duration-300" onerror="console.error('Image load error:', '${escapeHtml(coverUrl)}'); this.style.display='none'; this.nextElementSibling.style.display='flex';">` :
                                    ''
                                }
                                <div class="absolute inset-0 bg-gradient-to-br from-[#FFB347]/20 to-[#4A7856]/20 ${coverUrl ? 'hidden' : 'flex'} items-center justify-center">
                                    <i class="fas fa-book text-4xl text-[#FFB347]"></i>
                                </div>
                                ${book.source === 'uploaded' || book.isUploaded ? 
                                    '<span class="absolute top-2 left-2 px-2.5 py-1 rounded-md text-xs bg-gradient-to-r from-green-500 to-green-600 text-white font-semibold shadow-lg z-10"><i class="fas fa-upload mr-1"></i>Đã tải lên</span>' : 
                                    '<span class="absolute top-2 left-2 px-2 py-1 rounded text-xs bg-green-500 text-white font-semibold"><i class="fas fa-gift mr-1"></i>Miễn phí</span>'}
                                ${book.source && book.source !== 'uploaded' ? 
                                    `<span class="absolute top-2 right-2 px-2 py-1 rounded text-xs bg-blue-500 text-white font-semibold text-[10px]">${book.source === 'gutenberg' ? 'Gutenberg' : book.source === 'openlibrary' ? 'Open Library' : book.source === 'internetarchive' ? 'Archive' : book.source === 'libraryofcongress' ? 'LoC' : 'Google'}</span>` : ''}
                            </div>
                        </a>
                        <div class="p-4 space-y-3">
                            <div>
                                <h3 class="font-bold text-lg mb-1 line-clamp-2 text-gray-900">${escapeHtml(book.title)}</h3>
                                <p class="text-gray-600 text-sm">${escapeHtml(book.author)}</p>
                            </div>
                            ${book.rating > 0 ? `
                                <div class="flex items-center gap-1 text-[#FFB347]">
                                    ${generateStars(book.rating)}
                                    <span class="text-gray-600 text-xs ml-1">${book.rating.toFixed(1)}</span>
                                </div>
                            ` : ''}
                            <div class="flex gap-2">
                                ${book.source === 'uploaded' ? 
                                    `<a href="reading.php?id=${escapeHtml(book.id)}" class="flex-1 px-4 py-2 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg text-center text-sm font-semibold hover:shadow-lg transition-all">
                                        <i class="fas fa-book-open mr-1"></i>Đọc ngay
                                    </a>` :
                                    `<button onclick="readBookNow('${escapeHtml(book.id)}')" class="flex-1 px-4 py-2 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg text-center text-sm font-semibold hover:shadow-lg transition-all">
                                        <i class="fas fa-book-open mr-1"></i>Đọc ngay
                                    </button>`
                                }
                                ${book.source !== 'uploaded' ? 
                                    `<button onclick="addBookToLibrary('${escapeHtml(book.id)}')" class="px-4 py-2 glass border border-[#FFB347] rounded-lg text-[#FFB347] hover:bg-[#FFB347]/10 transition-all" title="Thêm vào kho sách">
                                        <i class="fas fa-plus"></i>
                                    </button>` : 
                                    `<a href="book-info.php?id=${escapeHtml(book.id)}" class="px-4 py-2 glass border border-[#FFB347] rounded-lg text-[#FFB347] hover:bg-[#FFB347]/10 transition-all" title="Xem chi tiết">
                                        <i class="fas fa-info-circle"></i>
                                    </a>`
                                }
                            </div>
                        </div>
                    </div>
                `;
            }).join('');
            
            if (append) {
                booksGrid.innerHTML += html;
            } else {
                booksGrid.innerHTML = html;
            }
            
            // Trigger reveal animation
            const revealElements = booksGrid.querySelectorAll('.reveal');
            revealElements.forEach(el => el.classList.add('active'));
        }
        
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
        
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Read book now - add to database if needed, then redirect to reading page
        async function readBookNow(bookId) {
            try {
                console.log('📖 readBookNow: Starting for bookId=', bookId);
                
                // First, check if book exists in database
                try {
                    await window.APIClient.getBook(bookId);
                    console.log('✓ Book already in database, redirecting to reading page');
                    window.location.href = `reading.php?id=${encodeURIComponent(bookId)}`;
                    return;
                } catch (getBookError) {
                    console.log('⚠️ Book not in database, adding it first...', getBookError);
                }
                
                // Book doesn't exist, need to add it
                // Try to get book details from API
                if (!window.BooksAPI || typeof window.BooksAPI.getBookDetails !== 'function') {
                    showNotification('Không thể tải thông tin sách. Vui lòng thử lại.', 'error');
                    return;
                }
                
                try {
                    const apiBook = await window.BooksAPI.getBookDetails(bookId);
                    const bookToAdd = {
                        id: bookId,
                        title: apiBook.title || 'Unknown',
                        author: apiBook.authors?.[0] || apiBook.author || 'Unknown',
                        description: apiBook.description || '',
                        cover_url: apiBook.imageLinks?.thumbnail || apiBook.cover || '',
                        page_count: apiBook.pageCount || 0,
                        published_date: apiBook.publishedDate || null,
                        isbn: apiBook.industryIdentifiers?.[0]?.identifier || '',
                        categories: apiBook.categories || [],
                        source: 'google_books',
                        status: 'reading' // Set status to 'reading' immediately
                    };
                    
                    await window.APIClient.addBook(bookToAdd);
                    console.log('✓ Book added to database, redirecting to reading page');
                    
                    // Redirect to reading page
                    window.location.href = `reading.php?id=${encodeURIComponent(bookId)}`;
                } catch (apiError) {
                    console.error('❌ Could not fetch book from API:', apiError);
                    showNotification('Không thể tải thông tin sách. Vui lòng thử lại.', 'error');
                }
            } catch (error) {
                console.error('❌ Error in readBookNow:', error);
                showNotification('Lỗi khi mở sách: ' + error.message, 'error');
            }
        }
        
        // Add book to library
        async function addBookToLibrary(bookId) {
            try {
                // Check if book is from Gutenberg or Open Library (already has full data)
                const bookElement = document.querySelector(`[data-book-id="${bookId}"]`);
                let bookData;
                
                if (bookId.startsWith('gutenberg_') || bookId.startsWith('openlib_')) {
                    // Get book data from the displayed card
                    if (bookElement) {
                        const title = bookElement.dataset.bookTitle || '';
                        const author = bookElement.dataset.bookAuthor || 'Unknown';
                        const cover = bookElement.dataset.bookCover || '';
                        const description = bookElement.dataset.bookDescription || '';
                        
                        bookData = {
                            id: bookId,
                            title: title,
                            author: author,
                            description: description,
                            cover_url: cover,
                            page_count: 0,
                            published_date: null,
                            isbn: '',
                            categories: [],
                            source: bookId.startsWith('gutenberg_') ? 'gutenberg' : 'openlibrary',
                            status: 'want_to_read'
                        };
                    } else {
                        throw new Error('Không tìm thấy thông tin sách');
                    }
                } else {
                    // Google Books - get details from API
                    if (!window.BooksAPI) {
                        throw new Error('BooksAPI chưa được tải');
                    }
                    
                    const bookDetails = await window.BooksAPI.getBookDetails(bookId);
                    
                    bookData = {
                        id: bookId,
                        title: bookDetails.title,
                        author: bookDetails.authors?.join(', ') || bookDetails.author || 'Unknown',
                        description: bookDetails.description || '',
                        cover_url: bookDetails.cover || '',
                        page_count: bookDetails.pageCount || 0,
                        published_date: bookDetails.publishedDate || null,
                        isbn: bookDetails.isbn || '',
                        categories: bookDetails.categories || [],
                        source: 'google_books',
                        status: 'want_to_read'
                    };
                }
                
                await window.APIClient.addBook(bookData);
                
                // Show success notification
                showNotification('Đã thêm sách vào kho sách của bạn!', 'success');
            } catch (error) {
                console.error('Error adding book:', error);
                showNotification('Lỗi khi thêm sách: ' + error.message, 'error');
            }
        }
        
        function showNotification(message, type = 'success') {
            const notification = document.createElement('div');
            notification.className = `fixed top-20 right-6 glass rounded-lg px-6 py-4 shadow-xl z-50 transform transition-all duration-300 ${
                type === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 'bg-red-50 border border-red-200 text-red-800'
            }`;
            notification.innerHTML = `
                <div class="flex items-center gap-3">
                    <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                    <span>${escapeHtml(message)}</span>
                </div>
            `;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.style.opacity = '0';
                notification.style.transform = 'translateX(100%)';
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }
        
        // Load more books
        async function loadMoreBooks() {
            if (!hasMoreBooks || isLoading) {
                return;
            }
            
            const loadMoreBtn = document.getElementById('load-more-btn');
            if (loadMoreBtn) {
                loadMoreBtn.disabled = true;
                loadMoreBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Đang tải...';
            }
            
            currentPage++;
            await loadFreeBooks(false);
            
            if (loadMoreBtn && hasMoreBooks) {
                loadMoreBtn.disabled = false;
                loadMoreBtn.innerHTML = '<i class="fas fa-arrow-down mr-2"></i>Tải thêm sách';
            }
        }
        
        // Search functionality
        let searchTimeout;
        const searchInput = document.getElementById('book-search');
        
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchTimeout);
                const query = e.target.value.trim();
                
                // Only search if query changed
                if (query !== currentSearch) {
                    currentSearch = query;
                    
                    if (query.length >= 2) {
                        searchTimeout = setTimeout(() => {
                            currentPage = 0; // Reset page
                            loadedBookIds.clear(); // Clear tracking
                            loadFreeBooks(true);
                        }, 500);
                    } else if (query.length === 0) {
                        currentPage = 0; // Reset page
                        loadedBookIds.clear(); // Clear tracking
                        loadFreeBooks(true);
                    }
                }
            });
        }
        
        // Handle category filter clicks
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault(); // Prevent navigation
                let filter = btn.dataset.filter;
                
                // If no data-filter, try to get from href
                if (!filter) {
                    const href = btn.getAttribute('href');
                    if (href) {
                        const match = href.match(/[?&]category=([^&]*)/);
                        filter = match ? match[1] : 'all';
                    } else {
                        filter = 'all';
                    }
                }
                
                console.log('Filter clicked:', filter, 'Current category:', currentCategory);
                
                if (filter !== currentCategory) {
                    currentCategory = filter;
                    currentPage = 0;
                    loadedBookIds.clear();
                    
                    // Update URL without reload
                    const url = new URL(window.location);
                    url.searchParams.delete('special'); // Remove special if exists
                    if (filter === 'all') {
                        url.searchParams.delete('category');
                    } else {
                        url.searchParams.set('category', filter);
                    }
                    
                    window.history.pushState({category: currentCategory}, '', url);
                    
                    // Update active filter button
                    document.querySelectorAll('.filter-btn').forEach(b => {
                        b.classList.remove('bg-gradient-to-r', 'from-[#FFB347]', 'to-[#FF9500]', 'text-white');
                        if (!b.classList.contains('glass')) {
                            b.classList.add('glass');
                        }
                    });
                    btn.classList.add('bg-gradient-to-r', 'from-[#FFB347]', 'to-[#FF9500]', 'text-white');
                    btn.classList.remove('glass');
                    
                    // Load books
                    console.log('Loading books for category:', filter);
                    loadFreeBooks(true);
                }
            });
        });
        
        // Load books when page loads
        document.addEventListener('DOMContentLoaded', () => {
            waitForBooksAPI(() => {
                // Check if required functions exist
                if (typeof window.BooksAPI.getFreeBooks !== 'function') {
                    console.error('getFreeBooks is not a function!', window.BooksAPI);
                }
                if (typeof window.BooksAPI.getFreeBooksByCategory !== 'function') {
                    console.error('getFreeBooksByCategory is not a function!', window.BooksAPI);
                }
                
                console.log('BooksAPI available functions:', Object.keys(window.BooksAPI));
                loadFreeBooks(true);
            });
        });
        
        // Make functions global
        // Upload book modal functions
        function showUploadModal() {
            const modal = document.getElementById('upload-modal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        }
        
        function hideUploadModal() {
            const modal = document.getElementById('upload-modal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.getElementById('upload-book-form').reset();
            }
        }
        
        // Handle upload form submission
        document.getElementById('upload-book-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const form = e.target;
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            const originalText = submitBtn.innerHTML;
            
            try {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Đang tải lên...';
                
                const response = await fetch('api/upload-book.php', {
                    method: 'POST',
                    body: formData
                });
                
                const data = await response.json();
                
                if (data.success) {
                    showNotification('Sách đã được tải lên thành công!', 'success');
                    hideUploadModal();
                    
                    // Reload books to show the new uploaded book
                    if (currentCategory === 'all') {
                        currentPage = 0;
                        loadedBookIds.clear();
                        await loadFreeBooks(true);
                    }
                } else {
                    showNotification('Lỗi: ' + (data.message || 'Không thể tải sách lên'), 'error');
                }
            } catch (error) {
                console.error('Error uploading book:', error);
                showNotification('Lỗi khi tải sách lên: ' + error.message, 'error');
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        });
        
        // Auto-fill title/author from filename
        document.getElementById('book_file').addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const fileName = file.name.replace(/\.[^/.]+$/, ''); // Remove extension
                
                // Try to parse "Author - Title" format
                if (fileName.includes(' - ')) {
                    const parts = fileName.split(' - ');
                    if (parts.length >= 2) {
                        document.getElementById('upload-author').value = parts[0].trim();
                        document.getElementById('upload-title').value = parts.slice(1).join(' - ').trim();
                    }
                } else {
                    // If no separator, use filename as title
                    document.getElementById('upload-title').value = fileName;
                }
            }
        });
        
        // Close modal when clicking outside
        document.getElementById('upload-modal').addEventListener('click', (e) => {
            if (e.target.id === 'upload-modal') {
                hideUploadModal();
            }
        });
        
        window.addBookToLibrary = addBookToLibrary;
        window.readBookNow = readBookNow;
        window.loadFreeBooks = loadFreeBooks;
        window.loadMoreBooks = loadMoreBooks;
        window.showUploadModal = showUploadModal;
        window.hideUploadModal = hideUploadModal;
    </script>

<?php include __DIR__ . '/includes/footer.php'; ?>

