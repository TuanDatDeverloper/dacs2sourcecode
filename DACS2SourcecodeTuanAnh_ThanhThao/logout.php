<?php
/**
 * Logout Page - BookOnline
 * Xử lý đăng xuất và redirect
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/auth.php';

$auth = new Auth();

// Logout
$auth->logout();

// Redirect về trang chủ
header('Location: index.php');
exit;
?>

