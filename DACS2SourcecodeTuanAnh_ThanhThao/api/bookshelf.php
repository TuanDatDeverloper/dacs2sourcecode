<?php
/**
 * Bookshelf Layout API - BookOnline
 * Save and load 3D bookshelf layout
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
            $layout = $db->fetchOne(
                "SELECT * FROM bookshelf_layouts WHERE user_id = ?",
                [$userId]
            );
            
            if ($layout) {
                // Parse layout_data JSON
                $layout['layout_data'] = json_decode($layout['layout_data'], true);
                echo json_encode($layout);
            } else {
                // Return default layout
                echo json_encode([
                    'user_id' => $userId,
                    'layout_data' => [],
                    'theme' => 'default'
                ]);
            }
            break;
            
        case 'PUT':
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data)) {
                $data = $_POST;
            }
            
            $layoutData = $data['layout_data'] ?? [];
            $theme = $data['theme'] ?? 'default';
            
            // Convert to JSON string
            $layoutDataJson = json_encode($layoutData);
            
            // Check if layout exists
            $existing = $db->fetchOne(
                "SELECT id FROM bookshelf_layouts WHERE user_id = ?",
                [$userId]
            );
            
            if ($existing) {
                // Update existing layout
                $db->execute(
                    "UPDATE bookshelf_layouts 
                     SET layout_data = ?, theme = ?, updated_at = CURRENT_TIMESTAMP 
                     WHERE user_id = ?",
                    [$layoutDataJson, $theme, $userId]
                );
            } else {
                // Create new layout
                $db->execute(
                    "INSERT INTO bookshelf_layouts (user_id, layout_data, theme) 
                     VALUES (?, ?, ?)",
                    [$userId, $layoutDataJson, $theme]
                );
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Layout saved successfully'
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
    error_log("Bookshelf API Error: " . $e->getMessage());
}
?>

