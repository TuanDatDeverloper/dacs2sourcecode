<?php
/**
 * Admin Users Management - BookOnline
 * Quản lý danh sách users
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin.php';

$auth = new Auth();
$auth->requireLogin();

$admin = new Admin();
$admin->requireAdmin();

$currentUser = $auth->getCurrentUser();
$pageTitle = 'Quản lý Users - Admin';
include __DIR__ . '/../includes/admin-header.php';
?>

<div class="px-6 py-12">
    <!-- Header -->
    <div class="mb-8 reveal">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold mb-2">
                    <span class="animated-gradient">Quản lý Users</span>
                </h1>
                <p class="text-gray-600">Xem và quản lý tất cả người dùng</p>
            </div>
            <a href="index.php" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Về Dashboard
            </a>
        </div>
    </div>

    <!-- Filters & Search -->
    <div class="glass rounded-2xl p-6 mb-6 reveal">
        <div class="grid md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <input
                    type="text"
                    id="search-input"
                    placeholder="Tìm kiếm theo email, tên, username..."
                    class="w-full px-4 py-2 rounded-lg border border-gray-200 bg-white text-gray-900 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50"
                />
            </div>
            <select
                id="filter-verified"
                class="px-4 py-2 rounded-lg border border-gray-200 bg-white text-gray-900 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50"
            >
                <option value="">Tất cả (Email Verified)</option>
                <option value="1">Đã verify</option>
                <option value="0">Chưa verify</option>
            </select>
            <select
                id="filter-active"
                class="px-4 py-2 rounded-lg border border-gray-200 bg-white text-gray-900 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50"
            >
                <option value="">Tất cả (Trạng thái)</option>
                <option value="1">Đang hoạt động</option>
                <option value="0">Đã khóa</option>
            </select>
        </div>
    </div>

    <!-- Users Table -->
    <div class="glass rounded-2xl p-6">
        <div id="users-loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-[#FFB347]"></div>
            <p class="mt-4 text-gray-600">Đang tải...</p>
        </div>

        <div id="users-table-container" style="display: none !important;">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-4 font-semibold text-gray-900">ID</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-900">Email</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-900">Tên</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-900">Coins</th>
                            <th class="text-center py-3 px-4 font-semibold text-gray-900">Trạng thái</th>
                            <th class="text-center py-3 px-4 font-semibold text-gray-900">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="users-table-body">
                        <!-- Users will be loaded here -->
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div id="pagination" class="mt-6 flex items-center justify-between">
                <!-- Pagination will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div id="edit-user-modal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden items-center justify-center">
    <div class="glass rounded-2xl p-8 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900">Sửa thông tin User</h2>
            <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <div id="edit-user-form-container">
            <!-- Form will be loaded here -->
        </div>
    </div>
</div>

<script src="../js/api-client.js"></script>
<script src="../js/admin.js"></script>
<script>
// Escape HTML để tránh XSS
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

let currentPage = 1;
let currentFilters = {};

// Load users
async function loadUsers(page = 1, filters = {}) {
    currentPage = page;
    currentFilters = filters;
    
    const params = new URLSearchParams({
        page: page,
        limit: 20,
        ...filters
    });
    
    try {
        document.getElementById('users-loading').style.display = 'block';
        document.getElementById('users-table-container').style.display = 'none';
        
        const response = await fetch(`../api/admin/users.php?${params}`);
        
        // Log response để debug
        console.log('API Response Status:', response.status);
        console.log('API Response URL:', response.url);
        
        const data = await response.json();
        console.log('API Response Data:', data);
        console.log('Data structure:', {
            success: data.success,
            hasData: !!data.data,
            hasUsers: !!(data.data && data.data.users),
            usersCount: data.data?.users?.length || 0
        });
        
        if (data.success && data.data) {
            try {
                console.log('Rendering users table with data:', data.data);
                renderUsersTable(data.data);
                renderPagination(data.data);
                
                // Hide loading và show table
                const loadingEl = document.getElementById('users-loading');
                const containerEl = document.getElementById('users-table-container');
                
                if (loadingEl) {
                    loadingEl.style.display = 'none';
                    console.log('Loading element hidden');
                } else {
                    console.error('users-loading element not found!');
                }
                
                if (containerEl) {
                    // Remove any classes that might hide it
                    containerEl.classList.remove('hidden', 'reveal');
                    // Force show với !important
                    containerEl.setAttribute('style', 'display: block !important; visibility: visible !important; opacity: 1 !important;');
                    console.log('Table container shown, display:', containerEl.style.display);
                    console.log('Table container computed style:', window.getComputedStyle(containerEl).display);
                    console.log('Table container classes:', containerEl.className);
                    console.log('Table container parent display:', window.getComputedStyle(containerEl.parentElement).display);
                } else {
                    console.error('users-table-container element not found!');
                }
                
                // Verify table body has content
                const tbody = document.getElementById('users-table-body');
                if (tbody) {
                    console.log('Table body innerHTML length:', tbody.innerHTML.length);
                    console.log('Table body has content:', tbody.innerHTML.length > 0);
                }
                
                console.log('Users table rendered successfully');
            } catch (renderError) {
                console.error('Error rendering users table:', renderError);
                alert('Lỗi hiển thị dữ liệu: ' + renderError.message);
            }
        } else {
            // Hiển thị lỗi nếu có
            console.error('API Error:', data.message);
            if (data.debug) {
                console.error('Debug Info:', data.debug);
            }
            alert('Lỗi: ' + (data.message || 'Không thể tải danh sách users'));
        }
    } catch (error) {
        console.error('Error loading users:', error);
        alert('Lỗi: ' + error.message);
        document.getElementById('users-loading').innerHTML = '<p class="text-red-500">Lỗi tải danh sách users</p>';
    }
}

// Render users table
function renderUsersTable(data) {
    console.log('renderUsersTable called with:', data);
    const tbody = document.getElementById('users-table-body');
    
    if (!tbody) {
        console.error('users-table-body element not found!');
        return;
    }
    
    if (!data || !data.users || data.users.length === 0) {
        console.log('No users to display');
        tbody.innerHTML = '<tr><td colspan="6" class="text-center py-8 text-gray-500">Không có users nào</td></tr>';
        return;
    }
    
    console.log('Rendering', data.users.length, 'users');
    
    tbody.innerHTML = data.users.map(user => `
        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
            <td class="py-3 px-4 text-gray-900">${user.id}</td>
            <td class="py-3 px-4">
                <div class="flex items-center gap-2">
                    <span class="text-gray-900">${escapeHtml(user.email)}</span>
                    ${user.email_verified ? '<i class="fas fa-check-circle text-green-500" title="Đã verify"></i>' : '<i class="fas fa-times-circle text-red-500" title="Chưa verify"></i>'}
                </div>
            </td>
            <td class="py-3 px-4 text-gray-900">${escapeHtml(user.full_name || user.username || '-')}</td>
            <td class="py-3 px-4 text-gray-900">${user.coins || 0}</td>
            <td class="py-3 px-4 text-center">
                <div class="flex items-center justify-center gap-2">
                    ${user.is_active ? '<span class="px-2 py-1 bg-green-100 text-green-700 rounded text-xs">Hoạt động</span>' : '<span class="px-2 py-1 bg-red-100 text-red-700 rounded text-xs">Đã khóa</span>'}
                    ${user.is_admin ? '<span class="px-2 py-1 bg-blue-100 text-blue-700 rounded text-xs">Admin</span>' : ''}
                </div>
            </td>
            <td class="py-3 px-4">
                <div class="flex items-center justify-center gap-2">
                    <button onclick="editUser(${user.id})" class="px-3 py-1 bg-[#FFB347] text-white rounded hover:bg-[#FF9500] transition-colors text-sm">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button onclick="toggleUserActive(${user.id}, ${user.is_active ? 0 : 1})" class="px-3 py-1 ${user.is_active ? 'bg-red-500' : 'bg-green-500'} text-white rounded hover:opacity-80 transition-colors text-sm">
                        <i class="fas ${user.is_active ? 'fa-ban' : 'fa-unlock'}"></i>
                    </button>
                    <button onclick="deleteUser(${user.id})" class="px-3 py-1 bg-red-600 text-white rounded hover:bg-red-700 transition-colors text-sm">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

// Render pagination
function renderPagination(data) {
    const container = document.getElementById('pagination');
    
    if (data.total_pages <= 1) {
        container.innerHTML = '';
        return;
    }
    
    let html = '<div class="flex items-center gap-2">';
    
    // Previous button
    if (data.page > 1) {
        html += `<button onclick="loadUsers(${data.page - 1}, currentFilters)" class="px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50">Trước</button>`;
    }
    
    // Page numbers
    for (let i = Math.max(1, data.page - 2); i <= Math.min(data.total_pages, data.page + 2); i++) {
        html += `<button onclick="loadUsers(${i}, currentFilters)" class="px-4 py-2 ${i === data.page ? 'bg-[#FFB347] text-white' : 'border border-gray-200 hover:bg-gray-50'} rounded-lg">${i}</button>`;
    }
    
    // Next button
    if (data.page < data.total_pages) {
        html += `<button onclick="loadUsers(${data.page + 1}, currentFilters)" class="px-4 py-2 border border-gray-200 rounded-lg hover:bg-gray-50">Sau</button>`;
    }
    
    html += `</div><div class="text-gray-600">Trang ${data.page} / ${data.total_pages} (${data.total} users)</div>`;
    
    container.innerHTML = html;
}

// Edit user
async function editUser(userId) {
    try {
            const response = await fetch('../api/admin/users.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'get', user_id: userId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            const user = data.user;
            const formContainer = document.getElementById('edit-user-form-container');
            
            formContainer.innerHTML = `
                <form id="edit-user-form" class="space-y-4">
                    <input type="hidden" name="user_id" value="${user.id}">
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" value="${escapeHtml(user.email)}" required
                            class="w-full px-4 py-2 rounded-lg border border-gray-200 bg-white text-gray-900 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                        <input type="text" name="username" value="${escapeHtml(user.username || '')}"
                            class="w-full px-4 py-2 rounded-lg border border-gray-200 bg-white text-gray-900 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tên đầy đủ</label>
                        <input type="text" name="full_name" value="${escapeHtml(user.full_name || '')}"
                            class="w-full px-4 py-2 rounded-lg border border-gray-200 bg-white text-gray-900 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Coins</label>
                        <input type="number" name="coins" value="${user.coins || 0}" min="0"
                            class="w-full px-4 py-2 rounded-lg border border-gray-200 bg-white text-gray-900 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50">
                    </div>
                    
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="email_verified" ${user.email_verified ? 'checked' : ''}
                                class="w-4 h-4 text-[#FFB347] rounded focus:ring-[#FFB347]">
                            <span class="text-sm text-gray-700">Email đã verify</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_admin" ${user.is_admin ? 'checked' : ''}
                                class="w-4 h-4 text-[#FFB347] rounded focus:ring-[#FFB347]">
                            <span class="text-sm text-gray-700">Admin</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" ${user.is_active ? 'checked' : ''}
                                class="w-4 h-4 text-[#FFB347] rounded focus:ring-[#FFB347]">
                            <span class="text-sm text-gray-700">Đang hoạt động</span>
                        </label>
                    </div>
                    
                    <div class="flex gap-4 pt-4">
                        <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg transition-all">
                            Lưu thay đổi
                        </button>
                        <button type="button" onclick="closeEditModal()" class="px-6 py-3 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                            Hủy
                        </button>
                    </div>
                </form>
            `;
            
            document.getElementById('edit-user-form').addEventListener('submit', async (e) => {
                e.preventDefault();
                await saveUser(userId);
            });
            
            document.getElementById('edit-user-modal').classList.remove('hidden');
            document.getElementById('edit-user-modal').classList.add('flex');
        }
    } catch (error) {
        console.error('Error loading user:', error);
        alert('Lỗi tải thông tin user');
    }
}

// Save user
async function saveUser(userId) {
    const form = document.getElementById('edit-user-form');
    const formData = new FormData(form);
    
    const data = {
        user_id: userId,
        email: formData.get('email'),
        username: formData.get('username'),
        full_name: formData.get('full_name'),
        coins: intval(formData.get('coins')),
        email_verified: formData.get('email_verified') ? 1 : 0,
        is_admin: formData.get('is_admin') ? 1 : 0,
        is_active: formData.get('is_active') ? 1 : 0
    };
    
    try {
            const response = await fetch('../api/admin/users.php', {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Cập nhật thành công!');
            closeEditModal();
            loadUsers(currentPage, currentFilters);
        } else {
            alert('Lỗi: ' + result.message);
        }
    } catch (error) {
        console.error('Error saving user:', error);
        alert('Lỗi lưu thông tin');
    }
}

// Toggle user active
async function toggleUserActive(userId, isActive) {
    if (!confirm(isActive ? 'Bạn có chắc muốn mở khóa user này?' : 'Bạn có chắc muốn khóa user này?')) {
        return;
    }
    
    try {
            const response = await fetch('../api/admin/users.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                action: isActive ? 'unban' : 'ban',
                user_id: userId
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            loadUsers(currentPage, currentFilters);
        } else {
            alert('Lỗi: ' + result.message);
        }
    } catch (error) {
        console.error('Error toggling user:', error);
        alert('Lỗi thao tác');
    }
}

// Delete user
async function deleteUser(userId) {
    if (!confirm('Bạn có chắc muốn XÓA user này? Hành động này không thể hoàn tác!')) {
        return;
    }
    
    try {
            const response = await fetch('../api/admin/users.php', {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ user_id: userId })
        });
        
        const result = await response.json();
        
        if (result.success) {
            alert('Đã xóa user thành công!');
            loadUsers(currentPage, currentFilters);
        } else {
            alert('Lỗi: ' + result.message);
        }
    } catch (error) {
        console.error('Error deleting user:', error);
        alert('Lỗi xóa user');
    }
}

// Close edit modal
function closeEditModal() {
    document.getElementById('edit-user-modal').classList.add('hidden');
    document.getElementById('edit-user-modal').classList.remove('flex');
}

// Search and filter
document.getElementById('search-input').addEventListener('input', (e) => {
    const filters = { ...currentFilters };
    if (e.target.value) {
        filters.search = e.target.value;
    } else {
        delete filters.search;
    }
    loadUsers(1, filters);
});

document.getElementById('filter-verified').addEventListener('change', (e) => {
    const filters = { ...currentFilters };
    if (e.target.value) {
        filters.email_verified = e.target.value;
    } else {
        delete filters.email_verified;
    }
    loadUsers(1, filters);
});

document.getElementById('filter-active').addEventListener('change', (e) => {
    const filters = { ...currentFilters };
    if (e.target.value) {
        filters.is_active = e.target.value;
    } else {
        delete filters.is_active;
    }
    loadUsers(1, filters);
});

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function intval(value) {
    return parseInt(value) || 0;
}

// Initialize
loadUsers();
</script>

        </div>
    </div>
</body>
</html>

