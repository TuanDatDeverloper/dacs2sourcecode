<?php
/**
 * Inventory API - BookOnline
 * Get user inventory and manage items
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';

$auth = new Auth();
$auth->requireLogin();

$db = new Database();
$method = $_SERVER['REQUEST_METHOD'];
$userId = $_SESSION['user_id'];

try {
    switch ($method) {
        case 'GET':
            $inventory = $db->fetchAll(
                "SELECT ui.*, si.name, si.description, si.price, si.category, si.image, si.type
                 FROM user_inventory ui
                 JOIN shop_items si ON ui.item_id = si.id
                 WHERE ui.user_id = ?
                 ORDER BY ui.acquired_at DESC",
                [$userId]
            );
            echo json_encode($inventory);
            break;
            
        case 'PUT':
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data)) {
                $data = $_POST;
            }
            
            $action = $data['action'] ?? '';
            $itemId = $data['item_id'] ?? null;
            
            if (!$itemId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Item ID is required']);
                exit;
            }
            
            if ($action === 'equip') {
                // Equip item (for bookshelf decorations)
                // This is a simple implementation - can be extended
                echo json_encode(['success' => true, 'message' => 'Item equipped']);
            } elseif ($action === 'unequip') {
                // Unequip item
                echo json_encode(['success' => true, 'message' => 'Item unequipped']);
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
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
    error_log("Inventory API Error: " . $e->getMessage());
}
?>

