<?php
/**
 * Upload Book API - BookOnline
 * Handle book file uploads and add to database
 */

header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';

$auth = new Auth();
$auth->requireLogin();

$db = new Database();
$userId = $_SESSION['user_id'];

// Create uploads directory if it doesn't exist
$uploadDir = __DIR__ . '/../assets/uploads/books/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }
    
    // Check if file was uploaded
    if (!isset($_FILES['book_file']) || $_FILES['book_file']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
        exit;
    }
    
    $file = $_FILES['book_file'];
    $allowedTypes = ['application/pdf', 'application/epub+zip', 'application/epub', 'text/plain', 'text/html'];
    $allowedExtensions = ['pdf', 'epub', 'txt', 'html', 'htm'];
    
    // Get file extension
    $fileName = $file['name'];
    $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
    // Validate file type
    if (!in_array($fileExtension, $allowedExtensions) && !in_array($file['type'], $allowedTypes)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Allowed: PDF, EPUB, TXT, HTML']);
        exit;
    }
    
    // Validate file size (max 50MB)
    $maxSize = 50 * 1024 * 1024; // 50MB
    if ($file['size'] > $maxSize) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'File too large. Maximum size: 50MB']);
        exit;
    }
    
    // Get book metadata from form
    $title = $_POST['title'] ?? '';
    $author = $_POST['author'] ?? '';
    $description = $_POST['description'] ?? '';
    $category = $_POST['category'] ?? 'General';
    $categories = isset($_POST['categories']) ? json_decode($_POST['categories'], true) : [$category];
    $pageCount = intval($_POST['page_count'] ?? 0);
    $publishedDate = $_POST['published_date'] ?? null;
    $isbn = $_POST['isbn'] ?? '';
    
    // If title/author not provided, try to extract from filename
    if (empty($title)) {
        $title = pathinfo($fileName, PATHINFO_FILENAME);
        // Try to parse "Author - Title" format
        if (strpos($title, ' - ') !== false) {
            $parts = explode(' - ', $title, 2);
            if (count($parts) === 2) {
                $author = trim($parts[0]);
                $title = trim($parts[1]);
            }
        }
    }
    
    if (empty($title)) {
        $title = 'Untitled Book';
    }
    if (empty($author)) {
        $author = 'Unknown Author';
    }
    
    // Generate unique book ID
    $bookId = 'uploaded_' . time() . '_' . rand(1000, 9999);
    
    // Generate unique filename
    $safeFileName = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileName);
    $uniqueFileName = $bookId . '_' . $safeFileName;
    $filePath = $uploadDir . $uniqueFileName;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filePath)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to save file']);
        exit;
    }
    
    // Try to extract metadata from file (basic)
    // For PDF/EPUB, we could use libraries, but for now we'll use provided data
    
    // Auto-categorize based on title/description keywords
    $autoCategory = detectCategory($title, $description, $category);
    if (!in_array($autoCategory, $categories)) {
        $categories[] = $autoCategory;
    }
    
    // Save book to database
    $categoriesJson = json_encode($categories);
    $fileUrl = '/assets/uploads/books/' . $uniqueFileName;
    
    // Check if book already exists (by title and author)
    $existing = $db->fetchOne(
        "SELECT id FROM books WHERE title = ? AND author = ?",
        [$title, $author]
    );
    
    if ($existing) {
        $bookId = $existing['id'];
        // Update file path if needed
        $db->execute(
            "UPDATE books SET cover_url = ?, source = 'uploaded' WHERE id = ?",
            [$fileUrl, $bookId]
        );
    } else {
        // Insert new book
        $db->execute(
            "INSERT INTO books (id, title, author, description, cover_url, isbn, page_count, published_date, categories, source) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'uploaded')",
            [$bookId, $title, $author, $description, $fileUrl, $isbn, $pageCount, $publishedDate, $categoriesJson]
        );
    }
    
    // Don't automatically add to user's library
    // User will add it when they click "Đọc ngay" or manually add it
    // This way, uploaded books only appear in "Sách mới", not in "Sách của tôi" until user reads them
    
    // Initialize reading progress
    $existingProgress = $db->fetchOne(
        "SELECT id FROM reading_progress WHERE user_id = ? AND book_id = ?",
        [$userId, $bookId]
    );
    
    if (!$existingProgress) {
        $db->execute(
            "INSERT INTO reading_progress (user_id, book_id, total_pages) 
             VALUES (?, ?, ?)",
            [$userId, $bookId, $pageCount]
        );
    }
    
    echo json_encode([
        'success' => true,
        'book_id' => $bookId,
        'message' => 'Sách đã được tải lên thành công!',
        'book' => [
            'id' => $bookId,
            'title' => $title,
            'author' => $author,
            'category' => $autoCategory,
            'file_url' => $fileUrl
        ]
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
    error_log("Upload Book API Error: " . $e->getMessage());
}

/**
 * Auto-detect category based on title and description
 */
function detectCategory($title, $description, $defaultCategory = 'General') {
    $text = strtolower($title . ' ' . $description);
    
    $categoryKeywords = [
        'fiction' => ['fiction', 'novel', 'tiểu thuyết', 'truyện', 'story'],
        'literature' => ['literature', 'văn học', 'poetry', 'thơ', 'poem'],
        'history' => ['history', 'lịch sử', 'historical', 'war', 'chiến tranh'],
        'science' => ['science', 'khoa học', 'physics', 'chemistry', 'biology', 'vật lý', 'hóa học'],
        'philosophy' => ['philosophy', 'triết học', 'philosophical', 'ethics', 'đạo đức']
    ];
    
    foreach ($categoryKeywords as $category => $keywords) {
        foreach ($keywords as $keyword) {
            if (strpos($text, $keyword) !== false) {
                return $category;
            }
        }
    }
    
    return $defaultCategory;
}
?>

