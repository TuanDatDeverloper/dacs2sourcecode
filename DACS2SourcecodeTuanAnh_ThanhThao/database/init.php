<?php
/**
 * Database Initialization Script
 * Chạy script này để tạo database và tables
 * 
 * Usage: php database/init.php
 * Hoặc truy cập qua browser: http://localhost/DACS2SourcecodeTuanAnh_ThanhThao/database/init.php
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';

// Read schema file
$schemaFile = __DIR__ . '/schema.sql';

if (!file_exists($schemaFile)) {
    die("Error: schema.sql file not found!");
}

$schema = file_get_contents($schemaFile);

// Split by semicolon and execute each statement
$statements = array_filter(
    array_map('trim', explode(';', $schema)),
    function($stmt) {
        return !empty($stmt) && !preg_match('/^--/', $stmt);
    }
);

$db = new Database();
$success = true;
$errors = [];

try {
    $db->beginTransaction();
    
    foreach ($statements as $statement) {
        if (!empty(trim($statement))) {
            try {
                $db->execute($statement);
            } catch (PDOException $e) {
                // Ignore "table already exists" errors
                if (strpos($e->getMessage(), 'already exists') === false) {
                    $errors[] = $e->getMessage();
                    $success = false;
                }
            }
        }
    }
    
    if ($success) {
        $db->commit();
        $message = "Database initialized successfully!";
    } else {
        $db->rollback();
        $message = "Database initialization completed with some errors.";
    }
    
} catch (Exception $e) {
    $db->rollback();
    $success = false;
    $message = "Error: " . $e->getMessage();
    $errors[] = $e->getMessage();
}

// Output result
if (php_sapi_name() === 'cli') {
    // Command line
    echo "\n";
    echo "========================================\n";
    echo "Database Initialization\n";
    echo "========================================\n";
    echo $message . "\n";
    if (!empty($errors)) {
        echo "\nErrors:\n";
        foreach ($errors as $error) {
            echo "- " . $error . "\n";
        }
    }
    echo "========================================\n";
} else {
    // Web browser
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Database Initialization</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                max-width: 800px;
                margin: 50px auto;
                padding: 20px;
                background: #f5f5f5;
            }
            .container {
                background: white;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            }
            h1 { color: #333; }
            .success { color: #28a745; }
            .error { color: #dc3545; }
            .errors {
                background: #f8d7da;
                padding: 15px;
                border-radius: 4px;
                margin-top: 20px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Database Initialization</h1>
            <p class="<?php echo $success ? 'success' : 'error'; ?>">
                <?php echo htmlspecialchars($message); ?>
            </p>
            <?php if (!empty($errors)): ?>
                <div class="errors">
                    <strong>Errors:</strong>
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            <p><a href="../index.php">← Back to Home</a></p>
        </div>
    </body>
    </html>
    <?php
}

