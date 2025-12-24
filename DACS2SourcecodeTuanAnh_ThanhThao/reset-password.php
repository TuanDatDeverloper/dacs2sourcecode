<?php
/**
 * Reset Password Page - BookOnline
 * Trang đặt lại mật khẩu với mã xác nhận
 */

$email = $_GET['email'] ?? '';
$pageTitle = 'Đặt lại mật khẩu - BookOnline';
include __DIR__ . '/includes/header.php';
?>

<div class="container mx-auto px-6 py-12">
    <div class="max-w-md mx-auto">
        <div class="glass rounded-2xl p-8 card-modern reveal">
            <div class="text-center mb-6">
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-[#FFB347]/20 to-[#FFB347]/10 border border-[#FFB347]/30 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-lock text-3xl text-[#FFB347]"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">
                    <span class="animated-gradient">Đặt lại mật khẩu</span>
                </h1>
                <p class="text-gray-600">Nhập mã xác nhận và mật khẩu mới</p>
            </div>

            <div id="message-container" class="mb-4"></div>

            <form id="reset-password-form" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input
                        type="email"
                        id="email-input"
                        value="<?php echo htmlspecialchars($email); ?>"
                        placeholder="your@email.com"
                        class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-white text-gray-900 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50"
                        required
                        readonly
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mã xác nhận (6 số)</label>
                    <input
                        type="text"
                        id="verification-code"
                        maxlength="6"
                        pattern="[0-9]{6}"
                        placeholder="000000"
                        class="w-full px-4 py-3 text-center text-2xl font-bold tracking-widest rounded-lg border-2 border-gray-200 bg-white text-gray-900 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50"
                        required
                        autocomplete="off"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Mật khẩu mới</label>
                    <input
                        type="password"
                        id="new-password"
                        placeholder="Tối thiểu 6 ký tự"
                        minlength="6"
                        class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-white text-gray-900 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50"
                        required
                        autocomplete="new-password"
                    />
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Xác nhận mật khẩu</label>
                    <input
                        type="password"
                        id="confirm-password"
                        placeholder="Nhập lại mật khẩu"
                        minlength="6"
                        class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-white text-gray-900 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50"
                        required
                        autocomplete="new-password"
                    />
                </div>

                <button
                    type="submit"
                    class="w-full px-6 py-3 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg transition-all"
                >
                    <i class="fas fa-check mr-2"></i>Đặt lại mật khẩu
                </button>

                <div class="text-center">
                    <button
                        type="button"
                        onclick="resendCode()"
                        class="text-sm text-[#FFB347] hover:text-[#FF9500] font-medium"
                    >
                        <i class="fas fa-redo mr-1"></i>Gửi lại mã
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="js/api-client.js"></script>
<script src="js/verification.js"></script>
<script>
// Auto-format code input
const codeInput = document.getElementById('verification-code');
codeInput.addEventListener('input', (e) => {
    e.target.value = e.target.value.replace(/\D/g, '').slice(0, 6);
});

// Reset password
document.getElementById('reset-password-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const email = document.getElementById('email-input').value.trim();
    const code = codeInput.value.trim();
    const newPassword = document.getElementById('new-password').value;
    const confirmPassword = document.getElementById('confirm-password').value;
    
    // Validation
    if (code.length !== 6) {
        showMessage('Vui lòng nhập đủ 6 số', 'error');
        return;
    }
    
    if (newPassword.length < 6) {
        showMessage('Mật khẩu phải có ít nhất 6 ký tự', 'error');
        return;
    }
    
    if (newPassword !== confirmPassword) {
        showMessage('Mật khẩu xác nhận không khớp', 'error');
        return;
    }
    
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Đang xử lý...';
    
    try {
        const response = await fetch('api/verification.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'reset_password',
                email: email,
                code: code,
                password: newPassword
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showMessage('Mật khẩu đã được đặt lại thành công! Đang chuyển đến trang đăng nhập...', 'success');
            setTimeout(() => {
                window.location.href = 'login.php';
            }, 2000);
        } else {
            showMessage(data.message || 'Lỗi đặt lại mật khẩu', 'error');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('Lỗi: ' + error.message, 'error');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
});

// Resend code
async function resendCode() {
    const email = document.getElementById('email-input').value.trim();
    
    if (!email) {
        showMessage('Vui lòng nhập email', 'error');
        return;
    }
    
    try {
        const response = await fetch('api/verification.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'send_password_reset',
                email: email
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showMessage('Mã reset password mới đã được gửi đến email của bạn', 'success');
        } else {
            showMessage(data.message || 'Không thể gửi mã', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('Lỗi: ' + error.message, 'error');
    }
}

function showMessage(message, type) {
    const container = document.getElementById('message-container');
    const bgColor = type === 'success' 
        ? 'bg-green-100 border-green-400 text-green-700' 
        : 'bg-red-100 border-red-400 text-red-700';
    
    container.innerHTML = `
        <div class="border ${bgColor} px-4 py-3 rounded relative" role="alert">
            <span class="block sm:inline">${message}</span>
        </div>
    `;
    
    setTimeout(() => {
        container.innerHTML = '';
    }, 5000);
}

// Auto-focus code input
codeInput.focus();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

