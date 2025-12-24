<?php
/**
 * Update Book Cover API
 * Cập nhật ảnh bìa sách trong database
 */

header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 0);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';

$auth = new Auth();
$auth->requireLogin();

// Chỉ admin mới có quyền cập nhật
$currentUser = $auth->getCurrentUser();
if (!$currentUser || !($currentUser['is_admin'] == 1 || $currentUser['is_admin'] === '1' || $currentUser['is_admin'] === true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Chỉ quản trị viên mới có quyền cập nhật']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

try {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (empty($data)) {
        $data = $_POST;
    }
    
    $bookId = $data['book_id'] ?? null;
    $coverUrl = $data['cover_url'] ?? null;
    
    if (!$bookId || !$coverUrl) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'book_id và cover_url là bắt buộc']);
        exit;
    }
    
    // Validate URL
    if (!filter_var($coverUrl, FILTER_VALIDATE_URL) && !str_starts_with($coverUrl, 'data:image')) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'cover_url không hợp lệ']);
        exit;
    }
    
    $db = new Database();
    
    // Kiểm tra sách có tồn tại không
    $book = $db->fetchOne("SELECT id FROM books WHERE id = ?", [$bookId]);
    
    if (!$book) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Không tìm thấy sách']);
        exit;
    }
    
    // Cập nhật cover_url
    $db->execute(
        "UPDATE books SET cover_url = ? WHERE id = ?",
        [$coverUrl, $bookId]
    );
    
    echo json_encode([
        'success' => true,
        'message' => 'Đã cập nhật ảnh bìa thành công',
        'book_id' => $bookId,
        'cover_url' => $coverUrl
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
    error_log("Update Book Cover API Error: " . $e->getMessage());
}

