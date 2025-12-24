<?php
/**
 * Admin Dashboard - BookOnline
 * Trang chủ admin panel
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/admin.php';

$auth = new Auth();
$auth->requireLogin();

$admin = new Admin();
$admin->requireAdmin();

$currentUser = $auth->getCurrentUser();
$pageTitle = 'Admin Dashboard - BookOnline';
include __DIR__ . '/../includes/admin-header.php';
?>

<div class="px-6 py-12">
    <!-- Header -->
    <div class="mb-8 reveal">
        <h1 class="text-3xl md:text-4xl font-bold mb-2">
            <span class="animated-gradient">Admin Dashboard</span>
        </h1>
        <p class="text-gray-600">Quản lý hệ thống BookOnline</p>
    </div>

    <!-- Loading State -->
    <div id="stats-loading" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-[#FFB347]"></div>
        <p class="mt-4 text-gray-600">Đang tải thống kê...</p>
    </div>

    <!-- Stats Cards -->
    <div id="stats-cards" class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8" style="display: none;">
        <div class="glass rounded-2xl p-6 card-modern reveal relative z-10">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#FFB347]/20 to-[#FFB347]/10 border border-[#FFB347]/30 flex items-center justify-center">
                    <i class="fas fa-users text-2xl text-[#FFB347]"></i>
                </div>
            </div>
            <div class="text-3xl font-bold text-gray-900 mb-1" id="stat-total-users">0</div>
            <div class="text-sm text-gray-600">Tổng số users</div>
        </div>

        <div class="glass rounded-2xl p-6 card-modern reveal relative z-10" style="transition-delay: 0.1s;">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#4A7856]/20 to-[#4A7856]/10 border border-[#4A7856]/30 flex items-center justify-center">
                    <i class="fas fa-user-plus text-2xl text-[#4A7856]"></i>
                </div>
            </div>
            <div class="text-3xl font-bold text-gray-900 mb-1" id="stat-new-users">0</div>
            <div class="text-sm text-gray-600">Users mới hôm nay</div>
        </div>

        <div class="glass rounded-2xl p-6 card-modern reveal relative z-10" style="transition-delay: 0.2s;">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#EF4444]/20 to-[#EF4444]/10 border border-[#EF4444]/30 flex items-center justify-center">
                    <i class="fas fa-envelope text-2xl text-[#EF4444]"></i>
                </div>
            </div>
            <div class="text-3xl font-bold text-gray-900 mb-1" id="stat-unverified">0</div>
            <div class="text-sm text-gray-600">Chưa verify email</div>
        </div>

        <div class="glass rounded-2xl p-6 card-modern reveal relative z-10" style="transition-delay: 0.3s;">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#EF4444]/20 to-[#EF4444]/10 border border-[#EF4444]/30 flex items-center justify-center">
                    <i class="fas fa-ban text-2xl text-[#EF4444]"></i>
                </div>
            </div>
            <div class="text-3xl font-bold text-gray-900 mb-1" id="stat-banned">0</div>
            <div class="text-sm text-gray-600">Users bị khóa</div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid lg:grid-cols-3 gap-8">
        <!-- Left Column - Charts & Quick Actions -->
        <div class="lg:col-span-2 space-y-8">
            <!-- User Growth Chart -->
            <div class="glass rounded-2xl p-6 card-modern reveal relative z-10">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">
                    <i class="fas fa-chart-line text-[#FFB347] mr-2"></i>
                    Tăng trưởng Users (30 ngày)
                </h2>
                <canvas id="userGrowthChart" style="max-height: 300px;"></canvas>
            </div>

            <!-- Quick Actions -->
            <div class="glass rounded-2xl p-6 card-modern reveal relative z-10">
                <h2 class="text-2xl font-bold text-gray-900 mb-6">
                    <i class="fas fa-bolt text-[#FFB347] mr-2"></i>
                    Thao tác nhanh
                </h2>
                <div class="grid md:grid-cols-2 gap-4">
                    <a href="users.php" class="px-6 py-4 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg text-center font-semibold hover:shadow-lg transition-all">
                        <i class="fas fa-users mr-2"></i>Quản lý Users
                    </a>
                    <a href="send-email.php" class="px-6 py-4 border border-gray-200 rounded-lg text-gray-700 text-center font-medium hover:bg-gray-50 transition-colors">
                        <i class="fas fa-envelope mr-2"></i>Gửi Email
                    </a>
                    <a href="settings.php" class="px-6 py-4 border border-gray-200 rounded-lg text-gray-700 text-center font-medium hover:bg-gray-50 transition-colors">
                        <i class="fas fa-cog mr-2"></i>Cài đặt
                    </a>
                    <a href="logs.php" class="px-6 py-4 border border-gray-200 rounded-lg text-gray-700 text-center font-medium hover:bg-gray-50 transition-colors">
                        <i class="fas fa-history mr-2"></i>Xem Logs
                    </a>
                </div>
            </div>
        </div>

        <!-- Right Column - Recent Activity -->
        <div class="space-y-8">
            <!-- Recent Users -->
            <div class="glass rounded-2xl p-6 card-modern reveal relative z-10">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-user-clock text-[#FFB347] mr-2"></i>
                    Users mới nhất
                </h2>
                <div id="recent-users" class="space-y-3">
                    <p class="text-gray-500 text-center py-4">Đang tải...</p>
                </div>
            </div>

            <!-- Recent Logs -->
            <div class="glass rounded-2xl p-6 card-modern reveal relative z-10">
                <h2 class="text-xl font-bold text-gray-900 mb-4">
                    <i class="fas fa-history text-[#FFB347] mr-2"></i>
                    Hoạt động gần đây
                </h2>
                <div id="recent-logs" class="space-y-2 max-h-96 overflow-y-auto">
                    <p class="text-gray-500 text-center py-4">Đang tải...</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="../js/api-client.js"></script>
<script src="../js/admin.js"></script>
<script>
// Load dashboard stats
async function loadDashboardStats() {
    try {
        const response = await fetch('../api/admin/stats.php');
        const data = await response.json();
        
        if (data.success) {
            // Update stats cards
            document.getElementById('stat-total-users').textContent = data.stats.total_users || 0;
            document.getElementById('stat-new-users').textContent = data.stats.new_users_today || 0;
            document.getElementById('stat-unverified').textContent = data.stats.unverified_users || 0;
            document.getElementById('stat-banned').textContent = data.stats.banned_users || 0;
            
            // Show stats cards
            document.getElementById('stats-loading').style.display = 'none';
            document.getElementById('stats-cards').style.display = 'grid';
            
            // Draw chart
            if (data.growth && data.growth.length > 0) {
                drawGrowthChart(data.growth);
            }
        }
    } catch (error) {
        console.error('Error loading stats:', error);
        document.getElementById('stats-loading').innerHTML = '<p class="text-red-500">Lỗi tải thống kê</p>';
    }
}

// Draw user growth chart
function drawGrowthChart(growthData) {
    const ctx = document.getElementById('userGrowthChart');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: growthData.map(item => new Date(item.date).toLocaleDateString('vi-VN')),
            datasets: [{
                label: 'Users mới',
                data: growthData.map(item => item.count),
                borderColor: '#FFB347',
                backgroundColor: 'rgba(255, 179, 71, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}

// Load recent users
async function loadRecentUsers() {
    try {
        const response = await fetch('../api/admin/users.php?limit=5&page=1');
        const data = await response.json();
        
        if (data.success && data.data.users) {
            const container = document.getElementById('recent-users');
            if (data.data.users.length === 0) {
                container.innerHTML = '<p class="text-gray-500 text-center py-4">Chưa có users</p>';
                return;
            }
            
            container.innerHTML = data.data.users.map(user => `
                <div class="p-3 bg-white rounded-lg border border-gray-200 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">${escapeHtml(user.full_name || user.username || user.email)}</p>
                            <p class="text-sm text-gray-500">${escapeHtml(user.email)}</p>
                        </div>
                        <div class="flex items-center gap-2">
                            ${user.email_verified ? '<i class="fas fa-check-circle text-green-500" title="Đã verify"></i>' : '<i class="fas fa-times-circle text-red-500" title="Chưa verify"></i>'}
                            ${user.is_active ? '' : '<i class="fas fa-ban text-red-500" title="Đã khóa"></i>'}
                        </div>
                    </div>
                    <p class="text-xs text-gray-400 mt-2">${new Date(user.created_at).toLocaleDateString('vi-VN')}</p>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading recent users:', error);
    }
}

// Load recent logs
async function loadRecentLogs() {
    try {
        const response = await fetch('../api/admin/logs.php?limit=10');
        const data = await response.json();
        
        if (data.success && data.logs) {
            const container = document.getElementById('recent-logs');
            if (data.logs.length === 0) {
                container.innerHTML = '<p class="text-gray-500 text-center py-4">Chưa có logs</p>';
                return;
            }
            
            container.innerHTML = data.logs.map(log => `
                <div class="p-3 bg-white rounded-lg border border-gray-200">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">${escapeHtml(log.action)}</p>
                            <p class="text-xs text-gray-500">${escapeHtml(log.admin_name || log.admin_email || 'Unknown')}</p>
                        </div>
                        <p class="text-xs text-gray-400">${new Date(log.created_at).toLocaleString('vi-VN')}</p>
                    </div>
                </div>
            `).join('');
        }
    } catch (error) {
        console.error('Error loading logs:', error);
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Initialize
loadDashboardStats();
loadRecentUsers();
loadRecentLogs();

// Reveal animations
document.addEventListener('DOMContentLoaded', () => {
    const reveals = document.querySelectorAll('.reveal');
    reveals.forEach((el, i) => {
        setTimeout(() => el.classList.add('active'), i * 100);
    });
});
</script>

        </div>
    </div>
</body>
</html>

