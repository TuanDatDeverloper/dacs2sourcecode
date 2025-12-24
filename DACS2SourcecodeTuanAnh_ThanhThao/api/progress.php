<?php
/**
 * Reading Progress API - BookOnline
 * Update and get reading progress
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

// Debug logging
error_log("Progress API called: Method=$method, UserID=$userId");

try {
    switch ($method) {
        case 'GET':
            $bookId = $_GET['book_id'] ?? null;
            
            if (!$bookId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Book ID is required']);
                exit;
            }
            
            $progress = $db->fetchOne(
                "SELECT * FROM reading_progress WHERE user_id = ? AND book_id = ?",
                [$userId, $bookId]
            );
            
            if ($progress) {
                echo json_encode($progress);
            } else {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Progress not found']);
            }
            break;
            
        case 'PUT':
        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data)) {
                $data = $_POST;
            }
            
            $bookId = $data['book_id'] ?? null;
            $currentPage = $data['current_page'] ?? $data['position'] ?? 0;
            $progress = $data['progress'] ?? null;
            
            if (!$bookId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Book ID is required']);
                exit;
            }
            
            // Get book info - if book doesn't exist, we can't track progress
            $book = $db->fetchOne("SELECT page_count FROM books WHERE id = ?", [$bookId]);
            if (!$book) {
                error_log("ERROR: Book not found in database: BookID=$bookId, UserID=$userId");
                http_response_code(404);
                echo json_encode([
                    'success' => false, 
                    'message' => 'Book not found in database. Please add the book first.',
                    'book_id' => $bookId
                ]);
                exit;
            }
            $totalPages = $book['page_count'] ?? 0;
            error_log("Book found: BookID=$bookId, TotalPages=$totalPages");
            
            // Calculate progress if not provided
            if ($progress === null && $totalPages > 0) {
                $progress = ($currentPage / $totalPages) * 100;
            } elseif ($progress === null) {
                $progress = 0;
            }
            
            // Update reading progress (check if exists first)
            $existingProgress = $db->fetchOne(
                "SELECT id FROM reading_progress WHERE user_id = ? AND book_id = ?",
                [$userId, $bookId]
            );
            
            if ($existingProgress) {
                // Update existing
                $db->execute(
                    "UPDATE reading_progress 
                     SET current_page = ?, total_pages = ?, progress_percent = ?, last_read_at = CURRENT_TIMESTAMP
                     WHERE user_id = ? AND book_id = ?",
                    [$currentPage, $totalPages, $progress, $userId, $bookId]
                );
            } else {
                // Insert new
                $db->execute(
                    "INSERT INTO reading_progress (user_id, book_id, current_page, total_pages, progress_percent, last_read_at)
                     VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP)",
                    [$userId, $bookId, $currentPage, $totalPages, $progress]
                );
            }
            
            // Ensure book is in user_books (create if doesn't exist)
            $userBook = $db->fetchOne(
                "SELECT status FROM user_books WHERE user_id = ? AND book_id = ?",
                [$userId, $bookId]
            );
            
            if (!$userBook) {
                // Create user_books record if doesn't exist with status 'reading'
                error_log("Creating user_books record: UserID=$userId, BookID=$bookId, Status=reading");
                $db->execute(
                    "INSERT INTO user_books (user_id, book_id, status) 
                     VALUES (?, ?, 'reading')",
                    [$userId, $bookId]
                );
                // Re-fetch to get the record
                $userBook = $db->fetchOne(
                    "SELECT status FROM user_books WHERE user_id = ? AND book_id = ?",
                    [$userId, $bookId]
                );
                error_log("Created user_books record: " . json_encode($userBook));
            } else {
                error_log("User_books record exists: " . json_encode($userBook));
            }
            
            // Update status based on progress
            if ($progress >= 100) {
                // Book completed
                $db->execute(
                    "UPDATE user_books SET status = 'completed' 
                     WHERE user_id = ? AND book_id = ?",
                    [$userId, $bookId]
                );
                
                // Award coins for completing book (only if not already completed)
                if (!$userBook || $userBook['status'] !== 'completed') {
                    $baseCoins = 100;
                    $pageBonus = floor($totalPages / 10);
                    $totalCoins = $baseCoins + $pageBonus;
                    
                    $db->execute(
                        "UPDATE users SET coins = coins + ? WHERE id = ?",
                        [$totalCoins, $userId]
                    );
                    
                    // Log transaction
                    $db->execute(
                        "INSERT INTO coins_transactions (user_id, amount, reason, reference_id) 
                         VALUES (?, ?, 'book_completed', ?)",
                        [$userId, $totalCoins, $bookId]
                    );
                }
            } else {
                // progress < 100: Always update to 'reading' (unless already 'completed')
                // This ensures books are marked as 'reading' when user starts reading
                // Even at 0% progress, if we're tracking it, mark as reading
                error_log("Updating status to 'reading' for BookID=$bookId, UserID=$userId, Progress=$progress");
                $db->execute(
                    "UPDATE user_books 
                     SET status = CASE 
                         WHEN status = 'completed' THEN 'completed' 
                         ELSE 'reading' 
                     END
                     WHERE user_id = ? AND book_id = ?",
                    [$userId, $bookId]
                );
                // Verify update
                $updated = $db->fetchOne(
                    "SELECT status FROM user_books WHERE user_id = ? AND book_id = ?",
                    [$userId, $bookId]
                );
                error_log("Status updated to: " . json_encode($updated));
            }
            
            echo json_encode([
                'success' => true,
                'progress' => $progress,
                'current_page' => $currentPage,
                'total_pages' => $totalPages
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
    error_log("Progress API Error: " . $e->getMessage());
}
?>

