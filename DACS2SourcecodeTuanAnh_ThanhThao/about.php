<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$auth = new Auth();
$currentUser = $auth->getCurrentUser();

$pageTitle = 'Về chúng tôi - BookOnline';
include __DIR__ . '/includes/header.php';
?>

    <!-- Main Content -->
    <main class="pt-24 pb-12">
        <div class="container mx-auto px-6 max-w-4xl">
            <!-- Header -->
            <div class="text-center mb-12 reveal">
                <h1 class="text-4xl md:text-5xl font-bold mb-4">
                    <span class="animated-gradient">Về chúng tôi</span>
                </h1>
                <p class="text-lg text-gray-600">Khám phá câu chuyện đằng sau BookOnline</p>
            </div>

            <!-- Mission Section -->
            <div class="glass rounded-2xl p-8 card-modern mb-8 reveal">
                <div class="flex items-start gap-6">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-[#FFB347] to-[#FF9500] flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-bullseye text-white text-2xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold mb-3 text-gray-900">Sứ mệnh của chúng tôi</h2>
                        <p class="text-gray-700 leading-relaxed">
                            BookOnline được tạo ra với mục tiêu khuyến khích và phát triển văn hóa đọc sách trong cộng đồng. 
                            Chúng tôi tin rằng mỗi cuốn sách đều mang đến những giá trị riêng và việc đọc sách không chỉ là 
                            giải trí mà còn là cách để mở rộng kiến thức, phát triển tư duy và nuôi dưỡng tâm hồn.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Vision Section -->
            <div class="glass rounded-2xl p-8 card-modern mb-8 reveal">
                <div class="flex items-start gap-6">
                    <div class="w-16 h-16 rounded-full bg-gradient-to-br from-[#FFB347] to-[#FF9500] flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-eye text-white text-2xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold mb-3 text-gray-900">Tầm nhìn</h2>
                        <p class="text-gray-700 leading-relaxed">
                            Chúng tôi mong muốn trở thành nền tảng đọc sách trực tuyến hàng đầu, nơi mọi người có thể 
                            dễ dàng khám phá, đọc và chia sẻ những cuốn sách yêu thích. Với công nghệ AI hiện đại, 
                            chúng tôi tạo ra trải nghiệm đọc sách tương tác và thú vị, giúp người dùng không chỉ đọc mà 
                            còn hiểu sâu hơn về nội dung sách thông qua các câu hỏi và thử thách.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Features Section -->
            <div class="glass rounded-2xl p-8 card-modern mb-8 reveal">
                <h2 class="text-2xl font-bold mb-6 text-gray-900">Tính năng nổi bật</h2>
                <div class="grid md:grid-cols-2 gap-6">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-[#FFB347] to-[#FF9500] flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-book-open text-white"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-2">Thư viện sách phong phú</h3>
                            <p class="text-sm text-gray-600">Truy cập hàng ngàn cuốn sách từ nhiều thể loại khác nhau</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-[#FFB347] to-[#FF9500] flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-robot text-white"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-2">AI Quiz</h3>
                            <p class="text-sm text-gray-600">Trả lời câu hỏi về sách và nhận Book Coins</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-[#FFB347] to-[#FF9500] flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-store text-white"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-2">Cửa hàng ảo</h3>
                            <p class="text-sm text-gray-600">Mua đồ trang trí cho kệ sách 3D của bạn</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-[#FFB347] to-[#FF9500] flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-cube text-white"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-900 mb-2">Kệ sách 3D</h3>
                            <p class="text-sm text-gray-600">Trải nghiệm thư viện sách của bạn trong không gian 3D</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Team Section -->
            <div class="glass rounded-2xl p-8 card-modern mb-8 reveal">
                <h2 class="text-2xl font-bold mb-6 text-gray-900">Đội ngũ phát triển</h2>
                <p class="text-gray-700 leading-relaxed mb-4">
                    BookOnline được phát triển bởi một nhóm sinh viên đam mê công nghệ và yêu thích sách. 
                    Chúng tôi luôn nỗ lực để cải thiện trải nghiệm người dùng và mang đến những tính năng 
                    mới, hữu ích nhất.
                </p>
                <p class="text-gray-700 leading-relaxed">
                    Nếu bạn có bất kỳ góp ý hoặc câu hỏi nào, đừng ngần ngại liên hệ với chúng tôi!
                </p>
            </div>

            <!-- Contact Section -->
            <div class="glass rounded-2xl p-8 card-modern text-center reveal">
                <h2 class="text-2xl font-bold mb-4 text-gray-900">Liên hệ với chúng tôi</h2>
                <p class="text-gray-600 mb-6">Chúng tôi luôn sẵn sàng lắng nghe ý kiến của bạn</p>
                <div class="flex justify-center gap-6">
                    <a href="mailto:contact@bookonline.com" class="w-12 h-12 rounded-full bg-gradient-to-br from-[#FFB347] to-[#FF9500] flex items-center justify-center text-white hover:shadow-lg transition-all">
                        <i class="fas fa-envelope"></i>
                    </a>
                    <a href="#" class="w-12 h-12 rounded-full bg-gradient-to-br from-[#FFB347] to-[#FF9500] flex items-center justify-center text-white hover:shadow-lg transition-all">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="#" class="w-12 h-12 rounded-full bg-gradient-to-br from-[#FFB347] to-[#FF9500] flex items-center justify-center text-white hover:shadow-lg transition-all">
                        <i class="fab fa-twitter"></i>
                    </a>
                </div>
            </div>
        </div>
    </main>

<?php include __DIR__ . '/includes/footer.php'; ?>

