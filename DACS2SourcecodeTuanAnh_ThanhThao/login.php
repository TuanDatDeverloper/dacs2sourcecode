<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$auth = new Auth();

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    $currentUser = $auth->getCurrentUser();
    // Nếu là admin thì redirect đến admin panel
    if (isset($currentUser['is_admin']) && $currentUser['is_admin'] == 1) {
        header('Location: admin/index.php');
    } else {
        header('Location: dashboard.php');
    }
    exit;
}

$pageTitle = 'Đăng nhập - BookOnline';
include __DIR__ . '/includes/header-auth.php';
?>

<div class="min-h-screen flex items-center justify-center py-12 px-4">
    <!-- Login Container -->
    <div class="w-full max-w-md">
        <!-- Logo & Back Button -->
        <div class="text-center mb-8">
            <a href="index.php" class="inline-flex items-center gap-3 mb-6">
                <div class="w-12 h-12 rounded-lg bg-gradient-to-br from-[#FFB347] to-[#FF9500] flex items-center justify-center shadow-lg glow">
                    <i class="fas fa-book text-white text-xl"></i>
                </div>
                <span class="text-2xl font-bold gradient-text">BookOnline</span>
            </a>
            <a href="index.php" class="inline-flex items-center gap-2 text-gray-600 hover:text-[#FFB347] transition-colors text-sm">
                <i class="fas fa-arrow-left"></i>
                <span>Quay lại trang chủ</span>
            </a>
        </div>

        <!-- Login Card -->
        <div class="glass rounded-2xl p-8 shadow-xl card-modern">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Chào mừng trở lại!</h1>
                <p class="text-gray-600">Đăng nhập để tiếp tục hành trình đọc sách của bạn</p>
            </div>

            <!-- Error/Success Messages -->
            <div id="messageContainer" class="mb-4"></div>

            <!-- Login Form -->
            <form class="space-y-6" id="loginForm">
                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2 text-[#FFB347]"></i>Email
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        required
                        placeholder="your.email@example.com"
                        class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-white text-gray-900 placeholder:text-gray-400 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50 transition-all"
                    />
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2 text-[#FFB347]"></i>Mật khẩu
                    </label>
                    <div class="relative">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            placeholder="Nhập mật khẩu"
                            class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-white text-gray-900 placeholder:text-gray-400 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50 transition-all"
                        />
                        <button
                            type="button"
                            id="togglePassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#FFB347] transition-colors"
                        >
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- Remember & Forgot -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input
                            type="checkbox"
                            class="w-4 h-4 text-[#FFB347] border-gray-300 rounded focus:ring-[#FFB347]"
                        />
                        <span class="ml-2 text-sm text-gray-600">Ghi nhớ đăng nhập</span>
                    </label>
                    <a href="forgot-password.php" class="text-sm text-[#FFB347] hover:text-[#FF9500] transition-colors font-medium">
                        Quên mật khẩu?
                    </a>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    id="submitBtn"
                    class="w-full px-6 py-3 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg glow-hover transition-all btn-modern"
                >
                    <span class="relative z-10" id="submitText">Đăng nhập</span>
                </button>

                <!-- Divider -->
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-white text-gray-500">Hoặc đăng nhập với</span>
                    </div>
                </div>

                <!-- Social Login -->
                <div class="grid grid-cols-1 gap-4">
                    <button
                        type="button"
                        id="googleLoginBtn"
                        class="px-4 py-3 border-2 border-gray-200 rounded-lg hover:bg-gray-50 hover:border-red-300 transition-all flex items-center justify-center gap-2 text-gray-700 font-medium"
                    >
                        <i class="fab fa-google text-red-500 text-lg"></i>
                        <span>Đăng nhập với Google</span>
                    </button>
                </div>

                <!-- Sign Up Link -->
                <div class="text-center pt-4">
                    <p class="text-sm text-gray-600">
                        Chưa có tài khoản?
                        <a href="register.php" class="text-[#FFB347] hover:text-[#FF9500] font-semibold transition-colors">
                            Đăng ký ngay
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <!-- Google OAuth Library -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    
    <script src="js/api-client.js"></script>
    <script src="js/auth.js"></script>
    <script src="js/google-auth.js"></script>
    <script>
        // Toggle password visibility
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            eyeIcon.classList.toggle('fa-eye');
            eyeIcon.classList.toggle('fa-eye-slash');
        });

        // Form submission
        document.getElementById('loginForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const messageContainer = document.getElementById('messageContainer');
            
            // Simple validation
            if (!email || !password) {
                showMessage('Vui lòng điền đầy đủ thông tin', 'error');
                return;
            }
            
            // Disable button
            submitBtn.disabled = true;
            submitText.textContent = 'Đang đăng nhập...';
            
            try {
                // Use async login from auth.js
                const loginResult = await window.Auth.login(email, password);
                
                if (loginResult.success) {
                    showMessage('Đăng nhập thành công! Đang chuyển hướng...', 'success');
                    
                    // Redirect - Nếu là admin thì chuyển đến admin panel
                    setTimeout(() => {
                        const urlParams = new URLSearchParams(window.location.search);
                        const redirect = urlParams.get('redirect');
                        
                        if (redirect) {
                            window.location.href = redirect;
                        } else {
                            // Check if user is admin
                            // is_admin có thể là 1, '1', true, hoặc string
                            const isAdmin = loginResult.user && (
                                loginResult.user.is_admin == 1 || 
                                loginResult.user.is_admin === '1' || 
                                loginResult.user.is_admin === true ||
                                loginResult.user.is_admin === 'true'
                            );
                            
                            console.log('Login result:', loginResult.user);
                            console.log('Is admin:', isAdmin);
                            
                            if (isAdmin) {
                                window.location.href = 'admin/index.php';
                            } else {
                                window.location.href = 'dashboard.php';
                            }
                        }
                    }, 500);
                } else {
                    showMessage(loginResult.message || 'Đăng nhập thất bại', 'error');
                    submitBtn.disabled = false;
                    submitText.textContent = 'Đăng nhập';
                }
            } catch (error) {
                console.error('Login error:', error);
                showMessage('Lỗi: ' + error.message, 'error');
                submitBtn.disabled = false;
                submitText.textContent = 'Đăng nhập';
            }
        });
        
        function showMessage(message, type) {
            const messageContainer = document.getElementById('messageContainer');
            const bgColor = type === 'success' ? 'bg-green-100 border-green-400 text-green-700' : 'bg-red-100 border-red-400 text-red-700';
            
            messageContainer.innerHTML = `
                <div class="border ${bgColor} px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline">${message}</span>
                </div>
            `;
            
            // Auto hide after 5 seconds
            setTimeout(() => {
                messageContainer.innerHTML = '';
            }, 5000);
        }
    </script>
</div>
</body>
</html>

