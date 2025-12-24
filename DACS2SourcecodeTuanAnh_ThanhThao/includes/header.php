<?php
/**
 * Header Component - BookOnline
 * Navigation với dynamic content dựa trên auth status
 */

require_once __DIR__ . '/auth.php';
$auth = new Auth();
$isLoggedIn = $auth->isLoggedIn();
$currentUser = $auth->getCurrentUser();
$isEmailVerified = $auth->isEmailVerified(); // Kiểm tra email đã verify chưa

// Get page title (set before including header)
$pageTitle = $pageTitle ?? 'BookOnline - Nền tảng đọc sách trực tuyến';
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <meta name="description" content="Nền tảng đọc sách trực tuyến hiện đại với nhiều tính năng thú vị">
    
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="css/style.css">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- PDF.js for PDF rendering -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
        section {
            position: relative;
            z-index: 1;
        }
        .hero-section {
            min-height: 100vh;
            padding-top: 6rem;
            padding-bottom: 6rem;
        }
    </style>
</head>
<body class="bg-[#faf9f6] text-gray-900">
    <!-- Navigation -->
    <nav class="navbar fixed top-0 left-0 right-0 z-50 px-6 py-4">
        <div class="container mx-auto flex items-center justify-between">
            <!-- Logo -->
            <a href="index.php" class="flex items-center gap-3 group">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-[#FFB347] to-[#FF9500] flex items-center justify-center shadow-lg glow">
                    <i class="fas fa-book text-[#1a2a40] text-xl"></i>
                </div>
                <span class="text-xl font-bold gradient-text">BookOnline</span>
            </a>

            <!-- Desktop Navigation -->
            <div class="hidden md:flex items-center gap-8">
                <a href="index.php" class="text-gray-700 hover:text-[#FFB347] transition-colors font-medium">Trang chủ</a>
                <?php if ($isLoggedIn && $isEmailVerified): ?>
                    <a href="dashboard.php" class="text-gray-700 hover:text-[#FFB347] transition-colors font-medium">Dashboard</a>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                        <a href="admin/index.php" class="text-gray-700 hover:text-[#FFB347] transition-colors font-medium">
                            <i class="fas fa-shield-alt mr-1"></i>Admin
                        </a>
                    <?php endif; ?>
                    
                    <!-- Kho sách Dropdown -->
                    <div class="relative group">
                        <a href="#" class="text-gray-700 hover:text-[#FFB347] transition-colors font-medium flex items-center gap-1 cursor-pointer">
                            <span>Kho sách</span>
                            <i class="fas fa-chevron-down text-xs transition-transform group-hover:rotate-180"></i>
                        </a>
                        
                        <!-- Dropdown Menu -->
                        <div class="absolute top-full left-0 mt-2 w-56 glass rounded-lg shadow-xl border border-gray-200 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 z-50">
                            <div class="p-2">
                                <a href="new-books.php" class="block px-4 py-3 text-sm text-gray-700 hover:bg-[#FFB347]/10 hover:text-[#FFB347] rounded transition-colors mb-1">
                                    <i class="fas fa-star mr-2 text-[#FFB347]"></i>
                                    <span class="font-semibold">Sách mới</span>
                                    <p class="text-xs text-gray-500 mt-1">Khám phá sách miễn phí</p>
                                </a>
                                <a href="history.php" class="block px-4 py-3 text-sm text-gray-700 hover:bg-[#4A7856]/10 hover:text-[#4A7856] rounded transition-colors">
                                    <i class="fas fa-bookmark mr-2 text-[#4A7856]"></i>
                                    <span class="font-semibold">Sách của tôi</span>
                                    <p class="text-xs text-gray-500 mt-1">Thư viện cá nhân</p>
                                </a>
                            </div>
                        </div>
                    </div>
                    <a href="shop.php" class="text-gray-700 hover:text-[#FFB347] transition-colors font-medium">
                        <i class="fas fa-store mr-1"></i>Cửa hàng
                    </a>
                    <a href="quiz.php" class="text-gray-700 hover:text-[#FFB347] transition-colors font-medium">
                        <i class="fas fa-robot mr-1"></i>AI Quiz
                    </a>
                    <a href="bookshelf-3d.php" class="text-gray-700 hover:text-[#FFB347] transition-colors font-medium">
                        <i class="fas fa-cube mr-1"></i>Kệ sách 3D
                    </a>
                    <a href="inventory.php" class="text-gray-700 hover:text-[#FFB347] transition-colors font-medium">
                        <i class="fas fa-shopping-bag mr-1"></i>Túi đồ
                    </a>
                <?php elseif ($isLoggedIn && !$isEmailVerified): ?>
                    <!-- Hiển thị thông báo nếu chưa verify email -->
                    <a href="verify-email.php" class="text-yellow-600 hover:text-yellow-700 transition-colors font-medium">
                        <i class="fas fa-exclamation-triangle mr-1"></i>Xác nhận email
                    </a>
                <?php endif; ?>
                <a href="about.php" class="text-gray-700 hover:text-[#FFB347] transition-colors font-medium">Về chúng tôi</a>
            </div>

            <!-- Auth Buttons / User Info -->
            <div class="hidden md:flex items-center gap-4">
                <?php if ($isLoggedIn && $currentUser): ?>
                    <!-- User Profile Dropdown -->
                    <div class="relative" id="user-profile-dropdown">
                        <button id="user-profile-btn" class="flex items-center gap-3 px-4 py-2 glass rounded-lg border border-gray-200 hover:shadow-lg transition-all cursor-pointer">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#FFB347] to-[#FF9500] flex items-center justify-center">
                                <span class="text-white text-sm font-bold">
                                    <?php echo strtoupper(substr($currentUser['full_name'] ?: $currentUser['email'], 0, 1)); ?>
                                </span>
                            </div>
                            <div class="flex flex-col text-left">
                                <span class="text-sm font-semibold text-gray-900">
                                    <?php echo htmlspecialchars($currentUser['full_name'] ?: $currentUser['email']); ?>
                                </span>
                                <span class="text-xs text-[#FFB347] font-medium">
                                    <?php echo $currentUser['coins']; ?> Coins
                                </span>
                            </div>
                            <i class="fas fa-chevron-down text-xs text-gray-500 transition-transform" id="dropdown-arrow"></i>
                        </button>
                        
                        <!-- Dropdown Menu -->
                        <div id="user-dropdown-menu" class="absolute top-full right-0 mt-2 w-56 glass rounded-lg shadow-xl border border-gray-200 opacity-0 invisible transition-all duration-300 z-50">
                            <div class="p-2">
                                <a href="profile.php" class="block px-4 py-3 text-sm text-gray-700 hover:bg-[#FFB347]/10 hover:text-[#FFB347] rounded transition-colors">
                                    <i class="fas fa-user mr-2 text-[#FFB347]"></i>
                                    <span class="font-semibold">Thông tin cá nhân</span>
                                </a>
                                <div class="border-t border-gray-200 my-1"></div>
                                <a href="logout.php" class="block px-4 py-3 text-sm text-gray-700 hover:bg-red-50 hover:text-red-600 rounded transition-colors">
                                    <i class="fas fa-sign-out-alt mr-2 text-red-500"></i>
                                    <span class="font-semibold">Đăng xuất</span>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Login/Register -->
                    <a href="login.php" class="px-4 py-2 text-gray-700 hover:text-[#FFB347] transition-colors font-medium">Đăng nhập</a>
                    <a href="register.php" class="px-6 py-2 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold hover:shadow-lg glow-hover transition-all btn-modern">
                        Đăng ký
                    </a>
                <?php endif; ?>
            </div>

            <!-- Mobile Menu Button -->
            <button id="mobile-menu-btn" class="md:hidden text-gray-700 hover:text-[#FFB347]">
                <i class="fas fa-bars text-2xl"></i>
            </button>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden mt-4 pb-4">
            <div class="flex flex-col gap-4">
                <a href="index.php" class="text-gray-700 hover:text-[#FFB347] transition-colors">Trang chủ</a>
                <?php if ($isLoggedIn && $isEmailVerified): ?>
                    <a href="dashboard.php" class="text-gray-700 hover:text-[#FFB347] transition-colors">Dashboard</a>
                    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin']): ?>
                        <a href="admin/index.php" class="text-gray-700 hover:text-[#FFB347] transition-colors">
                            <i class="fas fa-shield-alt mr-1"></i>Admin
                        </a>
                    <?php endif; ?>
                    <a href="new-books.php" class="text-gray-700 hover:text-[#FFB347] transition-colors">
                        <i class="fas fa-star mr-1"></i>Sách mới
                    </a>
                    <a href="history.php" class="text-gray-700 hover:text-[#FFB347] transition-colors">
                        <i class="fas fa-bookmark mr-1"></i>Sách của tôi
                    </a>
                    <a href="shop.php" class="text-gray-700 hover:text-[#FFB347] transition-colors">
                        <i class="fas fa-store mr-1"></i>Cửa hàng
                    </a>
                    <a href="quiz.php" class="text-gray-700 hover:text-[#FFB347] transition-colors">
                        <i class="fas fa-robot mr-1"></i>AI Quiz
                    </a>
                    <a href="bookshelf-3d.php" class="text-gray-700 hover:text-[#FFB347] transition-colors">
                        <i class="fas fa-cube mr-1"></i>Kệ sách 3D
                    </a>
                    <a href="inventory.php" class="text-gray-700 hover:text-[#FFB347] transition-colors">
                        <i class="fas fa-shopping-bag mr-1"></i>Túi đồ
                    </a>
                <?php elseif ($isLoggedIn && !$isEmailVerified): ?>
                    <!-- Hiển thị thông báo nếu chưa verify email -->
                    <a href="verify-email.php" class="text-yellow-600 hover:text-yellow-700 transition-colors font-medium">
                        <i class="fas fa-exclamation-triangle mr-1"></i>Xác nhận email
                    </a>
                <?php endif; ?>
                <a href="about.php" class="text-gray-700 hover:text-[#FFB347] transition-colors">Về chúng tôi</a>
                
                <?php if ($isLoggedIn && $currentUser): ?>
                    <!-- User Info Mobile -->
                    <div class="pt-4 border-t border-gray-200">
                        <div class="flex items-center gap-3 px-4 py-2 glass rounded-lg border border-gray-200 mb-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#FFB347] to-[#FF9500] flex items-center justify-center">
                                <span class="text-white text-sm font-bold">
                                    <?php echo strtoupper(substr($currentUser['full_name'] ?: $currentUser['email'], 0, 1)); ?>
                                </span>
                            </div>
                            <div class="flex-1">
                                <div class="text-sm font-semibold text-gray-900">
                                    <?php echo htmlspecialchars($currentUser['full_name'] ?: $currentUser['email']); ?>
                                </div>
                                <div class="text-xs text-[#FFB347] font-medium">
                                    <?php echo $currentUser['coins']; ?> Coins
                                </div>
                            </div>
                        </div>
                        <a href="profile.php" class="w-full px-4 py-2 text-gray-700 hover:text-[#FFB347] transition-colors font-medium text-center block mb-2">
                            <i class="fas fa-user mr-1"></i>Thông tin cá nhân
                        </a>
                        <a href="logout.php" class="w-full px-4 py-2 text-gray-700 hover:text-red-600 transition-colors font-medium text-center block">
                            <i class="fas fa-sign-out-alt mr-1"></i>Đăng xuất
                        </a>
                    </div>
                <?php else: ?>
                    <!-- Auth Buttons Mobile -->
                    <div class="flex gap-4 pt-4 border-t border-gray-200">
                        <a href="login.php" class="px-4 py-2 text-gray-700 hover:text-[#FFB347] transition-colors">Đăng nhập</a>
                        <a href="register.php" class="px-6 py-2 bg-gradient-to-r from-[#FFB347] to-[#FF9500] text-white rounded-lg font-semibold">Đăng ký</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Mobile Menu Toggle Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuBtn = document.getElementById('mobile-menu-btn');
            const mobileMenu = document.getElementById('mobile-menu');
            
            if (mobileMenuBtn && mobileMenu) {
                mobileMenuBtn.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }
            
            // User Profile Dropdown
            const profileBtn = document.getElementById('user-profile-btn');
            const dropdownMenu = document.getElementById('user-dropdown-menu');
            const dropdownArrow = document.getElementById('dropdown-arrow');
            
            if (profileBtn && dropdownMenu) {
                profileBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    const isVisible = !dropdownMenu.classList.contains('invisible');
                    
                    if (isVisible) {
                        dropdownMenu.classList.add('invisible', 'opacity-0');
                        dropdownArrow.classList.remove('rotate-180');
                    } else {
                        dropdownMenu.classList.remove('invisible', 'opacity-0');
                        dropdownArrow.classList.add('rotate-180');
                    }
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(e) {
                    if (!profileBtn.contains(e.target) && !dropdownMenu.contains(e.target)) {
                        dropdownMenu.classList.add('invisible', 'opacity-0');
                        dropdownArrow.classList.remove('rotate-180');
                    }
                });
            }
        });
    </script>

