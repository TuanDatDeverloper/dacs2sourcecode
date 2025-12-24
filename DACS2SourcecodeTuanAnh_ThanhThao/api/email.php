<?php
/**
 * Email API - BookOnline
 * Gửi email thông báo cho người dùng
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/email.php';
require_once __DIR__ . '/../includes/admin.php';

$auth = new Auth();
$emailService = new EmailService();
$admin = new Admin();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'POST':
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input)) {
                $input = $_POST;
            }
            
            $action = $input['action'] ?? $_GET['action'] ?? '';
            
            if ($action === 'send_reminder') {
                // Gửi email nhắc nhở cho user hiện tại
                if (!$auth->isLoggedIn()) {
                    http_response_code(401);
                    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
                    exit;
                }
                
                $userId = $auth->getUserId();
                $result = $emailService->sendReadingReminder($userId);
                
                http_response_code($result['success'] ? 200 : 500);
                echo json_encode($result);
                
            } elseif ($action === 'send_custom') {
                // Gửi email tùy chỉnh (admin only hoặc user gửi cho chính mình)
                if (!$auth->isLoggedIn()) {
                    http_response_code(401);
                    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
                    exit;
                }
                
                $to = $input['to'] ?? '';
                $subject = $input['subject'] ?? '';
                $message = $input['message'] ?? '';
                
                if (empty($to) || empty($subject) || empty($message)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Thiếu thông tin: to, subject, message']);
                    exit;
                }
                
                // Cho phép gửi cho chính mình hoặc admin có thể gửi cho bất kỳ ai
                $currentUser = $auth->getCurrentUser();
                $isAdmin = $admin->isAdmin();
                
                if ($to !== $currentUser['email'] && !$isAdmin) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Chỉ có thể gửi email cho chính mình']);
                    exit;
                }
                
                $result = $emailService->sendEmail($to, $subject, $message);
                
                http_response_code($result['success'] ? 200 : 500);
                echo json_encode($result);
                
            } elseif ($action === 'send_bulk_reminders') {
                // Gửi email nhắc nhở cho tất cả users (admin only)
                if (!$auth->isLoggedIn()) {
                    http_response_code(401);
                    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
                    exit;
                }
                
                if (!$admin->isAdmin()) {
                    http_response_code(403);
                    echo json_encode(['success' => false, 'message' => 'Chỉ quản trị viên mới có quyền thực hiện']);
                    exit;
                }
                
                $result = $emailService->sendReadingRemindersToAll();
                
                http_response_code(200);
                echo json_encode([
                    'success' => true,
                    'message' => "Đã gửi {$result['success']} email thành công, {$result['failed']} email thất bại",
                    'stats' => $result
                ]);
                
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
            }
            break;
            
        case 'GET':
            // Get email logs (admin only)
            if (!$auth->isLoggedIn()) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
                exit;
            }
            
            if (!$admin->isAdmin()) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'Chỉ quản trị viên mới có quyền truy cập']);
                exit;
            }
            
            $limit = intval($_GET['limit'] ?? 50);
            $logs = $emailService->getEmailLogs($limit);
            
            echo json_encode([
                'success' => true,
                'logs' => $logs
            ]);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
    error_log("Email API Error: " . $e->getMessage());
}
?>

