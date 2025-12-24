<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$auth = new Auth();
$auth->requireLogin();
$auth->requireVerifiedEmail();

$currentUser = $auth->getCurrentUser();
$pageTitle = 'Thông tin cá nhân - BookOnline';
include __DIR__ . '/includes/header.php';
?>

<!-- Main Content -->
<main class="pt-24 pb-12">
    <div class="container mx-auto px-6 max-w-4xl">
        <!-- Header -->
        <div class="text-center mb-8 reveal">
            <h1 class="text-4xl md:text-5xl font-bold mb-4">
                <span class="animated-gradient">Thông tin cá nhân</span>
            </h1>
            <p class="text-lg text-gray-600">Quản lý thông tin tài khoản của bạn</p>
        </div>

        <!-- Profile Card -->
        <div class="glass rounded-2xl p-8 card-modern reveal">
            <!-- Avatar Section -->
            <div class="text-center mb-8">
                <div class="w-24 h-24 rounded-full bg-gradient-to-br from-[#FFB347] to-[#FF9500] flex items-center justify-center mx-auto mb-4 shadow-lg">
                    <span class="text-white text-3xl font-bold">
                        <?php echo strtoupper(substr($currentUser['full_name'] ?: $currentUser['email'], 0, 1)); ?>
                    </span>
                </div>
                <h2 class="text-2xl font-bold text-gray-900 mb-2">
                    <?php echo htmlspecialchars($currentUser['full_name'] ?: 'Chưa có tên'); ?>
                </h2>
                <p class="text-gray-600"><?php echo htmlspecialchars($currentUser['email']); ?></p>
            </div>

            <!-- Profile Form -->
            <form id="profile-form" class="space-y-6">
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Full Name -->
                    <div>
                        <label for="full_name" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-user mr-2 text-[#FFB347]"></i>Họ và tên
                        </label>
                        <input 
                            type="text" 
                            id="full_name" 
                            name="full_name" 
                            value="<?php echo htmlspecialchars($currentUser['full_name'] ?? ''); ?>"
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB347] focus:border-transparent transition-all"
                            placeholder="Nhập họ và tên"
                        >
                    </div>

                    <!-- Username -->
                    <div>
                        <label for="username" class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-at mr-2 text-[#FFB347]"></i>Tên người dùng
                        </label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            value="<?php echo htmlspecialchars($currentUser['username'] ?? ''); ?>"
                            class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-[#FFB347] focus:border-transparent transition-all"
                            placeholder="Nhập tên người dùng"
                        >
                    </div>
                </div>

                <!-- Email (Read-only) -->
                <div>
                    <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-envelope mr-2 text-[#FFB347]"></i>Email
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="<?php echo htmlspecialchars($currentUser['email']); ?>"
                        class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-gray-100 cursor-not-allowed"
                        readonly
                        disabled
                    >
                    <p class="text-xs text-gray-500 mt-1">Email không thể thay đổi</p>
                </div>

                <!-- Coins Display -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">
                        <i class="fas fa-coins mr-2 text-[#FFB347]"></i>Book Coins
                    </label>
                    <div class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-gradient-to-r from-[#FFB347]/10 to-[#FF9500]/10">
                        <span class="text-2xl font-bold text-[#FFB347]">
                            <?php echo number_format($currentUser['coins'] ?? 0); ?> Coins
                        </span>
                    </div>
                </div>

                <!-- Account Info -->
                <div class="grid md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-calendar mr-2 text-[#FFB347]"></i>Ngày tạo tài khoản
                        </label>
                        <div class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-gray-50">
                            <?php 
                            $createdAt = $currentUser['created_at'] ?? '';
                            if ($createdAt) {
                                $date = new DateTime($createdAt);
                                echo $date->format('d/m/Y H:i');
                            } else {
                                echo 'N/A';
                            }
                            ?>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-check-circle mr-2 text-[#FFB347]"></i>Trạng thái email
                        </label>
                        <div class="w-full px-4 py-3 rounded-lg border border-gray-300 bg-gray-50">
                            <?php if ($currentUser['email_verified'] ?? 0): ?>
                                <span class="text-green-600 font-semibold">
                                    <i class="fas fa-check mr-1"></i>Đã xác nhận
                                </span>
                            <?php else: ?>
                                <span class="text-yellow-600 font-semibold">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>Chưa xác nhận
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="flex gap-4 pt-4">
                    <button 
                        type="submit" 
                        id="save-btn"
                        class="flex-1 px-6 py-3 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg glow-hover transition-all btn-modern"
                    >
                        <i class="fas fa-save mr-2"></i>Lưu thay đổi
                    </button>
                    <button 
                        type="button" 
                        id="cancel-btn"
                        class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-all"
                    >
                        Hủy
                    </button>
                </div>
            </form>

            <!-- Danger Zone -->
            <div class="mt-8 pt-8 border-t border-gray-300">
                <h3 class="text-lg font-bold text-red-600 mb-4">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Vùng nguy hiểm
                </h3>
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <p class="text-sm text-gray-700 mb-4">
                        Xóa tài khoản sẽ xóa vĩnh viễn tất cả dữ liệu của bạn bao gồm:
                    </p>
                    <ul class="text-sm text-gray-600 list-disc list-inside mb-4 space-y-1">
                        <li>Thông tin cá nhân</li>
                        <li>Lịch sử đọc sách</li>
                        <li>Tiến độ đọc</li>
                        <li>Book Coins và vật phẩm đã mua</li>
                        <li>Kết quả quiz</li>
                    </ul>
                    <button 
                        type="button" 
                        id="delete-account-btn"
                        class="px-6 py-3 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 transition-all"
                    >
                        <i class="fas fa-trash-alt mr-2"></i>Xóa tài khoản
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- Delete Account Modal -->
<div id="delete-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden items-center justify-center">
    <div class="bg-white rounded-2xl p-8 max-w-md mx-4 glass">
        <h3 class="text-2xl font-bold text-red-600 mb-4">
            <i class="fas fa-exclamation-triangle mr-2"></i>Xác nhận xóa tài khoản
        </h3>
        <p class="text-gray-700 mb-6">
            Bạn có chắc chắn muốn xóa tài khoản? Hành động này không thể hoàn tác.
        </p>
        <div class="mb-4">
            <label for="confirm-email" class="block text-sm font-semibold text-gray-700 mb-2">
                Nhập email để xác nhận: <span class="text-red-600"><?php echo htmlspecialchars($currentUser['email']); ?></span>
            </label>
            <input 
                type="email" 
                id="confirm-email" 
                class="w-full px-4 py-3 rounded-lg border border-gray-300 focus:ring-2 focus:ring-red-500 focus:border-transparent"
                placeholder="Nhập email của bạn"
            >
        </div>
        <div class="flex gap-4">
            <button 
                type="button" 
                id="confirm-delete-btn"
                class="flex-1 px-6 py-3 bg-red-600 text-white rounded-lg font-semibold hover:bg-red-700 transition-all"
            >
                Xóa tài khoản
            </button>
            <button 
                type="button" 
                id="cancel-delete-btn"
                class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 rounded-lg font-semibold hover:bg-gray-300 transition-all"
            >
                Hủy
            </button>
        </div>
    </div>
</div>

<!-- Success/Error Messages -->
<div id="message-container" class="fixed top-24 right-6 z-50"></div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const profileForm = document.getElementById('profile-form');
    const saveBtn = document.getElementById('save-btn');
    const cancelBtn = document.getElementById('cancel-btn');
    const deleteAccountBtn = document.getElementById('delete-account-btn');
    const deleteModal = document.getElementById('delete-modal');
    const confirmDeleteBtn = document.getElementById('confirm-delete-btn');
    const cancelDeleteBtn = document.getElementById('cancel-delete-btn');
    const confirmEmailInput = document.getElementById('confirm-email');
    const messageContainer = document.getElementById('message-container');

    // Show message
    function showMessage(message, type = 'success') {
        const messageEl = document.createElement('div');
        messageEl.className = `px-6 py-4 rounded-lg shadow-lg mb-4 glass ${
            type === 'success' ? 'bg-green-50 border border-green-200 text-green-800' : 
            'bg-red-50 border border-red-200 text-red-800'
        }`;
        messageEl.innerHTML = `
            <div class="flex items-center gap-3">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                <span>${message}</span>
            </div>
        `;
        messageContainer.appendChild(messageEl);
        
        setTimeout(() => {
            messageEl.style.opacity = '0';
            messageEl.style.transition = 'opacity 0.3s';
            setTimeout(() => messageEl.remove(), 300);
        }, 3000);
    }

    // Save profile
    profileForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const formData = {
            full_name: document.getElementById('full_name').value.trim(),
            username: document.getElementById('username').value.trim()
        };

        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Đang lưu...';

        try {
            const response = await fetch('api/profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'update',
                    ...formData
                })
            });

            const data = await response.json();

            if (data.success) {
                showMessage('Cập nhật thông tin thành công!', 'success');
                // Reload page after 1 second
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                showMessage(data.message || 'Có lỗi xảy ra khi cập nhật thông tin', 'error');
                saveBtn.disabled = false;
                saveBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Lưu thay đổi';
            }
        } catch (error) {
            showMessage('Có lỗi xảy ra khi kết nối đến server', 'error');
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="fas fa-save mr-2"></i>Lưu thay đổi';
        }
    });

    // Cancel button
    cancelBtn.addEventListener('click', function() {
        window.location.reload();
    });

    // Show delete modal
    deleteAccountBtn.addEventListener('click', function() {
        deleteModal.classList.remove('hidden');
        deleteModal.classList.add('flex');
        confirmEmailInput.value = '';
    });

    // Cancel delete
    cancelDeleteBtn.addEventListener('click', function() {
        deleteModal.classList.add('hidden');
        deleteModal.classList.remove('flex');
    });

    // Confirm delete
    confirmDeleteBtn.addEventListener('click', async function() {
        const email = confirmEmailInput.value.trim();
        const userEmail = '<?php echo htmlspecialchars($currentUser['email']); ?>';

        if (email !== userEmail) {
            showMessage('Email không khớp!', 'error');
            return;
        }

        confirmDeleteBtn.disabled = true;
        confirmDeleteBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Đang xóa...';

        try {
            const response = await fetch('api/profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'delete',
                    confirm_email: email
                })
            });

            const data = await response.json();

            if (data.success) {
                showMessage('Tài khoản đã được xóa thành công. Đang chuyển hướng...', 'success');
                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 2000);
            } else {
                showMessage(data.message || 'Có lỗi xảy ra khi xóa tài khoản', 'error');
                confirmDeleteBtn.disabled = false;
                confirmDeleteBtn.innerHTML = 'Xóa tài khoản';
            }
        } catch (error) {
            showMessage('Có lỗi xảy ra khi kết nối đến server', 'error');
            confirmDeleteBtn.disabled = false;
            confirmDeleteBtn.innerHTML = 'Xóa tài khoản';
        }
    });

    // Close modal when clicking outside
    deleteModal.addEventListener('click', function(e) {
        if (e.target === deleteModal) {
            deleteModal.classList.add('hidden');
            deleteModal.classList.remove('flex');
        }
    });
});
</script>

<?php include __DIR__ . '/includes/footer.php'; ?>
