<?php
/**
 * Statistics API - BookOnline
 * Calculate and return statistics for dashboard
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';

$auth = new Auth();
$auth->requireLogin();

$db = new Database();
$userId = $_SESSION['user_id'];

try {
    // Get user info
    $user = $db->fetchOne("SELECT coins FROM users WHERE id = ?", [$userId]);
    
    // Total books
    $totalBooks = $db->fetchOne(
        "SELECT COUNT(*) as count FROM user_books WHERE user_id = ?",
        [$userId]
    )['count'] ?? 0;
    
    // Completed books
    $completedBooks = $db->fetchOne(
        "SELECT COUNT(*) as count FROM user_books WHERE user_id = ? AND status = 'completed'",
        [$userId]
    )['count'] ?? 0;
    
    // Reading books
    $readingBooks = $db->fetchOne(
        "SELECT COUNT(*) as count FROM user_books WHERE user_id = ? AND status = 'reading'",
        [$userId]
    )['count'] ?? 0;
    
    // Want to read
    $wantToReadBooks = $db->fetchOne(
        "SELECT COUNT(*) as count FROM user_books WHERE user_id = ? AND status = 'want_to_read'",
        [$userId]
    )['count'] ?? 0;
    
    // Total pages read
    $totalPagesRead = $db->fetchOne(
        "SELECT SUM(current_page) as total FROM reading_progress WHERE user_id = ?",
        [$userId]
    )['total'] ?? 0;
    
    // Total pages (from all books)
    $totalPages = $db->fetchOne(
        "SELECT SUM(total_pages) as total FROM reading_progress WHERE user_id = ?",
        [$userId]
    )['total'] ?? 0;
    
    // Average progress
    $avgProgress = $db->fetchOne(
        "SELECT AVG(progress_percent) as avg FROM reading_progress WHERE user_id = ?",
        [$userId]
    )['avg'] ?? 0;
    
    // Books by month (last 6 months) - using added_at or last_read_at from reading_progress
    $booksByMonth = $db->fetchAll(
        "SELECT 
            DATE_FORMAT(COALESCE(rp.last_read_at, ub.added_at), '%Y-%m') as month,
            COUNT(*) as count
         FROM user_books ub
         LEFT JOIN reading_progress rp ON ub.user_id = rp.user_id AND ub.book_id = rp.book_id
         WHERE ub.user_id = ? AND ub.status = 'completed'
         AND COALESCE(rp.last_read_at, ub.added_at) >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
         GROUP BY DATE_FORMAT(COALESCE(rp.last_read_at, ub.added_at), '%Y-%m')
         ORDER BY month ASC",
        [$userId]
    );
    
    // Books by category
    $booksByCategory = $db->fetchAll(
        "SELECT 
            b.categories as category,
            COUNT(*) as count
         FROM user_books ub
         JOIN books b ON ub.book_id = b.id
         WHERE ub.user_id = ?
         GROUP BY b.categories
         ORDER BY count DESC
         LIMIT 10",
        [$userId]
    );
    
    // Coins history (last 30 days)
    $coinsHistory = $db->fetchAll(
        "SELECT 
            DATE(transaction_date) as date,
            SUM(amount) as daily_coins
         FROM coins_transactions
         WHERE user_id = ? AND transaction_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
         GROUP BY DATE(transaction_date)
         ORDER BY date ASC",
        [$userId]
    );
    
    // Quiz attempts
    $quizAttempts = $db->fetchOne(
        "SELECT COUNT(*) as count FROM quiz_attempts WHERE user_id = ?",
        [$userId]
    )['count'] ?? 0;
    
    // Average quiz score (score is already percentage)
    $avgQuizScore = $db->fetchOne(
        "SELECT AVG(score) as avg FROM quiz_attempts WHERE user_id = ?",
        [$userId]
    )['avg'] ?? 0;
    
    // Total coins earned
    $totalCoinsEarned = $db->fetchOne(
        "SELECT SUM(amount) as total FROM coins_transactions WHERE user_id = ? AND amount > 0",
        [$userId]
    )['total'] ?? 0;
    
    // Total coins spent
    $totalCoinsSpent = abs($db->fetchOne(
        "SELECT SUM(amount) as total FROM coins_transactions WHERE user_id = ? AND amount < 0",
        [$userId]
    )['total'] ?? 0);
    
    // Reading streak (days)
    $readingStreak = calculateReadingStreak($db, $userId);
    
    // Favorite category
    $favoriteCategory = $db->fetchOne(
        "SELECT b.categories as category, COUNT(*) as count
         FROM user_books ub
         JOIN books b ON ub.book_id = b.id
         WHERE ub.user_id = ?
         GROUP BY b.categories
         ORDER BY count DESC
         LIMIT 1",
        [$userId]
    );
    
    $stats = [
        'user' => [
            'coins' => $user['coins'] ?? 0
        ],
        'books' => [
            'total' => (int)$totalBooks,
            'completed' => (int)$completedBooks,
            'reading' => (int)$readingBooks,
            'want_to_read' => (int)$wantToReadBooks
        ],
        'reading' => [
            'total_pages_read' => (int)$totalPagesRead,
            'total_pages' => (int)$totalPages,
            'average_progress' => round((float)$avgProgress, 2),
            'reading_streak' => $readingStreak
        ],
        'quizzes' => [
            'total_attempts' => (int)$quizAttempts,
            'average_score' => round((float)$avgQuizScore, 2)
        ],
        'coins' => [
            'current' => (int)($user['coins'] ?? 0),
            'total_earned' => (int)$totalCoinsEarned,
            'total_spent' => (int)$totalCoinsSpent
        ],
        'charts' => [
            'books_by_month' => $booksByMonth,
            'books_by_category' => $booksByCategory,
            'coins_history' => $coinsHistory
        ],
        'favorites' => [
            'category' => $favoriteCategory['category'] ?? 'N/A'
        ]
    ];
    
    echo json_encode($stats);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
    error_log("Stats API Error: " . $e->getMessage());
}

/**
 * Calculate reading streak (consecutive days with reading activity)
 */
function calculateReadingStreak($db, $userId) {
    // Get last read dates
    $lastReadDates = $db->fetchAll(
        "SELECT DISTINCT DATE(last_read_at) as date
         FROM reading_progress
         WHERE user_id = ? AND last_read_at IS NOT NULL
         ORDER BY date DESC
         LIMIT 30",
        [$userId]
    );
    
    if (empty($lastReadDates)) {
        return 0;
    }
    
    $streak = 0;
    $today = date('Y-m-d');
    $currentDate = $today;
    
    foreach ($lastReadDates as $row) {
        $readDate = $row['date'];
        
        if ($readDate === $currentDate) {
            $streak++;
            // Move to previous day
            $currentDate = date('Y-m-d', strtotime($currentDate . ' -1 day'));
        } else {
            break;
        }
    }
    
    return $streak;
}
?>

