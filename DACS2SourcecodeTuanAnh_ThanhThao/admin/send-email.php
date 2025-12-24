<?php
/**
 * Admin Page - Gửi email thông báo
 * Trang admin để gửi email thủ công hoặc xem logs
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/email.php';

$auth = new Auth();
$auth->requireLogin();

$currentUser = $auth->getCurrentUser();
$emailService = new EmailService();

$pageTitle = 'Gửi Email - BookOnline';

// Check admin
require_once __DIR__ . '/../includes/admin.php';
$admin = new Admin();
$admin->requireAdmin();

include __DIR__ . '/../includes/admin-header.php';
?>

<div class="px-6 py-12">
    <h1 class="text-3xl font-bold mb-8">
        <span class="animated-gradient">Gửi Email Thông Báo</span>
    </h1>
    
    <div class="grid md:grid-cols-2 gap-8">
        <!-- Send Email Form -->
        <div class="glass rounded-2xl p-8">
            <h2 class="text-2xl font-bold mb-6">Gửi Email Nhắc Nhở</h2>
            
            <div id="messageContainer" class="mb-4"></div>
            
            <div class="space-y-4">
                <button
                    onclick="sendReminderToMe()"
                    class="w-full px-6 py-3 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg transition-all"
                >
                    <i class="fas fa-envelope mr-2"></i>Gửi email nhắc nhở cho tôi
                </button>
                
                <button
                    onclick="sendRemindersToAll()"
                    class="w-full px-6 py-3 bg-gradient-to-r from-[#4A7856] to-[#4A7856]/90 text-white rounded-lg font-semibold hover:shadow-lg transition-all"
                >
                    <i class="fas fa-users mr-2"></i>Gửi email nhắc nhở cho tất cả
                </button>
            </div>
            
            <hr class="my-6 border-gray-200">
            
            <h3 class="text-xl font-bold mb-4">Gửi Email Tùy Chỉnh</h3>
            
            <form id="customEmailForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Gửi đến</label>
                    <input
                        type="email"
                        id="emailTo"
                        value="<?php echo htmlspecialchars($currentUser['email']); ?>"
                        class="w-full px-4 py-2 rounded-lg border border-gray-200 bg-white text-gray-900 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50"
                        required
                    />
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Tiêu đề</label>
                    <input
                        type="text"
                        id="emailSubject"
                        placeholder="Nhập tiêu đề email"
                        class="w-full px-4 py-2 rounded-lg border border-gray-200 bg-white text-gray-900 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50"
                        required
                    />
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nội dung</label>
                    <textarea
                        id="emailMessage"
                        rows="6"
                        placeholder="Nhập nội dung email (HTML được hỗ trợ)"
                        class="w-full px-4 py-2 rounded-lg border border-gray-200 bg-white text-gray-900 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50"
                        required
                    ></textarea>
                </div>
                
                <button
                    type="submit"
                    class="w-full px-6 py-3 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg transition-all"
                >
                    <i class="fas fa-paper-plane mr-2"></i>Gửi Email
                </button>
            </form>
        </div>
        
        <!-- Email Logs -->
        <div class="glass rounded-2xl p-8">
            <h2 class="text-2xl font-bold mb-6">Lịch sử Email</h2>
            
            <div id="emailLogs" class="space-y-2 max-h-96 overflow-y-auto">
                <p class="text-gray-500 text-center py-8">Đang tải...</p>
            </div>
        </div>
    </div>
</div>

<script src="../js/api-client.js"></script>
<script>
    // Send reminder to current user
    async function sendReminderToMe() {
        try {
            const result = await fetch('../api/email.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'send_reminder'
                })
            });
            
            const data = await result.json();
            showMessage(data.message || (data.success ? 'Email đã được gửi!' : 'Lỗi gửi email'), data.success ? 'success' : 'error');
        } catch (error) {
            showMessage('Lỗi: ' + error.message, 'error');
        }
    }
    
    // Send reminders to all users
    async function sendRemindersToAll() {
        if (!confirm('Bạn có chắc muốn gửi email nhắc nhở cho TẤT CẢ người dùng?')) {
            return;
        }
        
        try {
            const result = await fetch('../api/email.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'send_bulk_reminders'
                })
            });
            
            const data = await result.json();
            showMessage(data.message || 'Đã gửi email!', data.success ? 'success' : 'error');
        } catch (error) {
            showMessage('Lỗi: ' + error.message, 'error');
        }
    }
    
    // Send custom email
    document.getElementById('customEmailForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const to = document.getElementById('emailTo').value;
        const subject = document.getElementById('emailSubject').value;
        const message = document.getElementById('emailMessage').value;
        
        try {
            const result = await fetch('../api/email.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'send_custom',
                    to: to,
                    subject: subject,
                    message: message
                })
            });
            
            const data = await result.json();
            showMessage(data.message || (data.success ? 'Email đã được gửi!' : 'Lỗi gửi email'), data.success ? 'success' : 'error');
            
            if (data.success) {
                document.getElementById('customEmailForm').reset();
                document.getElementById('emailTo').value = '<?php echo htmlspecialchars($currentUser['email']); ?>';
                loadEmailLogs();
            }
        } catch (error) {
            showMessage('Lỗi: ' + error.message, 'error');
        }
    });
    
    // Load email logs
    async function loadEmailLogs() {
        try {
            const result = await fetch('../api/email.php?limit=20');
            const data = await result.json();
            
            const logsContainer = document.getElementById('emailLogs');
            
            if (!data.success || !data.logs || data.logs.length === 0) {
                logsContainer.innerHTML = '<p class="text-gray-500 text-center py-8">Chưa có email nào được gửi</p>';
                return;
            }
            
            logsContainer.innerHTML = data.logs.map(log => `
                <div class="p-3 bg-white rounded-lg border border-gray-200">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-900">${escapeHtml(log.to_email)}</span>
                        <span class="text-xs ${log.success ? 'text-green-600' : 'text-red-600'}">
                            ${log.success ? '✓' : '✗'}
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mb-1">${escapeHtml(log.subject)}</p>
                    <p class="text-xs text-gray-400">${new Date(log.sent_at).toLocaleString('vi-VN')}</p>
                </div>
            `).join('');
        } catch (error) {
            console.error('Error loading email logs:', error);
        }
    }
    
    function showMessage(message, type) {
        const container = document.getElementById('messageContainer');
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
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Load logs on page load
    loadEmailLogs();
</script>

        </div>
    </div>
</body>
</html>

