<?php
/**
 * Configuration File - BookOnline
 * Database và Site Configuration
 */

// Database Configuration
define('DB_TYPE', 'mysql'); // 'sqlite' hoặc 'mysql'
define('DB_NAME', 'book_online.db');
define('DB_PATH', __DIR__ . '/../database/' . DB_NAME);

// MySQL Configuration (nếu dùng MySQL)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', ''); // XAMPP mặc định password rỗng
define('DB_NAME_MYSQL', 'book_online');

// Site Configuration
define('SITE_URL', 'http://localhost/dacs2sourcecode/DACS2SourcecodeTuanAnh_ThanhThao');
define('UPLOAD_DIR', __DIR__ . '/../assets/uploads/');

// Session Configuration - Phải set TRƯỚC khi start session
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 nếu dùng HTTPS
    ini_set('session.gc_maxlifetime', 3600); // 1 hour
    session_start();
}

// Error Reporting
// Development: hiển thị errors
// Production: ẩn errors, chỉ log
$isDevelopment = (getenv('APP_ENV') === 'development' || 
                 strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false ||
                 strpos($_SERVER['HTTP_HOST'] ?? '', '127.0.0.1') !== false);

if ($isDevelopment) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0); // Ẩn errors trong production
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php-errors.log');
}

// Timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Google OAuth Configuration
// Lấy từ environment variables hoặc set trực tiếp
// Để bảo mật, nên dùng environment variables trong production
define('GOOGLE_CLIENT_ID', getenv('GOOGLE_CLIENT_ID') ?: '');
define('GOOGLE_CLIENT_SECRET', getenv('GOOGLE_CLIENT_SECRET') ?: '');

// Email/SMTP Configuration
// Có thể set trực tiếp hoặc dùng environment variables
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_PORT', getenv('SMTP_PORT') ?: 587);
define('SMTP_USER', getenv('SMTP_USER') ?: 'linhlac1235@gmail.com');
define('SMTP_PASS', getenv('SMTP_PASS') ?: 'furx jwgy ssig sbva');
define('SMTP_FROM_EMAIL', getenv('SMTP_FROM_EMAIL') ?: SMTP_USER); // Dùng SMTP_USER làm FROM nếu không set
define('SMTP_FROM_NAME', getenv('SMTP_FROM_NAME') ?: 'BookOnline');

