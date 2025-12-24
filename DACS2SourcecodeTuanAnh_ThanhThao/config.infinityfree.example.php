<?php
/**
 * CONFIG MẪU CHO INFINITYFREE
 * 
 * HƯỚNG DẪN:
 * 1. Copy nội dung file này
 * 2. Mở file includes/config.php trên server
 * 3. Tìm và thay thế các dòng tương ứng
 * 4. Điền thông tin của bạn vào
 */

// ============================================
// MYSQL CONFIGURATION - THAY ĐỔI PHẦN NÀY
// ============================================
// Lấy thông tin từ: Control Panel → MySQL Databases

define('DB_HOST', 'sqlXXX.infinityfree.com'); // Thay XXX bằng số của bạn
define('DB_USER', 'if0_40750024'); // Username database của bạn
define('DB_PASS', 'YOUR_DB_PASSWORD'); // Password database của bạn
define('DB_NAME_MYSQL', 'if0_40750024_hoa'); // Tên database của bạn

// ============================================
// SITE CONFIGURATION - THAY ĐỔI PHẦN NÀY
// ============================================
// URL website của bạn (phải dùng HTTPS)

define('SITE_URL', 'https://yourdomain.epizy.com'); // Thay yourdomain bằng domain của bạn

// ============================================
// EMAIL/SMTP CONFIGURATION - TÙY CHỌN
// ============================================
// Nếu muốn dùng email, cần tạo Gmail App Password

define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com'); // Email Gmail của bạn
define('SMTP_PASS', 'your-app-password'); // App Password (16 ký tự)
define('SMTP_FROM_EMAIL', 'your-email@gmail.com');
define('SMTP_FROM_NAME', 'BookOnline');

// ============================================
// GOOGLE OAUTH - TÙY CHỌN
// ============================================
// Nếu muốn dùng Google Login

define('GOOGLE_CLIENT_ID', 'your-google-client-id.apps.googleusercontent.com');
define('GOOGLE_CLIENT_SECRET', 'your-google-client-secret');

// ============================================
// HUGGING FACE AI - TÙY CHỌN
// ============================================
// Nếu muốn dùng AI Quiz với Hugging Face

// define('HUGGINGFACE_API_KEY', 'hf_your_token_here');

// ============================================
// LƯU Ý
// ============================================
// - File này chỉ là mẫu, KHÔNG sử dụng trực tiếp
// - Phải sửa trong file includes/config.php trên server
// - Đảm bảo đã import database trước khi test
// - Test từng chức năng sau khi deploy

