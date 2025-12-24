<?php
/**
 * Books API - BookOnline
 * CRUD operations for books
 */

header('Content-Type: application/json; charset=utf-8');
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
            if (isset($_GET['id'])) {
                // Get single book
                $bookId = $_GET['id'];
                
                // Get book info
                $book = $db->fetchOne(
                    "SELECT * FROM books WHERE id = ?",
                    [$bookId]
                );
                
                if (!$book) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Book not found']);
                    exit;
                }
                
                // Get user's relationship with book
                $userBook = $db->fetchOne(
                    "SELECT status, rating, notes, added_at 
                     FROM user_books 
                     WHERE user_id = ? AND book_id = ?",
                    [$userId, $bookId]
                );
                
                // Get reading progress
                $progress = $db->fetchOne(
                    "SELECT current_page, total_pages, progress_percent, last_read_at, bookmark 
                     FROM reading_progress 
                     WHERE user_id = ? AND book_id = ?",
                    [$userId, $bookId]
                );
                
                // Merge data
                $result = array_merge($book, $userBook ?: [], $progress ?: []);
                
                // Parse categories if JSON
                if (isset($result['categories']) && !empty($result['categories'])) {
                    $categories = json_decode($result['categories'], true);
                    $result['categories'] = is_array($categories) ? $categories : [$result['categories']];
                }
                
                echo json_encode($result);
                
            } else {
                // Get all user's books
                $status = $_GET['status'] ?? 'all';
                $search = $_GET['search'] ?? '';
                $category = $_GET['category'] ?? '';
                
                $sql = "SELECT b.*, ub.status, ub.rating, ub.added_at,
                               rp.current_page, rp.total_pages, rp.progress_percent
                        FROM books b 
                        INNER JOIN user_books ub ON b.id = ub.book_id 
                        LEFT JOIN reading_progress rp ON b.id = rp.book_id AND rp.user_id = ?
                        WHERE ub.user_id = ?";
                $params = [$userId, $userId];
                
                if ($status !== 'all') {
                    $sql .= " AND ub.status = ?";
                    $params[] = $status;
                }
                
                if (!empty($search)) {
                    $sql .= " AND (b.title LIKE ? OR b.author LIKE ?)";
                    $searchParam = "%$search%";
                    $params[] = $searchParam;
                    $params[] = $searchParam;
                }
                
                if (!empty($category)) {
                    $sql .= " AND b.categories LIKE ?";
                    $params[] = "%$category%";
                }
                
                // Order by last_read_at for reading status, otherwise by added_at
                if ($status === 'reading') {
                    $sql .= " ORDER BY rp.last_read_at DESC, ub.added_at DESC";
                } else {
                    $sql .= " ORDER BY ub.added_at DESC";
                }
                
                $books = $db->fetchAll($sql, $params);
                
                // Parse categories and ensure all fields are present
                foreach ($books as &$book) {
                    if (isset($book['categories']) && !empty($book['categories'])) {
                        $categories = json_decode($book['categories'], true);
                        $book['categories'] = is_array($categories) ? $categories : [$book['categories']];
                    } else {
                        $book['categories'] = [];
                    }
                    
                    // Ensure all required fields exist
                    $book['cover'] = $book['cover_url'] ?? '';
                    $book['cover_url'] = $book['cover_url'] ?? '';
                    $book['status'] = $book['status'] ?? 'want_to_read';
                    // Progress is from reading_progress table, not user_books
                    $book['progress'] = $book['progress_percent'] ?? 0;
                    $book['progress_percent'] = $book['progress_percent'] ?? 0;
                    $book['current_page'] = $book['current_page'] ?? 0;
                    $book['total_pages'] = $book['total_pages'] ?? ($book['page_count'] ?? 0);
                }
                
                echo json_encode($books);
            }
            break;
            
        case 'POST':
            // Add new book
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data)) {
                $data = $_POST;
            }
            
            if (empty($data['id']) && empty($data['title'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Book ID or title is required']);
                exit;
            }
            
            // Generate ID if not provided
            $bookId = $data['id'] ?? 'book_' . time() . '_' . rand(1000, 9999);
            
            // Check if book exists
            $existing = $db->fetchOne("SELECT id FROM books WHERE id = ?", [$bookId]);
            
            if (!$existing) {
                // Insert new book
                $categories = is_array($data['categories'] ?? []) 
                    ? json_encode($data['categories']) 
                    : ($data['categories'] ?? '');
                
                $db->execute(
                    "INSERT INTO books (id, title, author, description, cover_url, isbn, page_count, published_date, categories, source) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $bookId,
                        $data['title'] ?? '',
                        $data['author'] ?? '',
                        $data['description'] ?? '',
                        $data['cover'] ?? $data['cover_url'] ?? '',
                        $data['isbn'] ?? '',
                        $data['page_count'] ?? 0,
                        $data['published_date'] ?? null,
                        $categories,
                        $data['source'] ?? 'google_books'
                    ]
                );
            }
            
            // Link to user (check if exists first)
            $existingUserBook = $db->fetchOne(
                "SELECT id FROM user_books WHERE user_id = ? AND book_id = ?",
                [$userId, $bookId]
            );
            
            if ($existingUserBook) {
                // Update existing
                $db->execute(
                    "UPDATE user_books SET status = ? WHERE user_id = ? AND book_id = ?",
                    [$data['status'] ?? 'want_to_read', $userId, $bookId]
                );
            } else {
                // Insert new
                $db->execute(
                    "INSERT INTO user_books (user_id, book_id, status) 
                     VALUES (?, ?, ?)",
                    [$userId, $bookId, $data['status'] ?? 'want_to_read']
                );
            }
            
            // Initialize reading progress if not exists
            $existingProgress = $db->fetchOne(
                "SELECT id FROM reading_progress WHERE user_id = ? AND book_id = ?",
                [$userId, $bookId]
            );
            
            if (!$existingProgress) {
                $book = $db->fetchOne("SELECT page_count FROM books WHERE id = ?", [$bookId]);
                $totalPages = $book['page_count'] ?? 0;
                
                $db->execute(
                    "INSERT INTO reading_progress (user_id, book_id, total_pages) 
                     VALUES (?, ?, ?)",
                    [$userId, $bookId, $totalPages]
                );
            }
            
            echo json_encode(['success' => true, 'book_id' => $bookId]);
            break;
            
        case 'PUT':
            // Update book (status, rating, notes, progress)
            $data = json_decode(file_get_contents('php://input'), true);
            $bookId = $data['book_id'] ?? $_GET['id'] ?? null;
            
            if (!$bookId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Book ID is required']);
                exit;
            }
            
            // Update user_books
            $updates = [];
            $params = [];
            
            if (isset($data['status'])) {
                $updates[] = "status = ?";
                $params[] = $data['status'];
            }
            
            if (isset($data['rating'])) {
                $updates[] = "rating = ?";
                $params[] = $data['rating'];
            }
            
            if (isset($data['notes'])) {
                $updates[] = "notes = ?";
                $params[] = $data['notes'];
            }
            
            if (!empty($updates)) {
                $params[] = $userId;
                $params[] = $bookId;
                $sql = "UPDATE user_books SET " . implode(', ', $updates) . " WHERE user_id = ? AND book_id = ?";
                $db->execute($sql, $params);
            }
            
            // Update reading progress
            if (isset($data['progress']) || isset($data['current_page'])) {
                $progress = $data['progress'] ?? null;
                $currentPage = $data['current_page'] ?? null;
                
                // Get total pages
                $book = $db->fetchOne("SELECT page_count FROM books WHERE id = ?", [$bookId]);
                $totalPages = $book['page_count'] ?? 0;
                
                // Calculate progress if not provided
                if ($progress === null && $currentPage !== null && $totalPages > 0) {
                    $progress = ($currentPage / $totalPages) * 100;
                }
                
                $db->execute(
                    "INSERT INTO reading_progress (user_id, book_id, current_page, total_pages, progress_percent, last_read_at)
                     VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
                     ON DUPLICATE KEY UPDATE 
                     current_page = COALESCE(?, current_page),
                     total_pages = COALESCE(?, total_pages),
                     progress_percent = COALESCE(?, progress_percent),
                     last_read_at = CURRENT_TIMESTAMP",
                    [
                        $userId, $bookId, $currentPage, $totalPages, $progress,
                        $currentPage, $totalPages, $progress
                    ]
                );
                
                // Update progress in user_books
                if ($progress !== null) {
                    $db->execute(
                        "UPDATE user_books SET progress = ? WHERE user_id = ? AND book_id = ?",
                        [$progress, $userId, $bookId]
                    );
                }
                
                // Check if completed (progress >= 100%)
                if ($progress >= 100) {
                    $db->execute(
                        "UPDATE user_books SET status = 'completed' 
                         WHERE user_id = ? AND book_id = ?",
                        [$userId, $bookId]
                    );
                    
                    // Award coins
                    $baseCoins = 100;
                    $pageBonus = floor($totalPages / 10);
                    $totalCoins = $baseCoins + $pageBonus;
                    
                    $db->execute(
                        "UPDATE users SET coins = coins + ? WHERE id = ?",
                        [$totalCoins, $userId]
                    );
                }
            }
            
            echo json_encode(['success' => true]);
            break;
            
        case 'DELETE':
            // Remove book from user's library
            $bookId = $_GET['id'] ?? null;
            
            if (!$bookId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Book ID is required']);
                exit;
            }
            
            $db->execute(
                "DELETE FROM user_books WHERE user_id = ? AND book_id = ?",
                [$userId, $bookId]
            );
            
            // Also delete reading progress
            $db->execute(
                "DELETE FROM reading_progress WHERE user_id = ? AND book_id = ?",
                [$userId, $bookId]
            );
            
            echo json_encode(['success' => true]);
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
    error_log("Books API Error: " . $e->getMessage());
}
?>

