<?php
/**
 * Verification API - BookOnline
 * Xử lý email verification và password reset
 */

// Prevent any output before headers
ob_start();

// Set JSON header first
header('Content-Type: application/json; charset=utf-8');

// Disable error display (only log)
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/config.php';

// Re-disable error display after config (config may enable it)
ini_set('display_errors', 0);

require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/verification.php';

// Clear any output buffer
ob_clean();

$auth = new Auth();
$verification = new Verification();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'POST':
            // Get input from JSON body or POST data
            $rawInput = file_get_contents('php://input');
            $input = json_decode($rawInput, true);
            
            // If JSON decode failed or empty, try $_POST
            if (json_last_error() !== JSON_ERROR_NONE || empty($input)) {
                $input = $_POST;
            }
            
            // Log for debugging (remove in production)
            error_log("Verification API - Method: POST, Input: " . print_r($input, true));
            
            $action = $input['action'] ?? '';
            
            if (empty($action)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Action is required',
                    'debug' => ['received_input' => $input]
                ]);
                exit;
            }
            
            if ($action === 'send_email_verification') {
                // Gửi mã xác nhận email khi đăng ký
                if (!$auth->isLoggedIn()) {
                    http_response_code(401);
                    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
                    exit;
                }
                
                $userId = $auth->getUserId();
                $user = $auth->getCurrentUser();
                $email = $user['email'] ?? $input['email'] ?? '';
                
                if (empty($email)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Email is required']);
                    exit;
                }
                
                try {
                    $result = $verification->sendEmailVerificationCode($userId, $email);
                    
                    // Luôn trả về 200, success/failure trong response body
                    http_response_code(200);
                    echo json_encode($result);
                } catch (Exception $e) {
                    error_log("Error sending email verification: " . $e->getMessage());
                    http_response_code(200);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Không thể gửi email: ' . $e->getMessage()
                    ]);
                }
                
            } elseif ($action === 'verify_email') {
                // Xác minh mã email
                if (!$auth->isLoggedIn()) {
                    http_response_code(401);
                    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
                    exit;
                }
                
                $userId = $auth->getUserId();
                $code = $input['code'] ?? '';
                
                if (empty($code)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Mã xác nhận không được để trống']);
                    exit;
                }
                
                try {
                    $result = $verification->verifyEmailCode($userId, $code);
                    
                    // Luôn trả về 200, success/failure trong response body
                    http_response_code(200);
                    echo json_encode($result);
                } catch (Exception $e) {
                    error_log("Error verifying email code: " . $e->getMessage());
                    http_response_code(200);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Lỗi xác nhận: ' . $e->getMessage()
                    ]);
                }
                
            } elseif ($action === 'send_password_reset') {
                // Gửi mã reset password
                $email = $input['email'] ?? '';
                
                if (empty($email)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Email is required']);
                    exit;
                }
                
                $result = $verification->sendPasswordResetCode($email);
                
                http_response_code($result['success'] ? 200 : 400);
                echo json_encode($result);
                
            } elseif ($action === 'reset_password') {
                // Đặt lại mật khẩu với mã
                $email = $input['email'] ?? '';
                $code = $input['code'] ?? '';
                $newPassword = $input['password'] ?? '';
                
                if (empty($email) || empty($code) || empty($newPassword)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin']);
                    exit;
                }
                
                $result = $verification->resetPasswordWithCode($email, $code, $newPassword);
                
                http_response_code($result['success'] ? 200 : 400);
                echo json_encode($result);
                
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
            }
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    // Make sure we output JSON even on error
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
    error_log("Verification API Error: " . $e->getMessage());
    exit;
}

