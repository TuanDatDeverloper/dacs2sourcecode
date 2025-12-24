<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$auth = new Auth();
$pageTitle = 'BookOnline - Nền tảng đọc sách trực tuyến';
include __DIR__ . '/includes/header.php';
?>

    <!-- Hero Section -->
    <section class="hero-section relative flex items-center justify-center overflow-hidden bg-gradient-to-br from-[#faf9f6] via-[#fff9e6] to-[#faf9f6]">
        <!-- Animated Background -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none z-0">
            <div class="absolute top-20 left-10 w-72 h-72 bg-[#FFB347]/8 rounded-full blur-3xl float"></div>
            <div class="absolute bottom-20 right-10 w-96 h-96 bg-[#4A7856]/8 rounded-full blur-3xl float" style="animation-delay: 2s;"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-[#FFB347]/5 rounded-full blur-3xl"></div>
        </div>

        <div class="container mx-auto px-6 py-12 relative z-10">
            <div class="grid lg:grid-cols-2 gap-12 items-center">
                <!-- Left Content -->
                <div class="space-y-8 reveal">
                    <div class="space-y-4">
                        <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold leading-tight">
                            <span class="text-gray-900">Khám phá thế giới</span><br>
                            <span class="animated-gradient">sách trực tuyến</span><br>
                            <span class="text-gray-900">của bạn</span>
                        </h1>
                        <p class="text-lg md:text-xl text-gray-600">
                            Nền tảng đọc sách hiện đại với hàng nghìn đầu sách, giao diện đẹp mắt và trải nghiệm đọc tuyệt vời. Bắt đầu hành trình đọc sách của bạn ngay hôm nay!
                        </p>
                    </div>
                    <div class="flex flex-wrap gap-4">
                        <?php if ($auth->isLoggedIn()): ?>
                            <a href="dashboard.php" class="px-8 py-4 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-[#1a2a40] rounded-lg font-semibold text-lg hover:shadow-lg glow-hover transition-all btn-modern">
                                <span class="relative z-10">Vào Dashboard</span>
                            </a>
                        <?php else: ?>
                            <a href="register.php" class="px-8 py-4 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-[#1a2a40] rounded-lg font-semibold text-lg hover:shadow-lg glow-hover transition-all btn-modern">
                                <span class="relative z-10">Bắt đầu đọc ngay</span>
                            </a>
                        <?php endif; ?>
                        <a href="#features" class="px-8 py-4 glass border border-gray-200 rounded-lg font-semibold text-lg hover:bg-yellow-50 transition-all text-gray-700">
                            Khám phá tính năng
                        </a>
                    </div>
                </div>

                <!-- Right Image/Illustration -->
                <div class="relative reveal" style="transition-delay: 0.2s;">
                    <div class="relative h-[500px] w-full overflow-hidden rounded-2xl border-2 border-[#FFB347]/20 shadow-xl glow group bg-gradient-to-br from-[#fff9e6] to-[#ffe8cc]">
                        <img 
                            src="images/anhtrangchu.jpg" 
                            alt="BookOnline - Nền tảng đọc sách trực tuyến"
                            class="w-full h-full object-cover"
                            onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                        />
                        <div class="absolute inset-0 bg-gradient-to-br from-[#FFB347]/8 via-[#4A7856]/8 to-[#FFB347]/5 shimmer hidden items-center justify-center backdrop-blur-sm z-10">
                            <div class="text-center text-gray-700">
                                <i class="fas fa-book-open text-6xl mb-4 text-[#FFB347]"></i>
                                <p class="text-sm font-medium">Hình ảnh minh họa</p>
                                <p class="text-xs mt-2 text-gray-500">Đọc sách trực tuyến</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-24 relative overflow-hidden bg-[#faf9f6]">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-3 z-0">
            <div class="absolute top-0 left-0 w-full h-full" style="background-image: radial-gradient(circle at 2px 2px, rgba(255,179,71,0.15) 1px, transparent 0); background-size: 40px 40px;"></div>
        </div>

        <div class="container mx-auto px-6 relative z-10">
            <div class="text-center mb-16 reveal">
                <p class="text-sm uppercase tracking-wider text-[#FFB347] font-semibold mb-2">Tính năng</p>
                <h2 class="text-4xl md:text-5xl font-bold mb-4 text-gray-900">
                    Tại sao chọn <span class="animated-gradient">BookOnline</span>?
                </h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Chúng tôi mang đến trải nghiệm đọc sách tốt nhất với các tính năng hiện đại và giao diện đẹp mắt
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="glass rounded-2xl p-8 card-modern reveal relative z-10">
                    <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-[#FFB347]/20 to-[#FFB347]/10 border border-[#FFB347]/30 flex items-center justify-center mb-6 glow-hover">
                        <i class="fas fa-book-reader text-3xl text-[#FFB347]"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4 text-gray-900">Đọc sách mọi lúc</h3>
                    <p class="text-gray-600">
                        Truy cập thư viện sách khổng lồ từ bất kỳ đâu, bất kỳ lúc nào. Đọc trên mọi thiết bị với giao diện tối ưu.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="glass rounded-2xl p-8 card-modern reveal relative z-10" style="transition-delay: 0.1s;">
                    <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-[#4A7856]/20 to-[#4A7856]/10 border border-[#4A7856]/30 flex items-center justify-center mb-6 glow-hover">
                        <i class="fas fa-bookmark text-3xl text-[#4A7856]"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4 text-gray-900">Lưu tiến độ</h3>
                    <p class="text-gray-600">
                        Tự động lưu vị trí đọc của bạn. Tiếp tục đọc từ nơi bạn dừng lại với một cú click.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="glass rounded-2xl p-8 card-modern reveal relative z-10" style="transition-delay: 0.2s;">
                    <div class="w-16 h-16 rounded-xl bg-gradient-to-br from-[#FFB347]/20 to-[#FFB347]/10 border border-[#FFB347]/30 flex items-center justify-center mb-6 glow-hover">
                        <i class="fas fa-palette text-3xl text-[#FFB347]"></i>
                    </div>
                    <h3 class="text-2xl font-bold mb-4 text-gray-900">Tùy chỉnh giao diện</h3>
                    <p class="text-gray-600">
                        Điều chỉnh font chữ, màu sắc, độ sáng theo sở thích của bạn để có trải nghiệm đọc tốt nhất.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Books Section -->
    <section class="py-24 relative overflow-hidden bg-[#faf9f6]">
        <div class="container mx-auto px-6 relative z-10">
            <div class="text-center mb-16 reveal">
                <p class="text-sm uppercase tracking-wider text-[#FFB347] font-semibold mb-2">Thư viện sách</p>
                <h2 class="text-4xl md:text-5xl font-bold mb-4 text-gray-900">
                    Sách <span class="animated-gradient">phổ biến</span>
                </h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Khám phá những cuốn sách hay nhất được yêu thích bởi độc giả
                </p>
            </div>

            <!-- Loading State -->
            <div id="books-loading" class="text-center py-12">
                <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-[#FFB347]"></div>
                <p class="mt-4 text-gray-600">Đang tải sách...</p>
            </div>

            <!-- Books Grid -->
            <div id="popular-books-grid" class="grid md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" style="display: none;"></div>

            <div class="text-center mt-12 reveal">
                <a href="<?php echo $auth->isLoggedIn() ? 'history.php' : 'register.php'; ?>" class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold text-lg hover:shadow-lg glow-hover transition-all btn-modern">
                    <span>Xem tất cả sách</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="py-24 bg-gradient-to-br from-[#fff9e6] via-[#ffe8cc] to-[#fff9e6] relative overflow-hidden">
        <div class="container mx-auto px-6 relative z-10">
            <div class="grid md:grid-cols-4 gap-8 relative z-10">
                <div class="text-center reveal">
                    <div class="text-5xl font-bold gradient-text mb-2">10K+</div>
                    <div class="text-gray-600">Đầu sách</div>
                </div>
                <div class="text-center reveal" style="transition-delay: 0.1s;">
                    <div class="text-5xl font-bold gradient-text mb-2">50K+</div>
                    <div class="text-gray-600">Độc giả</div>
                </div>
                <div class="text-center reveal" style="transition-delay: 0.2s;">
                    <div class="text-5xl font-bold gradient-text mb-2">1M+</div>
                    <div class="text-gray-600">Trang đã đọc</div>
                </div>
                <div class="text-center reveal" style="transition-delay: 0.3s;">
                    <div class="text-5xl font-bold gradient-text mb-2">4.8</div>
                    <div class="text-gray-600">Đánh giá trung bình</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-24 relative overflow-hidden bg-[#faf9f6]">
        <div class="container mx-auto px-6 relative z-10">
            <div class="text-center mb-16 reveal">
                <p class="text-sm uppercase tracking-wider text-[#FFB347] font-semibold mb-2">Đánh giá</p>
                <h2 class="text-4xl md:text-5xl font-bold mb-4 text-gray-900">Người dùng nói gì về chúng tôi</h2>
            </div>

            <div class="grid md:grid-cols-3 gap-8 mt-8">
                <!-- Testimonial 1 -->
                <div class="glass rounded-2xl p-6 card-modern reveal relative z-10">
                    <div class="flex items-center gap-1 mb-4 text-[#FFB347]">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="text-gray-700 mb-6">"Trải nghiệm đọc sách tuyệt vời! Giao diện đẹp, dễ sử dụng và có nhiều sách hay."</p>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#FFB347]/30 to-[#FFB347]/10 border border-[#FFB347]/30"></div>
                        <div>
                            <div class="font-semibold text-gray-900">Trần Văn Tri</div>
                            <div class="text-sm text-gray-600">Độc giả</div>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 2 -->
                <div class="glass rounded-2xl p-6 card-modern reveal relative z-10" style="transition-delay: 0.1s;">
                    <div class="flex items-center gap-1 mb-4 text-[#FFB347]">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="text-gray-700 mb-6">"Tính năng lưu tiến độ rất tiện lợi. Tôi có thể đọc trên nhiều thiết bị khác nhau."</p>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#4A7856]/30 to-[#4A7856]/10 border border-[#4A7856]/30"></div>
                        <div>
                            <div class="font-semibold text-gray-900">Nguyễn Thị Sương</div>
                            <div class="text-sm text-gray-600">Sinh viên</div>
                        </div>
                    </div>
                </div>

                <!-- Testimonial 3 -->
                <div class="glass rounded-2xl p-6 card-modern reveal relative z-10" style="transition-delay: 0.2s;">
                    <div class="flex items-center gap-1 mb-4 text-[#FFB347]">
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                        <i class="fas fa-star"></i>
                    </div>
                    <p class="text-gray-700 mb-6">"Giao diện đẹp, hiện đại. Tôi thích cách tùy chỉnh font và màu sắc để đọc thoải mái hơn."</p>
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-[#FFB347]/30 to-[#FFB347]/10 border border-[#FFB347]/30"></div>
                        <div>
                            <div class="font-semibold text-gray-900">Lê Văn Quân</div>
                            <div class="text-sm text-gray-600">Giáo viên</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-24 relative overflow-hidden bg-[#faf9f6]">
        <div class="container mx-auto px-6 relative z-10">
            <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#fff9e6] via-[#ffe8cc] to-[#fff9e6] p-12 text-center border border-[#FFB347]/20 shadow-xl">
                <!-- Animated Background -->
                <div class="absolute inset-0 overflow-hidden">
                    <div class="absolute top-0 left-1/4 w-96 h-96 bg-[#FFB347]/10 rounded-full blur-3xl float"></div>
                    <div class="absolute bottom-0 right-1/4 w-80 h-80 bg-[#4A7856]/10 rounded-full blur-3xl float" style="animation-delay: 2s;"></div>
                    <div class="absolute inset-0 shimmer"></div>
                </div>

                <div class="relative z-10 reveal">
                    <h2 class="text-4xl md:text-5xl font-bold mb-4 text-gray-900">
                        Bắt đầu hành trình <span class="animated-gradient">đọc sách</span> của bạn
                    </h2>
                    <p class="text-lg text-gray-600 mb-8 max-w-2xl mx-auto">
                        Tham gia cộng đồng những người yêu sách và khám phá thế giới đọc sách mới ngay hôm nay.
                    </p>
                    <div class="flex flex-wrap justify-center gap-4">
                        <a href="<?php echo $auth->isLoggedIn() ? 'dashboard.php' : 'register.php'; ?>" class="px-8 py-4 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold text-lg hover:shadow-lg glow-hover transition-all btn-modern relative z-10">
                            <span class="relative z-10"><?php echo $auth->isLoggedIn() ? 'Vào Dashboard' : 'Đăng ký miễn phí'; ?></span>
                        </a>
                        <a href="#features" class="px-8 py-4 glass border border-gray-200 rounded-lg font-semibold text-lg hover:bg-yellow-50 transition-all text-gray-700 relative z-10">
                            Tìm hiểu thêm
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script src="js/api-client.js"></script>
    <script src="js/auth.js"></script>
    <script src="js/navigation.js"></script>
    <script src="js/books-api-simple.js"></script>
    <script src="js/main.js"></script>
    <script>
        // Load popular books on homepage
        async function loadPopularBooks() {
            const loadingEl = document.getElementById('books-loading');
            const gridEl = document.getElementById('popular-books-grid');
            
            if (!gridEl) return;
            
            try {
                if (!window.BooksAPI) {
                    throw new Error('BooksAPI chưa được tải');
                }
                
                let books = await window.BooksAPI.getPopularBooks('fiction', 8);
                
                if (books.length === 0) {
                    books = await window.BooksAPI.getPopularBooks('literature', 8);
                }
                
                if (books.length === 0) {
                    books = await window.BooksAPI.searchBooks('best books', 8);
                }
                
                if (books.length === 0) {
                    const cachedBooks = window.BooksAPI.getBooksFromLocal('popularBooks');
                    if (cachedBooks && cachedBooks.length > 0) {
                        books = cachedBooks.slice(0, 8);
                    }
                }
                
                if (books.length === 0) {
                    if (loadingEl) loadingEl.style.display = 'none';
                    gridEl.innerHTML = `
                        <div class="col-span-full text-center py-12">
                            <i class="fas fa-book text-4xl text-gray-400 mb-4"></i>
                            <p class="text-gray-600">Không tìm thấy sách nào</p>
                        </div>
                    `;
                } else {
                    if (loadingEl) loadingEl.style.display = 'none';
                    gridEl.style.display = 'grid';
                    displayBooksOnHomepage(books);
                    window.BooksAPI.saveBooksToLocal('popularBooks', books);
                }
            } catch (error) {
                console.error('Lỗi khi tải sách:', error);
                if (loadingEl) {
                    loadingEl.innerHTML = `
                        <div class="text-center py-12">
                            <i class="fas fa-exclamation-triangle text-4xl text-red-400 mb-4"></i>
                            <p class="text-gray-600 mb-2">Không thể tải sách. Vui lòng thử lại sau.</p>
                            <button onclick="loadPopularBooks()" class="mt-4 px-6 py-2 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg transition-all">
                                Thử lại
                            </button>
                        </div>
                    `;
                }
            }
        }
        
        function displayBooksOnHomepage(books) {
            const gridEl = document.getElementById('popular-books-grid');
            if (!gridEl) return;
            
            gridEl.style.display = 'grid';
            
            const html = books.map((book, index) => {
                const coverUrl = (book.cover && typeof book.cover === 'string') 
                    ? book.cover.replace('http://', 'https://') 
                    : 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="200" height="300"%3E%3Crect fill="%23faf9f6" width="200" height="300"/%3E%3Ctext x="50%25" y="50%25" text-anchor="middle" dy=".3em" fill="%23FFB347" font-family="sans-serif" font-size="14"%3EBook%3C/text%3E%3C/svg%3E';
                
                return `
                    <div class="book-card filterable-item card-modern glass rounded-2xl overflow-hidden reveal relative z-10" 
                         style="transition-delay: ${index * 0.1}s;">
                        <a href="book-info.php?id=${book.id || ''}" class="block">
                            <div class="relative h-64 w-full overflow-hidden bg-gradient-to-br from-[#FFB347]/10 to-[#4A7856]/10">
                                <img 
                                    src="${coverUrl}" 
                                    alt="${escapeHtml(book.title || 'Book')}"
                                    class="h-full w-full object-cover hover:scale-105 transition-transform duration-300"
                                    onerror="this.src='data:image/svg+xml,%3Csvg xmlns=\\'http://www.w3.org/2000/svg\\' width=\\'200\\' height=\\'300\\'%3E%3Crect fill=\\'%23faf9f6\\' width=\\'200\\' height=\\'300\\'/%3E%3Ctext x=\\'50%25\\' y=\\'50%25\\' text-anchor=\\'middle\\' dy=\\'.3em\\' fill=\\'%23FFB347\\' font-family=\\'sans-serif\\' font-size=\\'14\\'%3EBook%3C/text%3E%3C/svg%3E'"
                                />
                            </div>
                        </a>
                        <div class="p-4 space-y-3">
                            <div>
                                <h3 class="book-title font-bold text-lg mb-1 line-clamp-2 text-gray-900">${escapeHtml(book.title)}</h3>
                                <p class="book-author text-gray-600 text-sm">${escapeHtml(book.author)}</p>
                            </div>
                            <button onclick="handleBookClick('${book.id}')" class="block w-full px-4 py-2 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg text-center text-sm font-semibold hover:shadow-lg glow-hover transition-all">
                                Xem chi tiết
                            </button>
                        </div>
                    </div>
                `;
            }).join('');
            
            gridEl.innerHTML = html;
            
            const revealElements = gridEl.querySelectorAll('.reveal');
            revealElements.forEach(el => el.classList.add('active'));
        }
        
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }
        
        function handleBookClick(bookId) {
            window.location.href = `book-info.php?id=${bookId}`;
        }
        
        // Load books when page loads
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                if (typeof window.BooksAPI !== 'undefined') {
                    loadPopularBooks();
                } else {
                    setTimeout(() => loadPopularBooks(), 500);
                }
            }, 300);
        });
    </script>

<?php include __DIR__ . '/includes/footer.php'; ?>

