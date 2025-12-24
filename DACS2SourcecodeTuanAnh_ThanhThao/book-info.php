<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$auth = new Auth();
$auth->requireLogin(); // Protect page
$auth->requireVerifiedEmail(); // Yêu cầu email đã được xác nhận

$currentUser = $auth->getCurrentUser();
$bookId = $_GET['id'] ?? null;

if (!$bookId) {
    header('Location: history.php');
    exit;
}

$pageTitle = 'Chi tiết sách - BookOnline';
include __DIR__ . '/includes/header.php';
?>

    <!-- Main Content -->
    <main class="pt-24 pb-12">
        <div class="container mx-auto px-6">
            <!-- Back Button -->
            <a href="history.php" class="inline-flex items-center gap-2 text-gray-700 hover:text-[#FFB347] mb-6 transition-colors">
                <i class="fas fa-arrow-left"></i>
                <span>Quay lại</span>
            </a>

            <!-- Loading State -->
            <div id="book-loading" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-[#FFB347]"></div>
                <p class="mt-4 text-gray-600">Đang tải thông tin sách...</p>
            </div>

            <!-- Book Content -->
            <div id="book-content" class="grid lg:grid-cols-3 gap-8" style="display: none;">
                <!-- Left Column - Book Cover & Info -->
                <div class="lg:col-span-1 reveal">
                    <div class="glass rounded-2xl overflow-hidden card-modern">
                        <!-- Book Cover -->
                        <div class="relative h-96 w-full overflow-hidden bg-gradient-to-br from-[#FFB347]/10 to-[#4A7856]/10" id="book-cover-container">
                            <div class="h-full w-full bg-gradient-to-br from-[#fff9e6] to-[#ffe8cc] flex items-center justify-center">
                                <div class="text-center">
                                    <i class="fas fa-book text-6xl text-[#FFB347]/40 mb-4"></i>
                                    <p class="text-gray-600 text-sm">Bìa sách</p>
                                </div>
                            </div>
                        </div>

                        <!-- Book Info -->
                        <div class="p-6 space-y-4">
                            <div class="flex items-center justify-between">
                                <span class="px-3 py-1 rounded-lg glass text-sm text-gray-700 bg-gray-50" id="book-category">-</span>
                                <span class="px-3 py-1 rounded-lg bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white text-sm font-semibold" id="book-status-badge">-</span>
                            </div>
                            
                            <div>
                                <h1 class="text-2xl font-bold mb-2 text-gray-900" id="book-title">Đang tải...</h1>
                                <p class="text-gray-600" id="book-author">-</p>
                            </div>

                            <div class="flex items-center gap-4 text-sm text-gray-600">
                                <span id="book-pages"><i class="fas fa-book-open mr-2"></i>-</span>
                                <span id="book-year"><i class="fas fa-calendar mr-2"></i>-</span>
                            </div>

                            <!-- Rating -->
                            <div class="flex items-center gap-2">
                                <div class="star-rating" id="book-rating-display">
                                    <i class="far fa-star"></i>
                                    <i class="far fa-star"></i>
                                    <i class="far fa-star"></i>
                                    <i class="far fa-star"></i>
                                    <i class="far fa-star"></i>
                                </div>
                                <span class="text-gray-700" id="book-rating-text">Chưa có đánh giá</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column - Details -->
                <div class="lg:col-span-2 space-y-6 reveal" style="transition-delay: 0.2s;">
                    <!-- Progress Section -->
                    <div class="glass rounded-2xl p-6 card-modern">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-2xl font-bold text-gray-900">Tiến độ đọc</h2>
                            <span class="text-3xl font-bold gradient-text" id="progress-percent">0%</span>
                        </div>
                        <div class="progress-bar mb-4">
                            <div class="progress-fill" id="progress-bar-fill" style="width: 0%;"></div>
                        </div>
                        <div class="flex gap-2">
                            <input 
                                type="range" 
                                min="0" 
                                max="100" 
                                value="0" 
                                id="progress-slider"
                                class="flex-1 accent-[#FFB347]"
                            >
                            <button onclick="updateProgress()" class="px-6 py-2 bg-gradient-to-r from-[#4A7856] to-[#4A7856]/90 text-white rounded-lg font-semibold hover:shadow-lg transition-all">
                                <i class="fas fa-save mr-2"></i>Cập nhật
                            </button>
                        </div>
                        <div class="mt-2 text-sm text-gray-600">
                            <span id="current-page-display">Trang 0</span> / <span id="total-pages-display">0</span>
                        </div>
                    </div>

                    <!-- Rating Section -->
                    <div class="glass rounded-2xl p-6 card-modern">
                        <h2 class="text-2xl font-bold mb-4 text-gray-900">Đánh giá của bạn</h2>
                        <div class="flex items-center gap-2 mb-4">
                            <div class="star-rating" id="user-rating-stars">
                                <i class="far fa-star star" data-rating="1"></i>
                                <i class="far fa-star star" data-rating="2"></i>
                                <i class="far fa-star star" data-rating="3"></i>
                                <i class="far fa-star star" data-rating="4"></i>
                                <i class="far fa-star star" data-rating="5"></i>
                            </div>
                            <span class="text-lg font-semibold ml-4 text-gray-900" id="user-rating-value">Chưa đánh giá</span>
                        </div>
                        <input type="hidden" id="rating-value" value="0">
                    </div>

                    <!-- Description -->
                    <div class="glass rounded-2xl p-6 card-modern">
                        <h2 class="text-2xl font-bold mb-4 text-gray-900">Mô tả</h2>
                        <p class="text-gray-700 leading-relaxed" id="book-description">
                            Đang tải...
                        </p>
                    </div>

                    <!-- Notes Section -->
                    <div class="glass rounded-2xl p-6 card-modern">
                        <h2 class="text-2xl font-bold mb-4 text-gray-900">Ghi chú đọc</h2>
                        <textarea
                            id="book-notes"
                            class="w-full min-h-[200px] rounded-xl border border-gray-200 bg-white p-4 text-sm text-gray-900 placeholder:text-gray-400 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50 transition-all resize-none"
                            placeholder="Viết ghi chú của bạn ở đây..."
                        ></textarea>
                        <button onclick="saveNotes()" class="mt-4 w-full px-6 py-3 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg glow-hover transition-all btn-modern">
                            <span class="relative z-10">
                                <i class="fas fa-save mr-2"></i>Lưu ghi chú
                            </span>
                        </button>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-4">
                        <button onclick="handleReadBook()" class="flex-1 px-6 py-4 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold text-center hover:shadow-lg glow-hover transition-all btn-modern">
                            <span class="relative z-10">
                                <i class="fas fa-book-open mr-2"></i>Đọc ngay
                            </span>
                        </button>
                        <button onclick="deleteBook()" class="px-6 py-4 glass border border-red-200 rounded-lg font-semibold hover:bg-red-50 transition-all text-red-600">
                            <i class="fas fa-trash mr-2"></i>Xóa sách
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="js/api-client.js"></script>
    <script src="js/auth.js"></script>
    <script>
        const bookId = '<?php echo htmlspecialchars($bookId); ?>';
        let currentBook = null;
        let currentRating = 0;
        
        // Load book details
        async function loadBookDetails() {
            const loadingEl = document.getElementById('book-loading');
            const contentEl = document.getElementById('book-content');
            
            try {
                const book = await window.APIClient.getBook(bookId);
                currentBook = book;
                
                // Display book
                displayBookDetails(book);
                
                // Load progress
                await loadProgress();
                
                // Hide loading, show content
                if (loadingEl) loadingEl.style.display = 'none';
                if (contentEl) contentEl.style.display = 'grid';
                
            } catch (error) {
                console.error('Error loading book:', error);
                if (loadingEl) {
                    loadingEl.innerHTML = `
                        <div class="text-center py-12">
                            <i class="fas fa-exclamation-triangle text-4xl text-red-400 mb-4"></i>
                            <p class="text-gray-600 mb-2">Không thể tải thông tin sách</p>
                            <p class="text-sm text-gray-500 mb-4">${error.message}</p>
                            <a href="history.php" class="inline-block px-6 py-2 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg transition-all">
                                Quay lại kho sách
                            </a>
                        </div>
                    `;
                }
            }
        }
        
        function displayBookDetails(book) {
            // Cover
            const coverContainer = document.getElementById('book-cover-container');
            if (coverContainer && book.cover_url) {
                coverContainer.innerHTML = `
                    <img src="${escapeHtml(book.cover_url)}" alt="${escapeHtml(book.title)}" 
                         class="h-full w-full object-cover"
                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                    <div class="absolute inset-0 bg-gradient-to-br from-[#FFB347]/20 to-[#4A7856]/20 hidden items-center justify-center">
                        <i class="fas fa-book text-6xl text-[#FFB347]"></i>
                    </div>
                `;
            }
            
            // Title
            document.getElementById('book-title').textContent = book.title || 'N/A';
            
            // Author
            document.getElementById('book-author').textContent = book.author || 'N/A';
            
            // Category
            const category = Array.isArray(book.categories) ? book.categories[0] : (book.categories || 'N/A');
            document.getElementById('book-category').textContent = category;
            
            // Status
            const statusText = {
                'reading': 'Đang đọc',
                'completed': 'Đã đọc',
                'want_to_read': 'Muốn đọc'
            };
            document.getElementById('book-status-badge').textContent = statusText[book.status] || 'Muốn đọc';
            
            // Pages
            const totalPages = book.total_pages || book.page_count || 0;
            document.getElementById('book-pages').innerHTML = `<i class="fas fa-book-open mr-2"></i>${totalPages} trang`;
            document.getElementById('total-pages-display').textContent = totalPages;
            
            // Year
            if (book.published_date) {
                const year = new Date(book.published_date).getFullYear();
                document.getElementById('book-year').innerHTML = `<i class="fas fa-calendar mr-2"></i>${year}`;
            }
            
            // Rating
            currentRating = book.rating || 0;
            updateRatingDisplay(currentRating);
            
            // Description
            document.getElementById('book-description').textContent = book.description || 'Chưa có mô tả';
            
            // Notes
            if (book.notes) {
                document.getElementById('book-notes').value = book.notes;
            }
        }
        
        // Load progress
        async function loadProgress() {
            try {
                const progress = await window.APIClient.getProgress(bookId);
                
                if (progress) {
                    const progressPercent = progress.progress_percent || 0;
                    const currentPage = progress.current_page || 0;
                    const totalPages = progress.total_pages || currentBook?.total_pages || currentBook?.page_count || 0;
                    
                    // Update UI
                    document.getElementById('progress-percent').textContent = Math.round(progressPercent) + '%';
                    document.getElementById('progress-bar-fill').style.width = progressPercent + '%';
                    document.getElementById('progress-slider').value = progressPercent;
                    document.getElementById('current-page-display').textContent = `Trang ${currentPage}`;
                    document.getElementById('total-pages-display').textContent = totalPages;
                }
            } catch (error) {
                console.error('Error loading progress:', error);
            }
        }
        
        // Update progress
        async function updateProgress() {
            const slider = document.getElementById('progress-slider');
            const progress = parseInt(slider.value);
            const totalPages = parseInt(document.getElementById('total-pages-display').textContent) || 0;
            const currentPage = Math.round((progress / 100) * totalPages);
            
            try {
                await window.APIClient.updateProgress(bookId, progress, currentPage);
                
                // Update UI
                document.getElementById('progress-percent').textContent = progress + '%';
                document.getElementById('progress-bar-fill').style.width = progress + '%';
                document.getElementById('current-page-display').textContent = `Trang ${currentPage}`;
                
                // Show success message
                const btn = event.target;
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check mr-2"></i>Đã lưu!';
                btn.classList.add('bg-green-500');
                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.classList.remove('bg-green-500');
                }, 2000);
                
            } catch (error) {
                console.error('Error updating progress:', error);
                alert('Lỗi khi cập nhật tiến độ: ' + error.message);
            }
        }
        
        // Rating stars
        function setupRatingStars() {
            const stars = document.querySelectorAll('#user-rating-stars .star');
            stars.forEach((star, index) => {
                star.addEventListener('click', () => {
                    const rating = index + 1;
                    currentRating = rating;
                    updateRatingDisplay(rating);
                    saveRating(rating);
                });
                
                star.addEventListener('mouseenter', () => {
                    highlightStars(index + 1);
                });
            });
            
            document.getElementById('user-rating-stars').addEventListener('mouseleave', () => {
                highlightStars(currentRating);
            });
        }
        
        function highlightStars(rating) {
            const stars = document.querySelectorAll('#user-rating-stars .star');
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.classList.remove('far');
                    star.classList.add('fas', 'text-[#FFB347]');
                } else {
                    star.classList.remove('fas', 'text-[#FFB347]');
                    star.classList.add('far');
                }
            });
        }
        
        function updateRatingDisplay(rating) {
            highlightStars(rating);
            document.getElementById('user-rating-value').textContent = rating > 0 ? `${rating}/5` : 'Chưa đánh giá';
            document.getElementById('rating-value').value = rating;
        }
        
        // Save rating
        async function saveRating(rating) {
            try {
                await window.APIClient.updateBook(bookId, { rating: rating });
            } catch (error) {
                console.error('Error saving rating:', error);
            }
        }
        
        // Save notes
        async function saveNotes() {
            const notes = document.getElementById('book-notes').value;
            
            try {
                await window.APIClient.updateBook(bookId, { notes: notes });
                
                // Show success
                const btn = event.target;
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="fas fa-check mr-2"></i>Đã lưu!';
                setTimeout(() => {
                    btn.innerHTML = originalText;
                }, 2000);
            } catch (error) {
                console.error('Error saving notes:', error);
                alert('Lỗi khi lưu ghi chú: ' + error.message);
            }
        }
        
        // Read book
        function handleReadBook() {
            window.location.href = `reading.php?id=${bookId}`;
        }
        
        // Delete book
        async function deleteBook() {
            if (!confirm('Bạn có chắc muốn xóa sách này khỏi kho sách?')) {
                return;
            }
            
            try {
                await window.APIClient.deleteBook(bookId);
                alert('Đã xóa sách thành công!');
                window.location.href = 'history.php';
            } catch (error) {
                console.error('Error deleting book:', error);
                alert('Lỗi khi xóa sách: ' + error.message);
            }
        }
        
        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        // Progress slider change
        document.getElementById('progress-slider').addEventListener('input', (e) => {
            const progress = parseInt(e.target.value);
            document.getElementById('progress-percent').textContent = progress + '%';
            document.getElementById('progress-bar-fill').style.width = progress + '%';
            
            const totalPages = parseInt(document.getElementById('total-pages-display').textContent) || 0;
            const currentPage = Math.round((progress / 100) * totalPages);
            document.getElementById('current-page-display').textContent = `Trang ${currentPage}`;
        });
        
        // Load on page load
        document.addEventListener('DOMContentLoaded', () => {
            loadBookDetails();
            setupRatingStars();
        });
        
        // Make functions global
        window.updateProgress = updateProgress;
        window.saveNotes = saveNotes;
        window.handleReadBook = handleReadBook;
        window.deleteBook = deleteBook;
    </script>

<?php include __DIR__ . '/includes/footer.php'; ?>

