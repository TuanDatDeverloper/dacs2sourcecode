<?php
/**
 * Admin Header - BookOnline
 * Header riêng cho admin pages với sidebar navigation
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/admin.php';

$auth = new Auth();
$auth->requireLogin();

$admin = new Admin();
$admin->requireAdmin();

$currentUser = $auth->getCurrentUser();
$currentPage = basename($_SERVER['PHP_SELF']);

$pageTitle = $pageTitle ?? 'Admin - BookOnline';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../css/style.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-[#faf9f6] text-gray-900">
    <!-- Admin Sidebar -->
    <div class="fixed left-0 top-0 bottom-0 w-64 glass border-r border-gray-200 z-40 overflow-y-auto">
        <div class="p-6">
            <!-- Logo -->
            <a href="../index.php" class="flex items-center gap-3 mb-8">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-[#FFB347] to-[#FF9500] flex items-center justify-center shadow-lg">
                    <i class="fas fa-book text-[#1a2a40] text-xl"></i>
                </div>
                <span class="text-xl font-bold gradient-text">BookOnline</span>
            </a>
            
            <!-- Admin Menu -->
            <nav class="space-y-2">
                <a href="index.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors <?php echo $currentPage === 'index.php' ? 'bg-[#FFB347]/10 text-[#FFB347]' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <i class="fas fa-chart-line w-5"></i>
                    <span class="font-medium">Dashboard</span>
                </a>
                
                <a href="users.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors <?php echo $currentPage === 'users.php' ? 'bg-[#FFB347]/10 text-[#FFB347]' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <i class="fas fa-users w-5"></i>
                    <span class="font-medium">Quản lý Users</span>
                </a>
                
                <a href="send-email.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors <?php echo $currentPage === 'send-email.php' ? 'bg-[#FFB347]/10 text-[#FFB347]' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <i class="fas fa-envelope w-5"></i>
                    <span class="font-medium">Gửi Email</span>
                </a>
                
                <a href="logs.php" class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors <?php echo $currentPage === 'logs.php' ? 'bg-[#FFB347]/10 text-[#FFB347]' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <i class="fas fa-history w-5"></i>
                    <span class="font-medium">Logs</span>
                </a>
                
                <hr class="my-4 border-gray-200">
                
                <a href="../dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-gray-100 transition-colors">
                    <i class="fas fa-arrow-left w-5"></i>
                    <span class="font-medium">Về User Dashboard</span>
                </a>
                
                <a href="../logout.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-red-600 hover:bg-red-50 transition-colors">
                    <i class="fas fa-sign-out-alt w-5"></i>
                    <span class="font-medium">Đăng xuất</span>
                </a>
            </nav>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="ml-64">
        <!-- Top Bar -->
        <div class="fixed top-0 right-0 left-64 glass border-b border-gray-200 z-30 px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900"><?php echo htmlspecialchars($pageTitle); ?></h2>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-sm text-gray-600">
                        <i class="fas fa-user-shield mr-2"></i>
                        <?php echo htmlspecialchars($currentUser['full_name'] ?? $currentUser['email']); ?>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Page Content -->
        <div class="pt-20">
            <!-- Content will be inserted here -->

