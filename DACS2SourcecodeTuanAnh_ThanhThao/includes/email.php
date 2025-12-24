<?php
/**
 * Email Class - BookOnline
 * Xử lý gửi email thông báo
 */

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';

// Try to load PHPMailer if available
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php')) {
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/Exception.php';
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
    require_once __DIR__ . '/../vendor/phpmailer/phpmailer/src/SMTP.php';
}

class EmailService {
    private $db;
    private $smtpHost;
    private $smtpPort;
    private $smtpUser;
    private $smtpPass;
    private $smtpFromEmail;
    private $smtpFromName;
    
    public function __construct() {
        $this->db = new Database();
        
        // SMTP Configuration (lấy từ config.php hoặc environment)
        $this->smtpHost = defined('SMTP_HOST') ? SMTP_HOST : (getenv('SMTP_HOST') ?: 'smtp.gmail.com');
        $this->smtpPort = defined('SMTP_PORT') ? SMTP_PORT : (getenv('SMTP_PORT') ?: 587);
        $this->smtpUser = defined('SMTP_USER') ? SMTP_USER : (getenv('SMTP_USER') ?: '');
        $this->smtpPass = defined('SMTP_PASS') ? SMTP_PASS : (getenv('SMTP_PASS') ?: '');
        $this->smtpFromEmail = defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : (getenv('SMTP_FROM_EMAIL') ?: 'noreply@bookonline.com');
        $this->smtpFromName = defined('SMTP_FROM_NAME') ? SMTP_FROM_NAME : (getenv('SMTP_FROM_NAME') ?: 'BookOnline');
    }
    
    /**
     * Gửi email đơn giản (sử dụng mail() function)
     * @param string $to Email người nhận
     * @param string $subject Tiêu đề
     * @param string $message Nội dung (HTML)
     * @param string $fromEmail Email người gửi
     * @param string $fromName Tên người gửi
     * @return bool
     */
    public function sendSimpleEmail($to, $subject, $message, $fromEmail = null, $fromName = null) {
        $fromEmail = $fromEmail ?: $this->smtpFromEmail;
        $fromName = $fromName ?: $this->smtpFromName;
        
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: $fromName <$fromEmail>\r\n";
        $headers .= "Reply-To: $fromEmail\r\n";
        $headers .= "X-Mailer: PHP/" . phpversion();
        
        $result = mail($to, $subject, $message, $headers);
        
        // Log email
        $this->logEmail($to, $subject, $result);
        
        return $result;
    }
    
    /**
     * Gửi email với SMTP (sử dụng PHPMailer nếu có, hoặc fallback về mail())
     * @param string $to Email người nhận
     * @param string $subject Tiêu đề
     * @param string $message Nội dung (HTML)
     * @param array $options Tùy chọn thêm
     * @return array ['success' => bool, 'message' => string]
     */
    public function sendEmail($to, $subject, $message, $options = []) {
        // Validate email
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Email không hợp lệ'];
        }
        
        // Try PHPMailer first if available
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            return $this->sendWithPHPMailer($to, $subject, $message, $options);
        }
        
        // Fallback to simple mail()
        if (!empty($this->smtpUser) && !empty($this->smtpPass)) {
            // Try to use SMTP with stream context
            return $this->sendWithSMTP($to, $subject, $message, $options);
        }
        
        // Use simple mail() function
        $result = $this->sendSimpleEmail($to, $subject, $message);
        
        return [
            'success' => $result,
            'message' => $result ? 'Email đã được gửi' : 'Không thể gửi email'
        ];
    }
    
    /**
     * Gửi email với PHPMailer
     */
    private function sendWithPHPMailer($to, $subject, $message, $options) {
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            
            // Enable verbose debug output (only in development)
            $isDevelopment = (getenv('APP_ENV') === 'development' || 
                           strpos($_SERVER['HTTP_HOST'] ?? '', 'localhost') !== false);
            if ($isDevelopment) {
                $mail->SMTPDebug = 2; // Enable verbose debug output
                $mail->Debugoutput = function($str, $level) {
                    error_log("PHPMailer Debug: $str");
                };
            }
            
            // SMTP Configuration
            $mail->isSMTP();
            $mail->Host = $this->smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $this->smtpUser;
            $mail->Password = $this->smtpPass;
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $this->smtpPort;
            $mail->CharSet = 'UTF-8';
            
            // Timeout
            $mail->Timeout = 30;
            
            // From
            $mail->setFrom($this->smtpFromEmail, $this->smtpFromName);
            
            // To
            $mail->addAddress($to);
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $message;
            $mail->AltBody = strip_tags($message); // Plain text version
            
            $mail->send();
            
            $this->logEmail($to, $subject, true);
            
            return ['success' => true, 'message' => 'Email đã được gửi thành công'];
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
            error_log("PHPMailer Error: " . $errorMsg);
            $this->logEmail($to, $subject, false, $errorMsg);
            return ['success' => false, 'message' => 'Lỗi gửi email: ' . $errorMsg];
        }
    }
    
    /**
     * Gửi email với SMTP (stream context)
     */
    private function sendWithSMTP($to, $subject, $message, $options) {
        // This is a simplified SMTP implementation
        // For production, use PHPMailer
        return $this->sendSimpleEmail($to, $subject, $message);
    }
    
    /**
     * Gửi email nhắc nhở đọc sách
     * @param int $userId User ID
     * @return array ['success' => bool, 'message' => string]
     */
    public function sendReadingReminder($userId) {
        $user = $this->db->fetchOne(
            "SELECT id, email, full_name, username FROM users WHERE id = ?",
            [$userId]
        );
        
        if (!$user || empty($user['email'])) {
            return ['success' => false, 'message' => 'Không tìm thấy email của user'];
        }
        
        $userName = $user['full_name'] ?: $user['username'] ?: 'Bạn';
        
        // Get reading stats
        $stats = $this->db->fetchOne(
            "SELECT 
                COUNT(*) as reading_count,
                SUM(progress_percent) as total_progress
             FROM reading_progress rp
             JOIN user_books ub ON rp.user_id = ub.user_id AND rp.book_id = ub.book_id
             WHERE rp.user_id = ? AND ub.status = 'reading'",
            [$userId]
        );
        
        $readingCount = $stats['reading_count'] ?? 0;
        $totalProgress = $stats['total_progress'] ?? 0;
        $avgProgress = $readingCount > 0 ? round($totalProgress / $readingCount, 1) : 0;
        
        $subject = "📚 Nhắc nhở đọc sách - BookOnline";
        
        $message = $this->getReadingReminderTemplate($userName, $readingCount, $avgProgress);
        
        return $this->sendEmail($user['email'], $subject, $message);
    }
    
    /**
     * Gửi email cho nhiều users
     * @param array $userIds Array of user IDs
     * @param string $subject Tiêu đề
     * @param string $message Nội dung
     * @return array ['success' => int, 'failed' => int, 'total' => int]
     */
    public function sendBulkEmail($userIds, $subject, $message) {
        $success = 0;
        $failed = 0;
        
        foreach ($userIds as $userId) {
            $user = $this->db->fetchOne(
                "SELECT email FROM users WHERE id = ?",
                [$userId]
            );
            
            if ($user && !empty($user['email'])) {
                $result = $this->sendEmail($user['email'], $subject, $message);
                if ($result['success']) {
                    $success++;
                } else {
                    $failed++;
                }
            } else {
                $failed++;
            }
        }
        
        return [
            'success' => $success,
            'failed' => $failed,
            'total' => count($userIds)
        ];
    }
    
    /**
     * Gửi email nhắc nhở cho tất cả users đang đọc sách
     * @return array ['success' => int, 'failed' => int, 'total' => int]
     */
    public function sendReadingRemindersToAll() {
        // Lấy tất cả users có sách đang đọc và chưa đọc trong 7 ngày
        $users = $this->db->fetchAll(
            "SELECT DISTINCT u.id, u.email, u.full_name, u.username
             FROM users u
             JOIN user_books ub ON u.id = ub.user_id
             JOIN reading_progress rp ON u.id = rp.user_id AND ub.book_id = rp.book_id
             WHERE ub.status = 'reading'
             AND (rp.last_read_at IS NULL OR rp.last_read_at < DATE_SUB(NOW(), INTERVAL 7 DAY))
             AND u.email IS NOT NULL AND u.email != ''",
            []
        );
        
        $success = 0;
        $failed = 0;
        
        foreach ($users as $user) {
            $result = $this->sendReadingReminder($user['id']);
            if ($result['success']) {
                $success++;
            } else {
                $failed++;
            }
        }
        
        return [
            'success' => $success,
            'failed' => $failed,
            'total' => count($users)
        ];
    }
    
    /**
     * Template email nhắc nhở đọc sách
     */
    private function getReadingReminderTemplate($userName, $readingCount, $avgProgress) {
        $siteUrl = SITE_URL;
        
        return "
        <!DOCTYPE html>
        <html lang='vi'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Nhắc nhở đọc sách</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #FFB347 0%, #FF9500 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='color: white; margin: 0; font-size: 28px;'>📚 BookOnline</h1>
            </div>
            
            <div style='background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; border: 1px solid #e0e0e0;'>
                <h2 style='color: #FFB347; margin-top: 0;'>Xin chào {$userName}!</h2>
                
                <p>Chúng tôi nhận thấy bạn đã một thời gian chưa đọc sách. Hãy quay lại và tiếp tục hành trình đọc sách của bạn nhé! 📖</p>
                
                " . ($readingCount > 0 ? "
                <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #FFB347;'>
                    <h3 style='margin-top: 0; color: #333;'>Thống kê đọc sách của bạn:</h3>
                    <ul style='list-style: none; padding: 0;'>
                        <li style='padding: 8px 0;'>📚 <strong>Sách đang đọc:</strong> {$readingCount} cuốn</li>
                        <li style='padding: 8px 0;'>📊 <strong>Tiến độ trung bình:</strong> {$avgProgress}%</li>
                    </ul>
                </div>
                " : "
                <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #4A7856;'>
                    <p style='margin: 0;'>Bạn chưa có sách nào đang đọc. Hãy khám phá thư viện sách của chúng tôi!</p>
                </div>
                ") . "
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$siteUrl}/history.php' style='display: inline-block; background: linear-gradient(135deg, #FFB347 0%, #FF9500 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px;'>
                        Tiếp tục đọc sách →
                    </a>
                </div>
                
                <div style='text-align: center; margin-top: 30px;'>
                    <a href='{$siteUrl}/new-books.php' style='color: #FFB347; text-decoration: none; font-weight: bold;'>
                        Khám phá sách mới
                    </a>
                </div>
                
                <hr style='border: none; border-top: 1px solid #e0e0e0; margin: 30px 0;'>
                
                <p style='color: #666; font-size: 12px; text-align: center; margin: 0;'>
                    Email này được gửi tự động từ BookOnline.<br>
                    Nếu bạn không muốn nhận email này, vui lòng cập nhật cài đặt trong tài khoản của bạn.
                </p>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Log email vào database (optional)
     */
    private function logEmail($to, $subject, $success, $error = null) {
        try {
            // Tạo bảng email_logs nếu chưa có
            $this->db->execute("
                CREATE TABLE IF NOT EXISTS email_logs (
                    id BIGINT PRIMARY KEY AUTO_INCREMENT,
                    to_email VARCHAR(255) NOT NULL,
                    subject VARCHAR(255) NOT NULL,
                    success BOOLEAN DEFAULT 0,
                    error_message TEXT,
                    sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_to_email (to_email),
                    INDEX idx_sent_at (sent_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            // Log email
            $this->db->execute(
                "INSERT INTO email_logs (to_email, subject, success, error_message) VALUES (?, ?, ?, ?)",
                [$to, $subject, $success ? 1 : 0, $error]
            );
        } catch (Exception $e) {
            // Ignore logging errors
            error_log("Email logging error: " . $e->getMessage());
        }
    }
    
    /**
     * Lấy danh sách email logs
     * @param int $limit Số lượng logs cần lấy
     * @return array
     */
    public function getEmailLogs($limit = 50) {
        try {
            // Tạo bảng email_logs nếu chưa có
            $this->db->execute("
                CREATE TABLE IF NOT EXISTS email_logs (
                    id BIGINT PRIMARY KEY AUTO_INCREMENT,
                    to_email VARCHAR(255) NOT NULL,
                    subject VARCHAR(255) NOT NULL,
                    success BOOLEAN DEFAULT 0,
                    error_message TEXT,
                    sent_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_to_email (to_email),
                    INDEX idx_sent_at (sent_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            
            // MySQL/MariaDB does not support placeholders for LIMIT directly
            $limit = (int)$limit;
            $logs = $this->db->fetchAll(
                "SELECT * FROM email_logs ORDER BY sent_at DESC LIMIT {$limit}",
                []
            );
            
            return $logs ?: [];
        } catch (Exception $e) {
            error_log("Get email logs error: " . $e->getMessage());
            return [];
        }
    }
}
?>

