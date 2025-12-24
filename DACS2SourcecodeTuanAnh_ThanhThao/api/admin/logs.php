<?php
/**
 * Admin Logs API - BookOnline
 * Lấy admin activity logs
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/database.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/admin.php';

$auth = new Auth();
$admin = new Admin();

try {
    // Check login first
    if (!$auth->isLoggedIn()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
        exit;
    }
    
    // Check admin
    $admin->requireAdmin();
    
    $limit = intval($_GET['limit'] ?? 50);
    $logs = $admin->getLogs($limit);
    
    echo json_encode([
        'success' => true,
        'logs' => $logs
    ]);
} catch (Exception $e) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

