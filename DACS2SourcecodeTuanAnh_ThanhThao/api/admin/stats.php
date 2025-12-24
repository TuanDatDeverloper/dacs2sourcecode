<?php
/**
 * Admin Stats API - BookOnline
 * Thống kê cho admin dashboard
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
    
    $stats = $admin->getStats();
    
    // Get user growth data (last 30 days)
    $db = new Database();
    $growthData = $db->fetchAll(
        "SELECT DATE(created_at) as date, COUNT(*) as count
         FROM users
         WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
         GROUP BY DATE(created_at)
         ORDER BY date ASC"
    );
    
    echo json_encode([
        'success' => true,
        'stats' => $stats,
        'growth' => $growthData
    ]);
} catch (Exception $e) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>

