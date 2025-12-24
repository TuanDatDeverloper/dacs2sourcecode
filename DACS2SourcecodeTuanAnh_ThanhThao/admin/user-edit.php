<?php
/**
 * Admin Edit User - BookOnline
 * Trang sửa thông tin user chi tiết
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin.php';

$auth = new Auth();
$auth->requireLogin();

$admin = new Admin();
$admin->requireAdmin();

$userId = intval($_GET['id'] ?? 0);

if (!$userId) {
    header('Location: users.php');
    exit;
}

$user = $admin->getUser($userId);

if (!$user) {
    header('Location: users.php');
    exit;
}

$currentUser = $auth->getCurrentUser();
$pageTitle = 'Sửa User - Admin';
include __DIR__ . '/../includes/admin-header.php';
?>

<div class="px-6 py-12">
    <!-- Header -->
    <div class="mb-8 reveal">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold mb-2">
                    <span class="animated-gradient">Sửa thông tin User</span>
                </h1>
                <p class="text-gray-600">ID: <?php echo htmlspecialchars($user['id']); ?></p>
            </div>
            <a href="users.php" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Quay lại
            </a>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-8">
        <!-- Main Form -->
        <div class="lg:col-span-2">
            <div class="glass rounded-2xl p-8 card-modern reveal">
                <div id="message-container" class="mb-6"></div>

                <form id="edit-user-form" class="space-y-6">
                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user['id']); ?>">

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-envelope mr-2 text-[#FFB347]"></i>Email *
                        </label>
                        <input
                            type="email"
                            name="email"
                            value="<?php echo htmlspecialchars($user['email']); ?>"
                            required
                            class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-white text-gray-900 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50"
                        />
                    </div>

                    <!-- Username -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user mr-2 text-[#FFB347]"></i>Username
                        </label>
                        <input
                            type="text"
                            name="username"
                            value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>"
                            class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-white text-gray-900 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50"
                        />
                    </div>

                    <!-- Full Name -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-id-card mr-2 text-[#FFB347]"></i>Tên đầy đủ
                        </label>
                        <input
                            type="text"
                            name="full_name"
                            value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>"
                            class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-white text-gray-900 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50"
                        />
                    </div>

                    <!-- Coins -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-coins mr-2 text-[#FFB347]"></i>Book Coins
                        </label>
                        <input
                            type="number"
                            name="coins"
                            value="<?php echo htmlspecialchars($user['coins'] ?? 0); ?>"
                            min="0"
                            class="w-full px-4 py-3 rounded-lg border border-gray-200 bg-white text-gray-900 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50"
                        />
                    </div>

                    <!-- Checkboxes -->
                    <div class="grid md:grid-cols-3 gap-4">
                        <label class="flex items-center gap-3 p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                            <input
                                type="checkbox"
                                name="email_verified"
                                <?php echo ($user['email_verified'] ?? 0) ? 'checked' : ''; ?>
                                class="w-5 h-5 text-[#FFB347] rounded focus:ring-[#FFB347]"
                            />
                            <div>
                                <div class="font-medium text-gray-900">Email đã verify</div>
                                <div class="text-sm text-gray-500">Email đã được xác nhận</div>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                            <input
                                type="checkbox"
                                name="is_admin"
                                <?php echo ($user['is_admin'] ?? 0) ? 'checked' : ''; ?>
                                class="w-5 h-5 text-[#FFB347] rounded focus:ring-[#FFB347]"
                            />
                            <div>
                                <div class="font-medium text-gray-900">Quản trị viên</div>
                                <div class="text-sm text-gray-500">Có quyền admin</div>
                            </div>
                        </label>

                        <label class="flex items-center gap-3 p-4 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                            <input
                                type="checkbox"
                                name="is_active"
                                <?php echo ($user['is_active'] ?? 1) ? 'checked' : ''; ?>
                                class="w-5 h-5 text-[#FFB347] rounded focus:ring-[#FFB347]"
                            />
                            <div>
                                <div class="font-medium text-gray-900">Đang hoạt động</div>
                                <div class="text-sm text-gray-500">Tài khoản không bị khóa</div>
                            </div>
                        </label>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-4 pt-4">
                        <button
                            type="submit"
                            class="flex-1 px-6 py-3 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg transition-all"
                        >
                            <i class="fas fa-save mr-2"></i>Lưu thay đổi
                        </button>
                        <a
                            href="users.php"
                            class="px-6 py-3 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
                        >
                            Hủy
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- User Info Card -->
            <div class="glass rounded-2xl p-6 card-modern reveal">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Thông tin User</h3>
                <div class="space-y-3">
                    <div>
                        <div class="text-sm text-gray-500">Ngày tạo</div>
                        <div class="font-medium text-gray-900">
                            <?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?>
                        </div>
                    </div>
                    <?php if ($user['last_login']): ?>
                    <div>
                        <div class="text-sm text-gray-500">Lần đăng nhập cuối</div>
                        <div class="font-medium text-gray-900">
                            <?php echo date('d/m/Y H:i', strtotime($user['last_login'])); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if ($user['oauth_provider']): ?>
                    <div>
                        <div class="text-sm text-gray-500">Đăng nhập bằng</div>
                        <div class="font-medium text-gray-900">
                            <i class="fab fa-<?php echo strtolower($user['oauth_provider']); ?> mr-1"></i>
                            <?php echo ucfirst($user['oauth_provider']); ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="glass rounded-2xl p-6 card-modern reveal">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Thao tác nhanh</h3>
                <div class="space-y-2">
                    <button
                        onclick="sendVerificationEmail()"
                        class="w-full px-4 py-2 bg-[#4A7856] text-white rounded-lg hover:bg-[#4A7856]/90 transition-colors text-sm"
                    >
                        <i class="fas fa-envelope mr-2"></i>Gửi email verification
                    </button>
                    <button
                        onclick="toggleUserActive()"
                        class="w-full px-4 py-2 <?php echo ($user['is_active'] ?? 1) ? 'bg-red-500' : 'bg-green-500'; ?> text-white rounded-lg hover:opacity-90 transition-colors text-sm"
                    >
                        <i class="fas <?php echo ($user['is_active'] ?? 1) ? 'fa-ban' : 'fa-unlock'; ?> mr-2"></i>
                        <?php echo ($user['is_active'] ?? 1) ? 'Khóa tài khoản' : 'Mở khóa tài khoản'; ?>
                    </button>
                    <button
                        onclick="deleteUser()"
                        class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm"
                    >
                        <i class="fas fa-trash mr-2"></i>Xóa tài khoản
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../js/api-client.js"></script>
<script src="../js/admin.js"></script>
<script>
const userId = <?php echo $user['id']; ?>;

// Save user
document.getElementById('edit-user-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const formData = new FormData(e.target);
    const data = {
        user_id: userId,
        email: formData.get('email'),
        username: formData.get('username'),
        full_name: formData.get('full_name'),
        coins: parseInt(formData.get('coins')) || 0,
        email_verified: formData.get('email_verified') ? 1 : 0,
        is_admin: formData.get('is_admin') ? 1 : 0,
        is_active: formData.get('is_active') ? 1 : 0
    };
    
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Đang lưu...';
    
    try {
        const result = await window.AdminHandler.updateUser(userId, data);
        
        if (result.success) {
            showMessage('Cập nhật thành công!', 'success');
            setTimeout(() => {
                window.location.href = 'users.php';
            }, 1000);
        } else {
            showMessage('Lỗi: ' + result.message, 'error');
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

// Send verification email
async function sendVerificationEmail() {
    if (!confirm('Gửi email verification cho user này?')) return;
    
    try {
        const response = await fetch('../api/verification.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: 'send_email_verification',
                email: document.querySelector('input[name="email"]').value
            })
        });
        
        const data = await response.json();
        
        if (data.success) {
            showMessage('Email verification đã được gửi!', 'success');
        } else {
            showMessage('Lỗi: ' + data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('Lỗi: ' + error.message, 'error');
    }
}

// Toggle user active
async function toggleUserActive() {
    const isActive = document.querySelector('input[name="is_active"]').checked;
    const action = isActive ? 'mở khóa' : 'khóa';
    
    if (!confirm(`Bạn có chắc muốn ${action} user này?`)) return;
    
    try {
        const result = await window.AdminHandler.toggleUserActive(userId, !isActive);
        
        if (result.success) {
            showMessage(result.message, 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            showMessage('Lỗi: ' + result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showMessage('Lỗi: ' + error.message, 'error');
    }
}

// Delete user
async function deleteUser() {
    if (!confirm('Bạn có chắc muốn XÓA user này? Hành động này không thể hoàn tác!')) return;
    
    if (!confirm('Xác nhận lại: Bạn thực sự muốn xóa user này?')) return;
    
    try {
        const result = await window.AdminHandler.deleteUser(userId);
        
        if (result.success) {
            showMessage('Đã xóa user thành công!', 'success');
            setTimeout(() => {
                window.location.href = 'users.php';
            }, 1000);
        } else {
            showMessage('Lỗi: ' + result.message, 'error');
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
</script>

        </div>
    </div>
</body>
</html>

