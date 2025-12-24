<?php
/**
 * Email Verification Page - BookOnline
 * Trang xác nhận email sau khi đăng ký
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$auth = new Auth();
$auth->requireLogin();

$currentUser = $auth->getCurrentUser();

// Nếu đã verify rồi, redirect
if ($currentUser && isset($currentUser['email_verified']) && $currentUser['email_verified'] == 1) {
    // Nếu là admin thì redirect đến admin panel
    if (isset($currentUser['is_admin']) && $currentUser['is_admin'] == 1) {
        header('Location: admin/index.php');
    } else {
        header('Location: dashboard.php');
    }
    exit;
}

$pageTitle = 'Xác nhận Email - BookOnline';
include __DIR__ . '/includes/header.php';
?>

<div class="container mx-auto px-6 py-12">
    <div class="max-w-md mx-auto">
        <div class="glass rounded-2xl p-8 card-modern reveal">
            <div class="text-center mb-6">
                <div class="w-16 h-16 rounded-full bg-gradient-to-br from-[#FFB347]/20 to-[#FFB347]/10 border border-[#FFB347]/30 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-envelope text-3xl text-[#FFB347]"></i>
                </div>
                <h1 class="text-2xl font-bold text-gray-900 mb-2">
                    <span class="animated-gradient">Xác nhận Email</span>
                </h1>
                <p class="text-gray-600">Vui lòng nhập mã xác nhận đã được gửi đến email của bạn</p>
            </div>

            <div id="message-container" class="mb-4"></div>

            <form id="verify-form" class="space-y-4">
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

                <button
                    type="submit"
                    class="w-full px-6 py-3 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg transition-all"
                >
                    <i class="fas fa-check mr-2"></i>Xác nhận
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

            <div class="mt-6 pt-6 border-t border-gray-200">
                <p class="text-sm text-gray-500 text-center">
                    Mã đã được gửi đến: <strong><?php echo htmlspecialchars($currentUser['email'] ?? ''); ?></strong>
                </p>
            </div>
        </div>
    </div>
</div>

<script src="js/api-client.js"></script>
<script src="js/verification.js"></script>
<script>
// Auto-focus và format code input
const codeInput = document.getElementById('verification-code');
codeInput.addEventListener('input', (e) => {
    e.target.value = e.target.value.replace(/\D/g, '').slice(0, 6);
});

// Verify email
document.getElementById('verify-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const code = codeInput.value.trim();
    
    if (code.length !== 6) {
        showMessage('Vui lòng nhập đủ 6 số', 'error');
        return;
    }
    
    try {
        const response = await fetch('api/verification.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'verify_email',
                code: code
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showMessage('Email đã được xác nhận thành công! Đang chuyển hướng...', 'success');
            setTimeout(() => {
                // Reload page để lấy thông tin user mới nhất (sau khi verify)
                window.location.reload();
            }, 1500);
        } else {
            showMessage(data.message || 'Mã xác nhận không đúng', 'error');
            codeInput.value = '';
            codeInput.focus();
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('Lỗi: ' + error.message, 'error');
    }
});

// Resend code
async function resendCode() {
    try {
        const response = await fetch('api/verification.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'send_email_verification'
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showMessage('Mã xác nhận mới đã được gửi đến email của bạn', 'success');
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

// Auto-focus on load
codeInput.focus();
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>

