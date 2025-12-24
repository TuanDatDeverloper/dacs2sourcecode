<?php
/**
 * Public Books API - BookOnline
 * Get all public books (including uploaded books) for display
 */

header('Content-Type: application/json; charset=utf-8');
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';

$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method !== 'GET') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);
        exit;
    }
    
    $db = new Database();
    
    $category = $_GET['category'] ?? 'all';
    $search = $_GET['search'] ?? '';
    $limit = intval($_GET['limit'] ?? 40);
    $offset = intval($_GET['offset'] ?? 0);
    
    // Only get uploaded books (from khosach)
    $sql = "SELECT b.* FROM books b WHERE b.source = 'uploaded'";
    $params = [];
    
    // Filter by category
    if ($category !== 'all') {
        $sql .= " AND (b.categories LIKE ? OR b.categories LIKE ?)";
        $categoryParam = "%\"$category\"%";
        $params[] = $categoryParam;
        $params[] = "%$category%";
    }
    
    // Filter by search
    if (!empty($search)) {
        $sql .= " AND (b.title LIKE ? OR b.author LIKE ? OR b.description LIKE ?)";
        $searchParam = "%$search%";
        $params[] = $searchParam;
        $params[] = $searchParam;
        $params[] = $searchParam;
    }
    
    // Order by id DESC (simplest, most reliable)
    $sql .= " ORDER BY b.id DESC";
    
    // LIMIT and OFFSET must be integers, not bound parameters in some MySQL versions
    $sql .= " LIMIT " . intval($limit) . " OFFSET " . intval($offset);
    
    error_log("Public Books API: Executing SQL: $sql");
    
    try {
        $books = $db->fetchAll($sql, $params);
        error_log("Public Books API: Found " . count($books) . " books");
    } catch (Exception $dbError) {
        error_log("Public Books API: Database error: " . $dbError->getMessage());
        throw $dbError;
    }
    
    // Format books to match API format
    $formattedBooks = [];
    foreach ($books as $book) {
        $categories = [];
        if (!empty($book['categories'])) {
            $categories = json_decode($book['categories'], true);
            if (!is_array($categories)) {
                $categories = [$book['categories']];
            }
        }
        
        // Determine if book is free (uploaded books are always free)
        $isFree = $book['source'] === 'uploaded' || !empty($book['cover_url']);
        
        // Decode cover_url nếu bị encode và tạo đường dẫn đúng
        $coverUrl = $book['cover_url'] ?? '';
        if (!empty($coverUrl)) {
            // Decode URL nếu bị encode (có dấu %)
            if (strpos($coverUrl, '%') !== false) {
                $coverUrl = urldecode($coverUrl);
            }
            
            // Nếu là đường dẫn relative bắt đầu bằng /images/, cần thêm base path
            if (strpos($coverUrl, '/images/') === 0) {
                // Lấy base path từ SITE_URL hoặc tự động detect
                $basePath = '';
                if (defined('SITE_URL') && !empty(SITE_URL)) {
                    $parsed = parse_url(SITE_URL);
                    $basePath = $parsed['path'] ?? '';
                    // Remove trailing slash
                    $basePath = rtrim($basePath, '/');
                } else {
                    // Auto-detect từ script path (api/public-books.php -> ../)
                    $scriptPath = dirname(dirname($_SERVER['SCRIPT_NAME'] ?? ''));
                    $basePath = rtrim($scriptPath, '/');
                }
                
                // Tạo đường dẫn relative từ root của domain
                // Ví dụ: /dacs2sourcecode/DACS2SourcecodeTuanAnh_ThanhThao/images/book.jpg
                if (!empty($basePath)) {
                    $coverUrl = $basePath . $coverUrl;
                }
            }
        }
        
        $formattedBooks[] = [
            'id' => $book['id'],
            'title' => $book['title'],
            'authors' => [$book['author']],
            'author' => $book['author'],
            'description' => $book['description'] ?? '',
            'cover' => $coverUrl,
            'categories' => $categories,
            'category' => $categories[0] ?? 'General',
            'publishedDate' => $book['published_date'] ?? 'N/A',
            'pageCount' => $book['page_count'] ?? 0,
            'rating' => 0,
            'ratingsCount' => 0,
            'language' => 'vi',
            'previewLink' => $book['source'] === 'uploaded' ? ('reading.php?id=' . $book['id']) : '',
            'infoLink' => 'book-info.php?id=' . $book['id'],
            'isFree' => $isFree,
            'isbn' => $book['isbn'] ?? '',
            'source' => $book['source'] ?? 'manual'
        ];
    }
    
    echo json_encode($formattedBooks);
    
} catch (Exception $e) {
    http_response_code(500);
    $errorMessage = $e->getMessage();
    $errorFile = $e->getFile();
    $errorLine = $e->getLine();
    error_log("Public Books API Error: " . $errorMessage . " in " . $errorFile . ":" . $errorLine);
    error_log("Public Books API Trace: " . $e->getTraceAsString());
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $errorMessage,
        'error' => $errorMessage,
        'file' => basename($errorFile),
        'line' => $errorLine
    ]);
} catch (Error $e) {
    http_response_code(500);
    $errorMessage = $e->getMessage();
    $errorFile = $e->getFile();
    $errorLine = $e->getLine();
    error_log("Public Books API Fatal Error: " . $errorMessage . " in " . $errorFile . ":" . $errorLine);
    echo json_encode([
        'success' => false,
        'message' => 'Fatal error: ' . $errorMessage,
        'error' => $errorMessage,
        'file' => basename($errorFile),
        'line' => $errorLine
    ]);
}
?>
