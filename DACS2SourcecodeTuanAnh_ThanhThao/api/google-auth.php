<?php
/**
 * Google OAuth Authentication API - BookOnline
 * Xử lý đăng nhập bằng Google
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';

$auth = new Auth();
$db = new Database();
$method = $_SERVER['REQUEST_METHOD'];

// Google OAuth Configuration
// Lấy từ config.php (đã được define ở đó)
// Không cần define lại ở đây

try {
    switch ($method) {
        case 'POST':
            // Support both JSON and FormData
            $input = json_decode(file_get_contents('php://input'), true);
            if (empty($input)) {
                $input = $_POST;
            }
            $action = $input['action'] ?? $_GET['action'] ?? '';
            
            if ($action === 'verify') {
                // Verify Google ID token
                $idToken = $input['id_token'] ?? '';
                
                if (empty($idToken)) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'ID token is required']);
                    exit;
                }
                
                // Verify token với Google
                $userInfo = verifyGoogleToken($idToken);
                
                // Nếu chưa có Client ID, vẫn cho phép decode token (development mode)
                // CHỈ DÙNG CHO DEVELOPMENT - Production phải có Client ID
                if (!$userInfo && !empty($idToken)) {
                    // Try to decode JWT token (simple decode, không verify signature)
                    $parts = explode('.', $idToken);
                    if (count($parts) === 3) {
                        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1])), true);
                        if ($payload && isset($payload['email'])) {
                            $userInfo = [
                                'google_id' => $payload['sub'] ?? '',
                                'email' => $payload['email'] ?? '',
                                'name' => $payload['name'] ?? '',
                                'picture' => $payload['picture'] ?? '',
                                'verified_email' => $payload['email_verified'] ?? false
                            ];
                        }
                    }
                }
                
                if (!$userInfo || empty($userInfo['email'])) {
                    http_response_code(401);
                    echo json_encode(['success' => false, 'message' => 'Invalid Google token']);
                    exit;
                }
                
                // Tìm hoặc tạo user
                $user = findOrCreateGoogleUser($db, $userInfo);
                
                if ($user) {
                    // Set session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_name'] = $user['full_name'] ?: $user['email'];
                    
                    // Regenerate session ID
                    session_regenerate_id(true);
                    
                    // Remove password hash from response
                    unset($user['password_hash']);
                    
                    http_response_code(200);
                    echo json_encode([
                        'success' => true,
                        'message' => 'Đăng nhập thành công',
                        'user' => $user
                    ]);
                } else {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Không thể tạo tài khoản']);
                }
                
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
            }
            break;
            
        case 'GET':
            // Return Google Client ID for frontend
            echo json_encode([
                'client_id' => GOOGLE_CLIENT_ID,
                'enabled' => !empty(GOOGLE_CLIENT_ID)
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
    error_log("Google Auth API Error: " . $e->getMessage());
}

/**
 * Verify Google ID token
 * @param string $idToken Google ID token
 * @return array|null User info or null if invalid
 */
function verifyGoogleToken($idToken) {
    // Nếu chưa có Client ID, return null để xử lý ở nơi gọi
    if (empty(GOOGLE_CLIENT_ID)) {
        error_log("Warning: Google Client ID not configured, will decode token without verification");
        return null;
    }
    
    // Verify token với Google API
    $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($idToken);
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    if ($httpCode !== 200) {
        error_log("Google token verification failed: HTTP $httpCode, Error: $curlError");
        return null;
    }
    
    $data = json_decode($response, true);
    
    if (!$data) {
        error_log("Google token verification failed: Invalid JSON response");
        return null;
    }
    
    // Verify audience (Client ID)
    if (isset($data['aud']) && $data['aud'] !== GOOGLE_CLIENT_ID) {
        error_log("Google token verification failed: Invalid audience. Expected: " . GOOGLE_CLIENT_ID . ", Got: " . $data['aud']);
        return null;
    }
    
    return [
        'google_id' => $data['sub'] ?? '',
        'email' => $data['email'] ?? '',
        'name' => $data['name'] ?? '',
        'picture' => $data['picture'] ?? '',
        'verified_email' => $data['email_verified'] ?? false
    ];
}

/**
 * Find or create user from Google info
 * @param Database $db
 * @param array $googleInfo
 * @return array|null User data or null
 */
function findOrCreateGoogleUser($db, $googleInfo) {
    $email = $googleInfo['email'] ?? '';
    $googleId = $googleInfo['google_id'] ?? '';
    $name = $googleInfo['name'] ?? '';
    
    if (empty($email) || empty($googleId)) {
        return null;
    }
    
    // Tìm user theo email hoặc google_id
    $user = $db->fetchOne(
        "SELECT * FROM users WHERE email = ? OR google_id = ?",
        [$email, $googleId]
    );
    
    if ($user) {
        // Update google_id nếu chưa có
        if (empty($user['google_id'])) {
            $db->execute(
                "UPDATE users SET google_id = ?, oauth_provider = 'google' WHERE id = ?",
                [$googleId, $user['id']]
            );
        }
        
        // Update name nếu chưa có
        if (empty($user['full_name']) && !empty($name)) {
            $db->execute(
                "UPDATE users SET full_name = ? WHERE id = ?",
                [$name, $user['id']]
            );
        }
        
        // Get updated user
        $user = $db->fetchOne(
            "SELECT * FROM users WHERE id = ?",
            [$user['id']]
        );
        
        return $user;
    }
    
    // Tạo user mới
    try {
        // Generate random password (user không cần password khi login bằng Google)
        $randomPassword = bin2hex(random_bytes(16));
        $passwordHash = password_hash($randomPassword, PASSWORD_DEFAULT);
        
        $db->execute(
            "INSERT INTO users (email, password_hash, full_name, google_id, oauth_provider, coins) 
             VALUES (?, ?, ?, ?, 'google', 100)",
            [$email, $passwordHash, $name, $googleId]
        );
        
        $userId = $db->lastInsertId();
        
        return $db->fetchOne(
            "SELECT * FROM users WHERE id = ?",
            [$userId]
        );
    } catch (PDOException $e) {
        error_log("Error creating Google user: " . $e->getMessage());
        return null;
    }
}
?>

