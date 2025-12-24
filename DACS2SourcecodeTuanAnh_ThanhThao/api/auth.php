<?php
/**
 * Authentication API - BookOnline
 * Endpoints: login, register, logout, check auth status
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

// Clear any output buffer (keep buffer open for later use)
ob_clean();

$auth = new Auth();
$method = $_SERVER['REQUEST_METHOD'];

try {
    switch ($method) {
        case 'POST':
            $action = $_POST['action'] ?? $_GET['action'] ?? '';
            
            if ($action === 'login') {
                $email = $_POST['email'] ?? '';
                $password = $_POST['password'] ?? '';
                
                $result = $auth->login($email, $password);
                
                if ($result['success']) {
                    http_response_code(200);
                } else {
                    http_response_code(401);
                }
                
                echo json_encode($result);
                
            } elseif ($action === 'register') {
                $email = $_POST['email'] ?? '';
                $password = $_POST['password'] ?? '';
                $fullName = $_POST['full_name'] ?? '';
                $username = $_POST['username'] ?? '';
                
                $result = $auth->register($email, $password, $fullName, $username);
                
                if ($result['success']) {
                    http_response_code(201);
                } else {
                    http_response_code(400);
                }
                
                echo json_encode($result);
                
            } elseif ($action === 'logout') {
                $result = $auth->logout();
                http_response_code(200);
                echo json_encode($result);
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
            }
            break;
            
        case 'GET':
            $action = $_GET['action'] ?? '';
            
            if ($action === 'check' || isset($_GET['check'])) {
                $user = $auth->getCurrentUser();
                echo json_encode([
                    'logged_in' => $auth->isLoggedIn(),
                    'user' => $user
                ]);
            } elseif ($action === 'logout') {
                // Support GET logout for backward compatibility
                $result = $auth->logout();
                // Redirect to home page
                header('Location: ' . SITE_URL . '/index.php');
                exit;
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid request']);
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
    error_log("Auth API Error: " . $e->getMessage());
    exit;
}
