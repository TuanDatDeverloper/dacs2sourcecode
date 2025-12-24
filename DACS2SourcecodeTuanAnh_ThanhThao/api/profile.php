<?php
/**
 * Profile API
 * Xử lý cập nhật thông tin cá nhân và xóa tài khoản
 */

ob_start();
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
error_reporting(E_ALL);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/database.php';

ob_clean();

try {
    $auth = new Auth();
    
    // Kiểm tra đăng nhập
    if (!$auth->isLoggedIn()) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Chưa đăng nhập'
        ]);
        exit;
    }

    $currentUser = $auth->getCurrentUser();
    if (!$currentUser) {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Không tìm thấy thông tin người dùng'
        ]);
        exit;
    }

    $userId = $currentUser['id'];
    $db = new Database();

    // Lấy action từ request
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? $_POST['action'] ?? '';

    if ($action === 'update') {
        // Cập nhật thông tin cá nhân
        $fullName = trim($input['full_name'] ?? $_POST['full_name'] ?? '');
        $username = trim($input['username'] ?? $_POST['username'] ?? '');

        // Validate
        if (empty($fullName) && empty($username)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Vui lòng nhập ít nhất một trường thông tin'
            ]);
            exit;
        }

        // Kiểm tra username đã tồn tại chưa (nếu có thay đổi)
        if (!empty($username)) {
            $existingUser = $db->fetchOne(
                "SELECT id FROM users WHERE username = ? AND id != ?",
                [$username, $userId]
            );
            if ($existingUser) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Tên người dùng đã được sử dụng'
                ]);
                exit;
            }
        }

        // Cập nhật database
        $updateFields = [];
        $updateValues = [];

        if (!empty($fullName)) {
            $updateFields[] = 'full_name = ?';
            $updateValues[] = $fullName;
        }

        if (!empty($username)) {
            $updateFields[] = 'username = ?';
            $updateValues[] = $username;
        }

        if (empty($updateFields)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Không có thông tin nào để cập nhật'
            ]);
            exit;
        }

        $updateValues[] = $userId;
        $sql = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE id = ?";
        
        $db->execute($sql, $updateValues);

        // Cập nhật session
        $_SESSION['full_name'] = $fullName ?: ($currentUser['full_name'] ?? '');
        $_SESSION['username'] = $username ?: ($currentUser['username'] ?? '');

        echo json_encode([
            'success' => true,
            'message' => 'Cập nhật thông tin thành công'
        ]);

    } elseif ($action === 'delete') {
        // Xóa tài khoản
        $confirmEmail = trim($input['confirm_email'] ?? $_POST['confirm_email'] ?? '');

        // Validate email
        if (empty($confirmEmail)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Vui lòng nhập email để xác nhận'
            ]);
            exit;
        }

        if ($confirmEmail !== $currentUser['email']) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Email không khớp'
            ]);
            exit;
        }

        // Bắt đầu transaction
        $db->beginTransaction();

        try {
            // Xóa dữ liệu liên quan
            // 1. Xóa reading progress
            $db->execute("DELETE FROM reading_progress WHERE user_id = ?", [$userId]);
            
            // 2. Xóa user_books
            $db->execute("DELETE FROM user_books WHERE user_id = ?", [$userId]);
            
            // 3. Xóa coins transactions
            $db->execute("DELETE FROM coins_transactions WHERE user_id = ?", [$userId]);
            
            // 4. Xóa user inventory
            $db->execute("DELETE FROM user_inventory WHERE user_id = ?", [$userId]);
            
            // 5. Xóa bookshelf layouts
            $db->execute("DELETE FROM bookshelf_layouts WHERE user_id = ?", [$userId]);
            
            // 6. Xóa quiz attempts
            $db->execute("DELETE FROM quiz_attempts WHERE user_id = ?", [$userId]);
            
            // 7. Xóa verification codes
            $db->execute("DELETE FROM verification_codes WHERE user_id = ?", [$userId]);
            
            // 8. Xóa admin logs (nếu có)
            $db->execute("DELETE FROM admin_logs WHERE user_id = ?", [$userId]);
            
            // 9. Cuối cùng xóa user
            $db->execute("DELETE FROM users WHERE id = ?", [$userId]);

            // Commit transaction
            $db->commit();

            // Xóa session
            session_destroy();

            echo json_encode([
                'success' => true,
                'message' => 'Tài khoản đã được xóa thành công'
            ]);

        } catch (Exception $e) {
            // Rollback nếu có lỗi
            $db->rollback();
            throw $e;
        }

    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Action không hợp lệ'
        ]);
    }

} catch (Exception $e) {
    ob_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
    error_log("Profile API Error: " . $e->getMessage());
}

