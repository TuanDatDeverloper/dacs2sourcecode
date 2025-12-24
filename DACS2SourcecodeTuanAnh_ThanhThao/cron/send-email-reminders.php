<?php
/**
 * Cron Job - Gửi email nhắc nhở đọc sách
 * Chạy file này định kỳ (ví dụ: mỗi ngày lúc 9h sáng)
 * 
 * Cách chạy:
 * 1. Windows Task Scheduler: tạo task chạy php.exe với file này
 * 2. Linux Cron: 0 9 * * * php /path/to/send-email-reminders.php
 * 3. Hoặc gọi qua URL: http://localhost/.../cron/send-email-reminders.php
 */

// Set execution time limit
set_time_limit(300); // 5 minutes

// Include required files
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/email.php';

// Optional: Add security token check
$token = $_GET['token'] ?? '';
$expectedToken = getenv('CRON_TOKEN') ?: 'your-secret-token-here';

if (!empty($expectedToken) && $token !== $expectedToken) {
    http_response_code(403);
    die('Unauthorized');
}

$emailService = new EmailService();

echo "Bắt đầu gửi email nhắc nhở...\n";
echo "Thời gian: " . date('Y-m-d H:i:s') . "\n\n";

$result = $emailService->sendReadingRemindersToAll();

echo "Kết quả:\n";
echo "- Thành công: {$result['success']}\n";
echo "- Thất bại: {$result['failed']}\n";
echo "- Tổng cộng: {$result['total']}\n";
echo "\nHoàn thành!\n";

// Log to file
$logFile = __DIR__ . '/../logs/email-reminders.log';
$logDir = dirname($logFile);
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

$logMessage = date('Y-m-d H:i:s') . " - Sent: {$result['success']}, Failed: {$result['failed']}, Total: {$result['total']}\n";
file_put_contents($logFile, $logMessage, FILE_APPEND);
?>

