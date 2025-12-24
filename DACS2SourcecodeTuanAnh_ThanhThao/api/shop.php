<?php
/**
 * Shop API - BookOnline
 * Get shop items and handle purchases
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
            $category = $_GET['category'] ?? 'all';
            
            $sql = "SELECT * FROM shop_items WHERE is_active = 1";
            $params = [];
            
            if ($category !== 'all') {
                $sql .= " AND category = ?";
                $params[] = $category;
            }
            
            $sql .= " ORDER BY category, price ASC";
            
            $items = $db->fetchAll($sql, $params);
            echo json_encode($items);
            break;
            
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data)) {
                $data = $_POST;
            }
            
            $action = $data['action'] ?? '';
            
            if ($action === 'purchase') {
                $itemId = $data['item_id'] ?? null;
                
                if (!$itemId) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Item ID is required']);
                    exit;
                }
                
                // Get item info
                $item = $db->fetchOne("SELECT * FROM shop_items WHERE id = ? AND is_active = 1", [$itemId]);
                if (!$item) {
                    http_response_code(404);
                    echo json_encode(['success' => false, 'message' => 'Item not found']);
                    exit;
                }
                
                // Get user coins
                $user = $db->fetchOne("SELECT coins FROM users WHERE id = ?", [$userId]);
                $userCoins = $user['coins'] ?? 0;
                
                // Check if user has enough coins
                if ($userCoins < $item['price']) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Không đủ Book Coins. Bạn cần ' . $item['price'] . ' coins nhưng chỉ có ' . $userCoins . ' coins.'
                    ]);
                    exit;
                }
                
                // Check if user already owns this item
                $existing = $db->fetchOne(
                    "SELECT id FROM user_inventory WHERE user_id = ? AND item_id = ?",
                    [$userId, $itemId]
                );
                
                if ($existing) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Bạn đã sở hữu vật phẩm này rồi']);
                    exit;
                }
                
                // Start transaction
                $db->beginTransaction();
                
                try {
                    // Deduct coins
                    $db->execute(
                        "UPDATE users SET coins = coins - ? WHERE id = ?",
                        [$item['price'], $userId]
                    );
                    
                    // Add to inventory
                    $db->execute(
                        "INSERT INTO user_inventory (user_id, item_id) VALUES (?, ?)",
                        [$userId, $itemId]
                    );
                    
                    // Record purchase
                    $db->execute(
                        "INSERT INTO purchases (user_id, item_id, price_at_purchase) VALUES (?, ?, ?)",
                        [$userId, $itemId, $item['price']]
                    );
                    
                    // Log transaction
                    $db->execute(
                        "INSERT INTO coins_transactions (user_id, amount, reason, reference_id) 
                         VALUES (?, ?, 'item_purchase', ?)",
                        [$userId, -$item['price'], $itemId]
                    );
                    
                    $db->commit();
                    
                    // Get updated user coins
                    $updatedUser = $db->fetchOne("SELECT coins FROM users WHERE id = ?", [$userId]);
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Mua thành công!',
                        'item' => $item,
                        'remaining_coins' => $updatedUser['coins']
                    ]);
                    
                } catch (Exception $e) {
                    $db->rollback();
                    throw $e;
                }
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
    error_log("Shop API Error: " . $e->getMessage());
}
?>

