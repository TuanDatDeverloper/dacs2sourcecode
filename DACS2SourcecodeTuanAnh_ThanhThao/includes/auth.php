<?php
/**
 * Authentication Class - BookOnline
 * Xử lý đăng ký, đăng nhập, session management
 */

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/verification.php';

class Auth {
    private $db;
    private $verification;
    
    public function __construct() {
        $this->db = new Database();
        $this->verification = new Verification();
    }
    
    /**
     * Đăng ký user mới
     * @param string $email Email
     * @param string $password Password
     * @param string $fullName Tên đầy đủ (optional)
     * @param string $username Username (optional)
     * @return array ['success' => bool, 'message' => string, 'user' => array|null]
     */
    public function register($email, $password, $fullName = '', $username = '') {
        // Validation
        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email và mật khẩu không được để trống'];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Email không hợp lệ'];
        }
        
        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Mật khẩu phải có ít nhất 6 ký tự'];
        }
        
        // Check if email already exists
        $existing = $this->db->fetchOne(
            "SELECT id FROM users WHERE email = ?",
            [$email]
        );
        
        if ($existing) {
            return ['success' => false, 'message' => 'Email đã được sử dụng'];
        }
        
        // Check username if provided
        if (!empty($username)) {
            $existingUsername = $this->db->fetchOne(
                "SELECT id FROM users WHERE username = ?",
                [$username]
            );
            
            if ($existingUsername) {
                return ['success' => false, 'message' => 'Username đã được sử dụng'];
            }
        }
        
        // Hash password
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user
        try {
            $this->db->execute(
                "INSERT INTO users (email, password_hash, full_name, username, coins, email_verified) VALUES (?, ?, ?, ?, 100, 0)",
                [$email, $passwordHash, $fullName, $username ?: null] // email_verified = 0 by default
            );
            
            $userId = $this->db->lastInsertId();
            
            // Get created user
            $user = $this->db->fetchOne(
                "SELECT id, email, full_name, username, coins, email_verified FROM users WHERE id = ?",
                [$userId]
            );
            
            // Auto login
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['full_name'] ?: $user['username'] ?: $user['email'];
            session_regenerate_id(true);
            
            // Tự động gửi email verification
            try {
                $emailResult = $this->verification->sendEmailVerificationCode($userId, $email);
                if (!$emailResult['success']) {
                    error_log("Failed to send verification email: " . $emailResult['message']);
                }
            } catch (Exception $e) {
                error_log("Error sending verification email: " . $e->getMessage());
                // Không fail registration nếu email không gửi được
            }
            
            return [
                'success' => true,
                'message' => 'Đăng ký thành công. Vui lòng xác nhận email.',
                'user' => $user
            ];
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            return ['success' => false, 'message' => 'Lỗi hệ thống. Vui lòng thử lại sau.'];
        }
    }
    
    /**
     * Đăng nhập
     * @param string $email Email hoặc username
     * @param string $password Password
     * @return array ['success' => bool, 'message' => string, 'user' => array|null]
     */
    public function login($email, $password) {
        if (empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'Email và mật khẩu không được để trống'];
        }
        
        // Try to find user by email or username (include is_admin field)
        $user = $this->db->fetchOne(
            "SELECT * FROM users WHERE email = ? OR username = ?",
            [$email, $email]
        );
        
        if (!$user) {
            return ['success' => false, 'message' => 'Email/Username hoặc mật khẩu không đúng'];
        }
        
        // Verify password
        if (!password_verify($password, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Email/Username hoặc mật khẩu không đúng'];
        }
        
        // Check if user is active
        if (isset($user['is_active']) && $user['is_active'] == 0) {
            return ['success' => false, 'message' => 'Tài khoản của bạn đã bị khóa'];
        }
        
        // Update last login
        $this->db->execute(
            "UPDATE users SET last_login = NOW() WHERE id = ?",
            [$user['id']]
        );
        
        // Set session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_name'] = $user['full_name'] ?: $user['username'] ?: $user['email'];
        $_SESSION['is_admin'] = isset($user['is_admin']) && $user['is_admin'] == 1;
        
        // Regenerate session ID for security
        session_regenerate_id(true);
        
        // Return user data (without password)
        unset($user['password_hash']);
        
        return [
            'success' => true,
            'message' => 'Đăng nhập thành công',
            'user' => $user
        ];
    }
    
    /**
     * Đăng xuất
     * @return array ['success' => bool]
     */
    public function logout() {
        $_SESSION = [];
        
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        session_destroy();
        
        return ['success' => true];
    }
    
    /**
     * Kiểm tra user đã đăng nhập chưa
     * @return bool
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Lấy thông tin user hiện tại
     * @return array|null
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        $user = $this->db->fetchOne(
            "SELECT id, email, username, full_name, coins, email_verified, is_admin, is_active, created_at FROM users WHERE id = ?",
            [$_SESSION['user_id']]
        );
        
        return $user ?: null;
    }
    
    /**
     * Yêu cầu đăng nhập (redirect nếu chưa login)
     * @param string $redirectUrl URL để redirect sau khi login
     */
    public function requireLogin($redirectUrl = 'login.php') {
        if (!$this->isLoggedIn()) {
            $currentUrl = $_SERVER['REQUEST_URI'];
            header('Location: ' . $redirectUrl . '?redirect=' . urlencode($currentUrl));
            exit;
        }
    }
    
    /**
     * Yêu cầu email đã được xác nhận (redirect nếu chưa verify)
     * @param string $redirectUrl URL để redirect (mặc định là verify-email.php)
     */
    public function requireVerifiedEmail($redirectUrl = 'verify-email.php') {
        $this->requireLogin(); // Phải login trước
        
        $user = $this->getCurrentUser();
        if (!$user || !isset($user['email_verified']) || $user['email_verified'] != 1) {
            // Nếu là admin thì không cần verify email
            if (isset($user['is_admin']) && $user['is_admin'] == 1) {
                return; // Admin không cần verify email
            }
            
            header('Location: ' . $redirectUrl);
            exit;
        }
    }
    
    /**
     * Kiểm tra email đã được xác nhận chưa
     * @return bool
     */
    public function isEmailVerified() {
        if (!$this->isLoggedIn()) {
            return false;
        }
        
        $user = $this->getCurrentUser();
        if (!$user) {
            return false;
        }
        
        // Admin không cần verify email
        if (isset($user['is_admin']) && $user['is_admin'] == 1) {
            return true;
        }
        
        return isset($user['email_verified']) && $user['email_verified'] == 1;
    }
    
    /**
     * Lấy user ID hiện tại
     * @return int|null
     */
    public function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }
}

