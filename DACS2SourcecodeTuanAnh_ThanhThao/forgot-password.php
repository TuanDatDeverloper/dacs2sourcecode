<?php
/**
 * Forgot Password Page - BookOnline
 * Trang yêu cầu reset password
 */

$pageTitle = 'Quên mật khẩu - BookOnline';
include __DIR__ . '/includes/header.php';
?>

<div class="container mx-auto px-6 py-12">
    <div class="max-w-md mx-auto">
        <div class="glass rounded-2xl p-8 card-modern reveal">
            <div class="text-center mb-6">
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-[#FFB347]/20 to-[#FFB347]/10 border border-[#FFB347]/30 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-key text-3xl text-[#FFB347]"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">
                    <span class="animated-gradient">Quên mật khẩu</span>
                </h1>
                <p class="text-gray-600">Nhập email của bạn để nhận mã đặt lại mật khẩu</p>
            </div>

            <div id="message-container" class="mb-4"></div>

            <form id="forgot-password-form" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                    <input
                        type="email"
                        id="email-input"
                        placeholder="your@email.com"
                        class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-white text-gray-900 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50"
                        required
                        autocomplete="email"
                    />
                </div>

                <button
                    type="submit"
                    class="w-full px-6 py-3 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg transition-all"
                >
                    <i class="fas fa-paper-plane mr-2"></i>Gửi mã reset
                </button>

                <div class="text-center">
                    <a href="login.php" class="text-sm text-[#FFB347] hover:text-[#FF9500] font-medium">
                        <i class="fas fa-arrow-left mr-1"></i>Quay lại đăng nhập
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="js/api-client.js"></script>
<script src="js/verification.js"></script>
<script>
document.getElementById('forgot-password-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const email = document.getElementById('email-input').value.trim();
    
    if (!email) {
        showMessage('Vui lòng nhập email', 'error');
        return;
    }
    
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Đang gửi...';
    
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
            showMessage('Mã reset password đã được gửi đến email của bạn. Vui lòng kiểm tra hộp thư.', 'success');
            
            // Redirect to reset password page after 2 seconds
            setTimeout(() => {
                window.location.href = `reset-password.php?email=${encodeURIComponent(email)}`;
            }, 2000);
        } else {
            showMessage(data.message || 'Không thể gửi email', 'error');
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
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

