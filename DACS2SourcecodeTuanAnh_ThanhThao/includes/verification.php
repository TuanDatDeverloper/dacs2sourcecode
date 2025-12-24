<?php
/**
 * Verification Class - BookOnline
 * Xử lý email verification và password reset
 */

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/email.php';

class Verification {
    private $db;
    private $emailService;
    private $codeLength = 6;
    private $codeExpiry = 15; // minutes
    
    public function __construct() {
        $this->db = new Database();
        $this->emailService = new EmailService();
    }
    
    /**
     * Tạo mã verification ngẫu nhiên
     * @return string
     */
    private function generateCode() {
        return str_pad(rand(0, 999999), $this->codeLength, '0', STR_PAD_LEFT);
    }
    
    /**
     * Gửi mã xác nhận email khi đăng ký
     * @param int $userId User ID
     * @param string $email Email address
     * @return array ['success' => bool, 'message' => string, 'code' => string|null]
     */
    public function sendEmailVerificationCode($userId, $email) {
        // Kiểm tra rate limiting (tối đa 3 lần trong 15 phút)
        $recentCodes = $this->db->fetchAll(
            "SELECT COUNT(*) as count FROM verification_codes 
             WHERE user_id = ? AND type = 'email_verification' 
             AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE) AND used = 0",
            [$userId]
        );
        
        if ($recentCodes && $recentCodes[0]['count'] >= 3) {
            return [
                'success' => false,
                'message' => 'Bạn đã gửi quá nhiều mã. Vui lòng đợi 15 phút.'
            ];
        }
        
        // Xóa các mã cũ chưa dùng
        $this->db->execute(
            "DELETE FROM verification_codes 
             WHERE user_id = ? AND type = 'email_verification' AND used = 0",
            [$userId]
        );
        
        // Tạo mã mới
        $code = $this->generateCode();
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$this->codeExpiry} minutes"));
        
        // Lưu vào database
        $this->db->execute(
            "INSERT INTO verification_codes (user_id, code, type, email, expires_at) 
             VALUES (?, ?, 'email_verification', ?, ?)",
            [$userId, $code, $email, $expiresAt]
        );
        
        // Gửi email
        $subject = "📧 Xác nhận email - BookOnline";
        $message = $this->getEmailVerificationTemplate($code);
        
        $emailResult = $this->emailService->sendEmail($email, $subject, $message);
        
        if ($emailResult['success']) {
            return [
                'success' => true,
                'message' => 'Mã xác nhận đã được gửi đến email của bạn',
                'code' => $code // Chỉ trả về trong development
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Không thể gửi email: ' . $emailResult['message']
            ];
        }
    }
    
    /**
     * Xác minh mã email verification
     * @param int $userId User ID
     * @param string $code Verification code
     * @return array ['success' => bool, 'message' => string]
     */
    public function verifyEmailCode($userId, $code) {
        $verification = $this->db->fetchOne(
            "SELECT * FROM verification_codes 
             WHERE user_id = ? AND code = ? AND type = 'email_verification' AND used = 0
             ORDER BY created_at DESC LIMIT 1",
            [$userId, $code]
        );
        
        if (!$verification) {
            return [
                'success' => false,
                'message' => 'Mã xác nhận không hợp lệ'
            ];
        }
        
        // Kiểm tra hết hạn
        if (strtotime($verification['expires_at']) < time()) {
            return [
                'success' => false,
                'message' => 'Mã xác nhận đã hết hạn. Vui lòng yêu cầu mã mới.'
            ];
        }
        
        // Đánh dấu mã đã dùng
        $this->db->execute(
            "UPDATE verification_codes SET used = 1 WHERE id = ?",
            [$verification['id']]
        );
        
        // Cập nhật email_verified cho user
        $this->db->execute(
            "UPDATE users SET email_verified = 1 WHERE id = ?",
            [$userId]
        );
        
        return [
            'success' => true,
            'message' => 'Email đã được xác nhận thành công!'
        ];
    }
    
    /**
     * Gửi mã reset password
     * @param string $email Email address
     * @return array ['success' => bool, 'message' => string, 'code' => string|null]
     */
    public function sendPasswordResetCode($email) {
        // Tìm user theo email
        $user = $this->db->fetchOne(
            "SELECT id FROM users WHERE email = ?",
            [$email]
        );
        
        if (!$user) {
            // Không tiết lộ email có tồn tại hay không (security)
            return [
                'success' => true,
                'message' => 'Nếu email tồn tại, mã reset password đã được gửi.'
            ];
        }
        
        $userId = $user['id'];
        
        // Kiểm tra rate limiting
        $recentCodes = $this->db->fetchAll(
            "SELECT COUNT(*) as count FROM verification_codes 
             WHERE user_id = ? AND type = 'password_reset' 
             AND created_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE) AND used = 0",
            [$userId]
        );
        
        if ($recentCodes && $recentCodes[0]['count'] >= 3) {
            return [
                'success' => false,
                'message' => 'Bạn đã yêu cầu quá nhiều lần. Vui lòng đợi 15 phút.'
            ];
        }
        
        // Xóa các mã cũ
        $this->db->execute(
            "DELETE FROM verification_codes 
             WHERE user_id = ? AND type = 'password_reset' AND used = 0",
            [$userId]
        );
        
        // Tạo mã mới
        $code = $this->generateCode();
        $expiresAt = date('Y-m-d H:i:s', strtotime("+{$this->codeExpiry} minutes"));
        
        // Lưu vào database
        $this->db->execute(
            "INSERT INTO verification_codes (user_id, code, type, email, expires_at) 
             VALUES (?, ?, 'password_reset', ?, ?)",
            [$userId, $code, $email, $expiresAt]
        );
        
        // Gửi email
        $subject = "🔐 Đặt lại mật khẩu - BookOnline";
        $message = $this->getPasswordResetTemplate($code);
        
        $emailResult = $this->emailService->sendEmail($email, $subject, $message);
        
        if ($emailResult['success']) {
            return [
                'success' => true,
                'message' => 'Mã reset password đã được gửi đến email của bạn',
                'code' => $code // Chỉ trả về trong development
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Không thể gửi email: ' . $emailResult['message']
            ];
        }
    }
    
    /**
     * Xác minh mã reset password và đổi mật khẩu
     * @param string $email Email address
     * @param string $code Verification code
     * @param string $newPassword New password
     * @return array ['success' => bool, 'message' => string]
     */
    public function resetPasswordWithCode($email, $code, $newPassword) {
        // Tìm user
        $user = $this->db->fetchOne(
            "SELECT id FROM users WHERE email = ?",
            [$email]
        );
        
        if (!$user) {
            return [
                'success' => false,
                'message' => 'Email không tồn tại'
            ];
        }
        
        $userId = $user['id'];
        
        // Tìm mã verification
        $verification = $this->db->fetchOne(
            "SELECT * FROM verification_codes 
             WHERE user_id = ? AND code = ? AND type = 'password_reset' AND used = 0
             ORDER BY created_at DESC LIMIT 1",
            [$userId, $code]
        );
        
        if (!$verification) {
            return [
                'success' => false,
                'message' => 'Mã xác nhận không hợp lệ'
            ];
        }
        
        // Kiểm tra hết hạn
        if (strtotime($verification['expires_at']) < time()) {
            return [
                'success' => false,
                'message' => 'Mã xác nhận đã hết hạn. Vui lòng yêu cầu mã mới.'
            ];
        }
        
        // Validate password
        if (strlen($newPassword) < 6) {
            return [
                'success' => false,
                'message' => 'Mật khẩu phải có ít nhất 6 ký tự'
            ];
        }
        
        // Hash password
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        
        // Đánh dấu mã đã dùng
        $this->db->execute(
            "UPDATE verification_codes SET used = 1 WHERE id = ?",
            [$verification['id']]
        );
        
        // Cập nhật password
        $this->db->execute(
            "UPDATE users SET password_hash = ? WHERE id = ?",
            [$passwordHash, $userId]
        );
        
        return [
            'success' => true,
            'message' => 'Mật khẩu đã được đặt lại thành công!'
        ];
    }
    
    /**
     * Template email verification
     */
    private function getEmailVerificationTemplate($code) {
        $siteUrl = SITE_URL;
        
        return "
        <!DOCTYPE html>
        <html lang='vi'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Xác nhận email</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #FFB347 0%, #FF9500 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='color: white; margin: 0; font-size: 28px;'>📚 BookOnline</h1>
            </div>
            
            <div style='background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; border: 1px solid #e0e0e0;'>
                <h2 style='color: #FFB347; margin-top: 0;'>Xác nhận email của bạn</h2>
                
                <p>Xin chào!</p>
                <p>Cảm ơn bạn đã đăng ký tài khoản BookOnline. Vui lòng sử dụng mã sau để xác nhận email của bạn:</p>
                
                <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center; border: 2px solid #FFB347;'>
                    <div style='font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #FFB347;'>
                        {$code}
                    </div>
                </div>
                
                <p style='color: #666; font-size: 14px;'>Mã này sẽ hết hạn sau 15 phút.</p>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$siteUrl}/verify-email.php' style='display: inline-block; background: linear-gradient(135deg, #FFB347 0%, #FF9500 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px;'>
                        Xác nhận email →
                    </a>
                </div>
                
                <hr style='border: none; border-top: 1px solid #e0e0e0; margin: 30px 0;'>
                
                <p style='color: #666; font-size: 12px; text-align: center; margin: 0;'>
                    Nếu bạn không yêu cầu mã này, vui lòng bỏ qua email này.
                </p>
            </div>
        </body>
        </html>
        ";
    }
    
    /**
     * Template password reset
     */
    private function getPasswordResetTemplate($code) {
        $siteUrl = SITE_URL;
        
        return "
        <!DOCTYPE html>
        <html lang='vi'>
        <head>
            <meta charset='UTF-8'>
            <meta name='viewport' content='width=device-width, initial-scale=1.0'>
            <title>Đặt lại mật khẩu</title>
        </head>
        <body style='font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;'>
            <div style='background: linear-gradient(135deg, #FFB347 0%, #FF9500 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;'>
                <h1 style='color: white; margin: 0; font-size: 28px;'>📚 BookOnline</h1>
            </div>
            
            <div style='background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; border: 1px solid #e0e0e0;'>
                <h2 style='color: #FFB347; margin-top: 0;'>Đặt lại mật khẩu</h2>
                
                <p>Xin chào!</p>
                <p>Bạn đã yêu cầu đặt lại mật khẩu cho tài khoản BookOnline. Vui lòng sử dụng mã sau:</p>
                
                <div style='background: white; padding: 20px; border-radius: 8px; margin: 20px 0; text-align: center; border: 2px solid #FFB347;'>
                    <div style='font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #FFB347;'>
                        {$code}
                    </div>
                </div>
                
                <p style='color: #666; font-size: 14px;'>Mã này sẽ hết hạn sau 15 phút.</p>
                
                <div style='text-align: center; margin: 30px 0;'>
                    <a href='{$siteUrl}/reset-password.php' style='display: inline-block; background: linear-gradient(135deg, #FFB347 0%, #FF9500 100%); color: white; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px;'>
                        Đặt lại mật khẩu →
                    </a>
                </div>
                
                <hr style='border: none; border-top: 1px solid #e0e0e0; margin: 30px 0;'>
                
                <p style='color: #666; font-size: 12px; text-align: center; margin: 0;'>
                    Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này. Mật khẩu của bạn sẽ không thay đổi.
                </p>
            </div>
        </body>
        </html>
        ";
    }
}
?>

