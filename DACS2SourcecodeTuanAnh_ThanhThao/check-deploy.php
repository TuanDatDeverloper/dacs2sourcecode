<?php
/**
 * Check Deploy Status
 * Script để kiểm tra tình trạng deploy
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiểm Tra Deploy Status</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 900px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .header { background: linear-gradient(135deg, #FFB347, #FF9500); color: white; padding: 20px; border-radius: 10px; margin-bottom: 20px; }
        .check-item { background: white; padding: 15px; margin: 10px 0; border-radius: 8px; border-left: 4px solid #ddd; }
        .check-item.success { border-left-color: #10B981; }
        .check-item.error { border-left-color: #EF4444; }
        .check-item.warning { border-left-color: #F59E0B; }
        .status { font-weight: bold; margin-left: 10px; }
        .status.success { color: #10B981; }
        .status.error { color: #EF4444; }
        .status.warning { color: #F59E0B; }
        .code { background: #f4f4f4; padding: 10px; border-radius: 5px; font-family: monospace; margin-top: 10px; }
        .solution { background: #E0F2F7; padding: 15px; border-radius: 8px; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔍 Kiểm Tra Deploy Status</h1>
        <p>Domain: <strong>https://mapprod.great-site.net/</strong></p>
    </div>

    <?php
    $checks = [];
    $allPassed = true;

    // Check 1: File index.php exists
    $indexExists = file_exists(__DIR__ . '/index.php');
    $checks[] = [
        'name' => 'File index.php tồn tại',
        'status' => $indexExists ? 'success' : 'error',
        'message' => $indexExists ? '✅ File index.php đã có' : '❌ File index.php không tồn tại',
        'solution' => $indexExists ? '' : 'Upload file index.php lên /htdocs/'
    ];
    if (!$indexExists) $allPassed = false;

    // Check 2: File index2.html exists (should NOT exist)
    $index2Exists = file_exists(__DIR__ . '/index2.html');
    $checks[] = [
        'name' => 'File index2.html (phải XÓA)',
        'status' => !$index2Exists ? 'success' : 'error',
        'message' => !$index2Exists ? '✅ File index2.html đã được xóa' : '❌ File index2.html vẫn còn - PHẢI XÓA!',
        'solution' => $index2Exists ? 'Xóa file index2.html trong /htdocs/' : ''
    ];
    if ($index2Exists) $allPassed = false;

    // Check 3: Config.php exists
    $configExists = file_exists(__DIR__ . '/includes/config.php');
    $checks[] = [
        'name' => 'File config.php tồn tại',
        'status' => $configExists ? 'success' : 'error',
        'message' => $configExists ? '✅ File config.php đã có' : '❌ File config.php không tồn tại',
        'solution' => $configExists ? '' : 'Upload file includes/config.php'
    ];
    if (!$configExists) $allPassed = false;

    // Check 4: Config.php content
    if ($configExists) {
        $configContent = file_get_contents(__DIR__ . '/includes/config.php');
        $hasLocalhost = strpos($configContent, 'localhost') !== false;
        $hasMapprod = strpos($configContent, 'mapprod.great-site.net') !== false;
        
        $checks[] = [
            'name' => 'Config.php đã sửa SITE_URL',
            'status' => $hasMapprod && !$hasLocalhost ? 'success' : 'error',
            'message' => $hasMapprod && !$hasLocalhost 
                ? '✅ SITE_URL đã được sửa thành mapprod.great-site.net' 
                : '❌ SITE_URL vẫn là localhost hoặc chưa sửa',
            'solution' => $hasMapprod && !$hasLocalhost ? '' : 'Sửa SITE_URL trong includes/config.php thành: define(\'SITE_URL\', \'https://mapprod.great-site.net\');'
        ];
        if (!$hasMapprod || $hasLocalhost) $allPassed = false;
    }

    // Check 5: Database connection
    if ($configExists) {
        try {
            require_once __DIR__ . '/includes/config.php';
            require_once __DIR__ . '/includes/database.php';
            $db = new Database();
            $test = $db->fetchOne("SELECT 1 as test");
            $dbConnected = true;
        } catch (Exception $e) {
            $dbConnected = false;
            $dbError = $e->getMessage();
        }
        
        $checks[] = [
            'name' => 'Kết nối Database',
            'status' => $dbConnected ? 'success' : 'error',
            'message' => $dbConnected ? '✅ Kết nối database thành công' : '❌ Không thể kết nối database: ' . ($dbError ?? 'Unknown error'),
            'solution' => $dbConnected ? '' : 'Kiểm tra lại DB_HOST, DB_USER, DB_PASS, DB_NAME_MYSQL trong includes/config.php'
        ];
        if (!$dbConnected) $allPassed = false;
    }

    // Check 6: Current directory
    $currentDir = __DIR__;
    $isInSubfolder = strpos($currentDir, 'DACS2SourcecodeTuanAnh_ThanhThao') !== false || 
                     strpos($currentDir, 'dacs2sourcecode') !== false;
    
    $checks[] = [
        'name' => 'Vị trí file (phải ở root)',
        'status' => !$isInSubfolder ? 'success' : 'warning',
        'message' => !$isInSubfolder 
            ? '✅ File đang ở root /htdocs/' 
            : '⚠️ File đang ở trong subfolder - Nên di chuyển lên root',
        'solution' => $isInSubfolder ? 'Di chuyển tất cả file từ subfolder lên /htdocs/' : ''
    ];

    // Check 7: Important folders exist
    $folders = ['api', 'includes', 'images', 'assets', 'css', 'js'];
    foreach ($folders as $folder) {
        $exists = is_dir(__DIR__ . '/' . $folder);
        $checks[] = [
            'name' => "Folder $folder/",
            'status' => $exists ? 'success' : 'error',
            'message' => $exists ? "✅ Folder $folder/ đã có" : "❌ Folder $folder/ không tồn tại",
            'solution' => $exists ? '' : "Upload folder $folder/ lên server"
        ];
        if (!$exists) $allPassed = false;
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

    // Summary
    echo '<div class="header" style="margin-top: 30px;">';
    if ($allPassed) {
        echo '<h2>✅ Tất Cả Kiểm Tra Đã Pass!</h2>';
        echo '<p>Website đã sẵn sàng. Nếu vẫn không chạy, kiểm tra Error Logs trong Control Panel.</p>';
    } else {
        echo '<h2>❌ Có Lỗi Cần Sửa</h2>';
        echo '<p>Vui lòng sửa các lỗi trên trước khi test lại website.</p>';
    }
    echo '</div>';

    // Show current path info
    echo '<div class="check-item">';
    echo '<strong>Thông Tin Đường Dẫn:</strong>';
    echo '<div class="code">';
    echo 'Current Directory: ' . $currentDir . '<br>';
    echo 'Script Name: ' . ($_SERVER['SCRIPT_NAME'] ?? 'N/A') . '<br>';
    echo 'Document Root: ' . ($_SERVER['DOCUMENT_ROOT'] ?? 'N/A') . '<br>';
    echo 'HTTP Host: ' . ($_SERVER['HTTP_HOST'] ?? 'N/A') . '<br>';
    echo '</div>';
    echo '</div>';
    ?>
</body>
</html>

