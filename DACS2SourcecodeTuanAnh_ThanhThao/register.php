<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$auth = new Auth();

// Redirect if already logged in
if ($auth->isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$pageTitle = 'Đăng ký - BookOnline';
include __DIR__ . '/includes/header-auth.php';
?>

<div class="min-h-screen flex items-center justify-center py-12 px-4">
    <!-- Register Container -->
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

        <!-- Register Card -->
        <div class="glass rounded-2xl p-8 shadow-xl card-modern">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Tạo tài khoản mới</h1>
                <p class="text-gray-600">Tham gia cộng đồng đọc sách ngay hôm nay</p>
            </div>

            <!-- Error/Success Messages -->
            <div id="messageContainer" class="mb-4"></div>

            <!-- Register Form -->
            <form class="space-y-5" id="registerForm">
                <!-- Full Name -->
                <div>
                    <label for="fullName" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-2 text-[#FFB347]"></i>Họ và tên
                    </label>
                    <input
                        type="text"
                        id="fullName"
                        name="fullName"
                        required
                        placeholder="Nguyễn Văn A"
                        class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-white text-gray-900 placeholder:text-gray-400 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50 transition-all"
                    />
                </div>

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
                            placeholder="Tối thiểu 6 ký tự"
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

                <!-- Confirm Password -->
                <div>
                    <label for="confirmPassword" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2 text-[#FFB347]"></i>Xác nhận mật khẩu
                    </label>
                    <div class="relative">
                        <input
                            type="password"
                            id="confirmPassword"
                            name="confirmPassword"
                            required
                            placeholder="Nhập lại mật khẩu"
                            class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-white text-gray-900 placeholder:text-gray-400 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50 transition-all"
                        />
                        <button
                            type="button"
                            id="toggleConfirmPassword"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-[#FFB347] transition-colors"
                        >
                            <i class="fas fa-eye" id="eyeIcon2"></i>
                        </button>
                    </div>
                </div>

                <!-- Terms & Conditions -->
                <div class="flex items-start">
                    <input
                        type="checkbox"
                        id="terms"
                        name="terms"
                        required
                        class="mt-1 w-4 h-4 text-[#FFB347] border-gray-300 rounded focus:ring-[#FFB347]"
                    />
                    <label for="terms" class="ml-2 text-sm text-gray-600">
                        Tôi đồng ý với
                        <a href="#" class="text-[#FFB347] hover:text-[#FF9500] font-medium">Điều khoản dịch vụ</a>
                        và
                        <a href="#" class="text-[#FFB347] hover:text-[#FF9500] font-medium">Chính sách bảo mật</a>
                    </label>
                </div>

                <!-- Submit Button -->
                <button
                    type="submit"
                    id="submitBtn"
                    class="w-full px-6 py-3 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg glow-hover transition-all btn-modern"
                >
                    <span class="relative z-10" id="submitText">Đăng ký ngay</span>
                </button>

                <!-- Divider -->
                <div class="relative">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-200"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-4 bg-white text-gray-500">Hoặc đăng ký với</span>
                    </div>
                </div>

                <!-- Social Register -->
                <div class="grid grid-cols-2 gap-4">
                    <button
                        type="button"
                        class="px-4 py-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors flex items-center justify-center gap-2 text-gray-700"
                    >
                        <i class="fab fa-google text-red-500"></i>
                        <span class="text-sm font-medium">Google</span>
                    </button>
                    <button
                        type="button"
                        class="px-4 py-3 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors flex items-center justify-center gap-2 text-gray-700"
                    >
                        <i class="fab fa-facebook text-blue-600"></i>
                        <span class="text-sm font-medium">Facebook</span>
                    </button>
                </div>

                <!-- Login Link -->
                <div class="text-center pt-4">
                    <p class="text-sm text-gray-600">
                        Đã có tài khoản?
                        <a href="login.php" class="text-[#FFB347] hover:text-[#FF9500] font-semibold transition-colors">
                            Đăng nhập ngay
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>

    <script src="js/api-client.js"></script>
    <script src="js/auth.js"></script>
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

        // Toggle confirm password visibility
        const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
        const confirmPasswordInput = document.getElementById('confirmPassword');
        const eyeIcon2 = document.getElementById('eyeIcon2');

        toggleConfirmPassword.addEventListener('click', () => {
            const type = confirmPasswordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordInput.setAttribute('type', type);
            eyeIcon2.classList.toggle('fa-eye');
            eyeIcon2.classList.toggle('fa-eye-slash');
        });

        // Form validation
        document.getElementById('registerForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            const fullName = document.getElementById('fullName').value;
            const email = document.getElementById('email').value;
            const password = passwordInput.value;
            const confirmPassword = confirmPasswordInput.value;
            const submitBtn = document.getElementById('submitBtn');
            const submitText = document.getElementById('submitText');
            const messageContainer = document.getElementById('messageContainer');

            if (password !== confirmPassword) {
                showMessage('Mật khẩu xác nhận không khớp!', 'error');
                return;
            }

            if (password.length < 6) {
                showMessage('Mật khẩu phải có ít nhất 6 ký tự!', 'error');
                return;
            }

            // Disable button
            submitBtn.disabled = true;
            submitText.textContent = 'Đang đăng ký...';

            try {
                const result = await window.Auth.register(email, password, fullName);
                
                if (result.success) {
                    showMessage('Đăng ký thành công! Đang gửi email xác nhận...', 'success');
                    
                    // Store user data
                    if (typeof window.Auth !== 'undefined' && window.Auth.setUser) {
                        window.Auth.setUser(result.user);
                    }
                    
                    // Send verification email
                    try {
                        const verifyResponse = await fetch('api/verification.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                action: 'send_email_verification'
                            })
                        });
                        
                        const verifyData = await verifyResponse.json();
                        
                        if (verifyData.success) {
                            showMessage('Email xác nhận đã được gửi! Vui lòng kiểm tra hộp thư.', 'success');
                            setTimeout(() => {
                                window.location.href = 'verify-email.php';
                            }, 2000);
                        } else {
                            showMessage('Đăng ký thành công! Vui lòng xác nhận email.', 'success');
                            setTimeout(() => {
                                window.location.href = 'verify-email.php';
                            }, 2000);
                        }
                    } catch (error) {
                        showMessage('Đăng ký thành công! Vui lòng xác nhận email.', 'success');
                        setTimeout(() => {
                            window.location.href = 'verify-email.php';
                        }, 2000);
                    }
                } else {
                    showMessage(result.message || 'Đăng ký thất bại', 'error');
                    submitBtn.disabled = false;
                    submitText.textContent = 'Đăng ký ngay';
                }
            } catch (error) {
                console.error('Register error:', error);
                showMessage('Lỗi: ' + error.message, 'error');
                submitBtn.disabled = false;
                submitText.textContent = 'Đăng ký ngay';
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

