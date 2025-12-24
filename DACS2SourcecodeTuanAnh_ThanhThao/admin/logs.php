<?php
/**
 * Admin Logs - BookOnline
 * Xem lịch sử hoạt động admin
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin.php';

$auth = new Auth();
$auth->requireLogin();

$admin = new Admin();
$admin->requireAdmin();

$currentUser = $auth->getCurrentUser();
$pageTitle = 'Admin Logs - BookOnline';
include __DIR__ . '/../includes/admin-header.php';
?>

<div class="px-6 py-12">
    <!-- Header -->
    <div class="mb-8 reveal">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl md:text-4xl font-bold mb-2">
                    <span class="animated-gradient">Admin Logs</span>
                </h1>
                <p class="text-gray-600">Lịch sử hoạt động của quản trị viên</p>
            </div>
            <a href="index.php" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>Về Dashboard
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="glass rounded-2xl p-6 mb-6 reveal">
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tìm kiếm</label>
                <input
                    type="text"
                    id="search-input"
                    placeholder="Tìm theo action, admin..."
                    class="w-full px-4 py-2 rounded-lg border border-gray-200 bg-white text-gray-900 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50"
                />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Số lượng</label>
                <select
                    id="limit-select"
                    class="w-full px-4 py-2 rounded-lg border border-gray-200 bg-white text-gray-900 focus:border-[#FFB347] focus:outline-none focus:ring-2 focus:ring-[#FFB347]/50"
                >
                    <option value="50">50 logs</option>
                    <option value="100">100 logs</option>
                    <option value="200">200 logs</option>
                </select>
            </div>
            <div class="flex items-end">
                <button
                    onclick="loadLogs()"
                    class="w-full px-4 py-2 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg transition-all"
                >
                    <i class="fas fa-search mr-2"></i>Tìm kiếm
                </button>
            </div>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="glass rounded-2xl p-6 reveal">
        <div id="logs-loading" class="text-center py-12">
            <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-[#FFB347]"></div>
            <p class="mt-4 text-gray-600">Đang tải logs...</p>
        </div>

        <div id="logs-container" style="display: none;">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-3 px-4 font-semibold text-gray-900">Thời gian</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-900">Admin</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-900">Hành động</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-900">Đối tượng</th>
                            <th class="text-left py-3 px-4 font-semibold text-gray-900">Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody id="logs-table-body">
                        <!-- Logs will be loaded here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="../js/api-client.js"></script>
<script src="../js/admin.js"></script>
<script>
// Load logs
async function loadLogs() {
    const limit = document.getElementById('limit-select').value;
    
    try {
        document.getElementById('logs-loading').style.display = 'block';
        document.getElementById('logs-container').style.display = 'none';
        
        const result = await window.AdminHandler.getLogs(limit);
        
        if (result.success && result.logs) {
            renderLogs(result.logs);
            
            document.getElementById('logs-loading').style.display = 'none';
            document.getElementById('logs-container').style.display = 'block';
        }
    } catch (error) {
        console.error('Error loading logs:', error);
        document.getElementById('logs-loading').innerHTML = '<p class="text-red-500">Lỗi tải logs</p>';
    }
}

// Render logs
function renderLogs(logs) {
    const tbody = document.getElementById('logs-table-body');
    
    if (logs.length === 0) {
        tbody.innerHTML = '<tr><td colspan="5" class="text-center py-8 text-gray-500">Không có logs nào</td></tr>';
        return;
    }
    
    tbody.innerHTML = logs.map(log => `
        <tr class="border-b border-gray-100 hover:bg-gray-50 transition-colors">
            <td class="py-3 px-4 text-gray-900">
                <div class="text-sm">${new Date(log.created_at).toLocaleString('vi-VN')}</div>
            </td>
            <td class="py-3 px-4">
                <div class="font-medium text-gray-900">${escapeHtml(log.admin_name || log.admin_email || 'Unknown')}</div>
            </td>
            <td class="py-3 px-4">
                <span class="px-2 py-1 bg-[#FFB347]/10 text-[#FFB347] rounded text-sm font-medium">
                    ${escapeHtml(log.action)}
                </span>
            </td>
            <td class="py-3 px-4 text-gray-600">
                ${log.target_type ? `<span class="text-sm">${escapeHtml(log.target_type)}</span>` : '-'}
                ${log.target_id ? ` <span class="text-xs text-gray-400">#${log.target_id}</span>` : ''}
            </td>
            <td class="py-3 px-4">
                ${log.details ? `<div class="text-sm text-gray-600 max-w-xs truncate" title="${escapeHtml(log.details)}">${escapeHtml(log.details)}</div>` : '-'}
            </td>
        </tr>
    `).join('');
}

function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Initialize
loadLogs();

// Search on Enter
document.getElementById('search-input').addEventListener('keypress', (e) => {
    if (e.key === 'Enter') {
        loadLogs();
    }
});
</script>

        </div>
    </div>
</body>
</html>

