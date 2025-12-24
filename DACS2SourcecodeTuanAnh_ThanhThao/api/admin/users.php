<?php
/**
 * Admin Users API - BookOnline
 * Quản lý users (CRUD)
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/admin.php';

$auth = new Auth();
$admin = new Admin();
$method = $_SERVER['REQUEST_METHOD'];

try {
    // Check login first
    if (!$auth->isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
        exit;
    }
    
    // Check admin - check từ currentUser trực tiếp
    $currentUser = $auth->getCurrentUser();
    
    if (!$currentUser) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy thông tin user']);
        exit;
    }
    
    // Check admin từ currentUser (đã được load từ database với is_admin)
    $isAdminValue = $currentUser['is_admin'] ?? null;
    $isAdmin = (
        $isAdminValue == 1 || 
        $isAdminValue === '1' || 
        $isAdminValue === true ||
        (int)$isAdminValue === 1
    );
    
    if (!$isAdmin) {
        // Log error để debug
        error_log("Admin check failed - User ID: " . $currentUser['id'] . ", Email: " . $currentUser['email'] . ", is_admin: " . var_export($isAdminValue, true) . ", type: " . gettype($isAdminValue));
        
        // Check directly in database
        $db = new Database();
        $dbUser = $db->fetchOne(
            "SELECT id, email, is_admin FROM users WHERE id = ?",
            [$currentUser['id']]
        );
        
        http_response_code(403);
        echo json_encode([
            'success' => false,
            'message' => 'Chỉ quản trị viên mới có quyền truy cập',
            'debug' => [
                'user_id' => $currentUser['id'],
                'is_admin_from_current_user' => $isAdminValue,
                'is_admin_type' => gettype($isAdminValue),
                'is_admin_from_db' => $dbUser['is_admin'] ?? null,
                'is_admin_db_type' => isset($dbUser['is_admin']) ? gettype($dbUser['is_admin']) : null,
                'isAdmin_check_result' => $isAdmin,
            ]
        ]);
        exit;
    }
    
    switch ($method) {
        case 'GET':
            // Get users list
            $page = intval($_GET['page'] ?? 1);
            $limit = intval($_GET['limit'] ?? 20);
            $search = $_GET['search'] ?? '';
            $filters = [];
            
            if (isset($_GET['email_verified'])) {
                $filters['email_verified'] = $_GET['email_verified'] === '1';
            }
            if (isset($_GET['is_admin'])) {
                $filters['is_admin'] = $_GET['is_admin'] === '1';
            }
            if (isset($_GET['is_active'])) {
                $filters['is_active'] = $_GET['is_active'] === '1';
            }
            
            $result = $admin->getUsers([
                'page' => $page,
                'limit' => $limit,
                'search' => $search,
                'filter' => $filters
            ]);
            
            echo json_encode([
                'success' => true,
                'data' => $result
            ]);
            break;
            
        case 'POST':
            // Get single user or create user
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input)) {
                $input = $_POST;
            }
            
            $action = $input['action'] ?? $_GET['action'] ?? '';
            
            if ($action === 'get') {
                $userId = intval($input['user_id'] ?? 0);
                $user = $admin->getUser($userId);
                
                if ($user) {
                    echo json_encode([
                        'success' => true,
                        'user' => $user
                    ]);
                } else {
                    http_response_code(404);
                    echo json_encode([
                        'success' => false,
                        'message' => 'User không tồn tại'
                    ]);
                }
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
            }
            break;
            
        case 'PUT':
            // Update user
            $input = json_decode(file_get_contents('php://input'), true);
            $userId = intval($input['user_id'] ?? 0);
            
            if (!$userId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'User ID is required']);
                exit;
            }
            
            unset($input['user_id'], $input['action']);
            $result = $admin->updateUser($userId, $input);
            
            http_response_code($result['success'] ? 200 : 400);
            echo json_encode($result);
            break;
            
        case 'DELETE':
            // Delete user
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input)) {
                parse_str(file_get_contents('php://input'), $input);
            }
            if (empty($input)) {
                $input = $_GET;
            }
            
            $userId = intval($input['user_id'] ?? 0);
            
            if (!$userId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'User ID is required']);
                exit;
            }
            
            $result = $admin->deleteUser($userId);
            
            http_response_code($result['success'] ? 200 : 400);
            echo json_encode($result);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Method not allowed']);
            break;
    }
} catch (Exception $e) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    error_log("Admin Users API Error: " . $e->getMessage());
}
?>

