<?php
/**
 * Test Database Connection
 * Script để test kết nối database
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Database Connection</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .header { background: linear-gradient(135deg, #FFB347, #FF9500); color: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .check-item { background: white; padding: 15px; margin: 10px 0; border-radius: 8px; border-left: 4px solid #ddd; }
        .check-item.success { border-left-color: #10B981; }
        .check-item.error { border-left-color: #EF4444; }
        .status { font-weight: bold; margin-left: 10px; }
        .status.success { color: #10B981; }
        .status.error { color: #EF4444; }
        .code { background: #f4f4f4; padding: 10px; border-radius: 5px; font-family: monospace; margin-top: 10px; }
        .solution { background: #E0F2F7; padding: 15px; border-radius: 8px; margin-top: 10px; }
        .config-display { background: #fff3cd; padding: 15px; border-radius: 8px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔍 Test Database Connection</h1>
        <p>Kiểm tra kết nối database</p>
    </div>

    <?php
    // Load config
    $configFile = __DIR__ . '/includes/config.php';
    if (!file_exists($configFile)) {
        echo '<div class="check-item error">';
        echo '<strong>❌ Lỗi:</strong> File config.php không tồn tại!';
        echo '<div class="solution">Upload file includes/config.php lên server</div>';
        echo '</div>';
        exit;
    }

    require_once $configFile;

    // Display current config (hide password)
    echo '<div class="config-display">';
    echo '<strong>📋 Thông Tin Database Hiện Tại:</strong><br>';
    echo '<div class="code">';
    echo 'DB_HOST: ' . (defined('DB_HOST') ? htmlspecialchars(DB_HOST) : '❌ Chưa định nghĩa') . '<br>';
    echo 'DB_USER: ' . (defined('DB_USER') ? htmlspecialchars(DB_USER) : '❌ Chưa định nghĩa') . '<br>';
    echo 'DB_PASS: ' . (defined('DB_PASS') ? (strlen(DB_PASS) > 0 ? '***' . substr(DB_PASS, -2) : '❌ Rỗng') : '❌ Chưa định nghĩa') . '<br>';
    echo 'DB_NAME_MYSQL: ' . (defined('DB_NAME_MYSQL') ? htmlspecialchars(DB_NAME_MYSQL) : '❌ Chưa định nghĩa') . '<br>';
    echo '</div>';
    echo '</div>';

    // Test connection
    $checks = [];
    
    // Check 1: Config defined
    $allDefined = defined('DB_HOST') && defined('DB_USER') && defined('DB_PASS') && defined('DB_NAME_MYSQL');
    $checks[] = [
        'name' => 'Config đã được định nghĩa',
        'status' => $allDefined ? 'success' : 'error',
        'message' => $allDefined ? '✅ Tất cả config đã được định nghĩa' : '❌ Thiếu một số config',
        'solution' => $allDefined ? '' : 'Kiểm tra file includes/config.php đã có đầy đủ DB_HOST, DB_USER, DB_PASS, DB_NAME_MYSQL'
    ];

    if ($allDefined) {
        // Check 2: Try connection
        try {
            require_once __DIR__ . '/includes/database.php';
            $db = new Database();
            
            // Test query
            $test = $db->fetchOne("SELECT 1 as test, DATABASE() as db_name, USER() as db_user");
            
            $checks[] = [
                'name' => 'Kết nối Database',
                'status' => 'success',
                'message' => '✅ Kết nối database thành công!',
                'solution' => ''
            ];
            
            $checks[] = [
                'name' => 'Thông tin Database',
                'status' => 'success',
                'message' => 'Database: ' . ($test['db_name'] ?? 'N/A') . ', User: ' . ($test['db_user'] ?? 'N/A'),
                'solution' => ''
            ];
            
            // Check tables
            $tables = $db->fetchAll("SHOW TABLES");
            $tableCount = count($tables);
            
            $checks[] = [
                'name' => 'Kiểm tra Bảng',
                'status' => $tableCount > 0 ? 'success' : 'error',
                'message' => $tableCount > 0 
                    ? "✅ Tìm thấy $tableCount bảng trong database" 
                    : "❌ Không có bảng nào trong database - Cần import database!",
                'solution' => $tableCount > 0 ? '' : 'Import file database/DEPLOY_FOR_INFINITYFREE.sql vào phpMyAdmin'
            ];
            
            if ($tableCount > 0) {
                $tableNames = array_map(function($t) { return array_values($t)[0]; }, $tables);
                $importantTables = ['users', 'books', 'user_books'];
                $missingTables = array_diff($importantTables, $tableNames);
                
                if (empty($missingTables)) {
                    $checks[] = [
                        'name' => 'Bảng Quan Trọng',
                        'status' => 'success',
                        'message' => '✅ Tất cả bảng quan trọng đã có: ' . implode(', ', $importantTables),
                        'solution' => ''
                    ];
                } else {
                    $checks[] = [
                        'name' => 'Bảng Quan Trọng',
                        'status' => 'error',
                        'message' => '❌ Thiếu bảng: ' . implode(', ', $missingTables),
                        'solution' => 'Import file database/DEPLOY_FOR_INFINITYFREE.sql vào phpMyAdmin'
                    ];
                }
            }
            
        } catch (Exception $e) {
            $errorMsg = $e->getMessage();
            
            $solution = getSolutionForError($errorMsg);
            
            $checks[] = [
                'name' => 'Kết nối Database',
                'status' => 'error',
                'message' => '❌ Không thể kết nối database: ' . htmlspecialchars($errorMsg),
                'solution' => $solution
            ];
        }
    }

    function getSolutionForError($errorMsg) {
        if (strpos($errorMsg, 'Access denied') !== false) {
            return 'Kiểm tra lại DB_USER và DB_PASS trong includes/config.php';
        }
        if (strpos($errorMsg, 'Unknown database') !== false) {
            return 'Database chưa được tạo hoặc DB_NAME_MYSQL sai. Tạo database trong Control Panel hoặc sửa DB_NAME_MYSQL';
        }
        if (strpos($errorMsg, 'Connection refused') !== false || strpos($errorMsg, 'Host') !== false) {
            return 'DB_HOST sai. Kiểm tra lại DB_HOST trong includes/config.php (ví dụ: sqlXXX.infinityfree.com)';
        }
        return 'Kiểm tra lại tất cả thông tin database trong includes/config.php';
    }

    // Display checks
    foreach ($checks as $check) {
        echo '<div class="check-item ' . $check['status'] . '">';
        echo '<strong>' . $check['name'] . '</strong>';
        echo '<span class="status ' . $check['status'] . '">' . $check['message'] . '</span>';
        if (!empty($check['solution'])) {
            echo '<div class="solution">';
            echo '<strong>💡 Giải pháp:</strong> ' . $check['solution'];
            echo '</div>';
        }
        echo '</div>';
    }

    function getSolutionForError($errorMsg) {
        if (strpos($errorMsg, 'Access denied') !== false) {
            return 'Kiểm tra lại DB_USER và DB_PASS trong includes/config.php';
        }
        if (strpos($errorMsg, 'Unknown database') !== false) {
            return 'Database chưa được tạo hoặc DB_NAME_MYSQL sai. Tạo database trong Control Panel hoặc sửa DB_NAME_MYSQL';
        }
        if (strpos($errorMsg, 'Connection refused') !== false || strpos($errorMsg, 'Host') !== false) {
            return 'DB_HOST sai. Kiểm tra lại DB_HOST trong includes/config.php (ví dụ: sqlXXX.infinityfree.com)';
        }
        return 'Kiểm tra lại tất cả thông tin database trong includes/config.php';
    }
    ?>

    <div class="header" style="margin-top: 30px;">
        <h2>📝 Hướng Dẫn Sửa</h2>
        <div class="solution">
            <strong>1. Lấy thông tin Database từ InfinityFree:</strong><br>
            - Vào Control Panel → MySQL Databases<br>
            - Copy: Database Host, Username, Database Name, Password<br><br>
            
            <strong>2. Sửa file includes/config.php:</strong><br>
            <div class="code">
define('DB_HOST', 'sqlXXX.infinityfree.com'); // Thay XXX<br>
define('DB_USER', 'if0_40750024'); // Username của bạn<br>
define('DB_PASS', 'YOUR_PASSWORD'); // Password của bạn<br>
define('DB_NAME_MYSQL', 'if0_40750024_hoa'); // Tên database của bạn
            </div><br>
            
            <strong>3. Import Database:</strong><br>
            - Vào phpMyAdmin<br>
            - Chọn database của bạn<br>
            - Import file database/DEPLOY_FOR_INFINITYFREE.sql
        </div>
    </div>
</body>
</html>

