<?php
/**
 * Database Class - BookOnline
 * PDO Database Connection và Helper Methods
 */

require_once __DIR__ . '/config.php';

class Database {
    private $conn;
    private static $instance = null;
    
    /**
     * Constructor - Tạo database connection
     */
    public function __construct() {
        try {
            if (DB_TYPE === 'sqlite') {
                // SQLite connection
                $this->conn = new PDO('sqlite:' . DB_PATH);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            } else {
                // MySQL connection
                $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME_MYSQL . ";charset=utf8mb4";
                $this->conn = new PDO($dsn, DB_USER, DB_PASS);
                $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            }
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            
            // Check if we're in API context (JSON response expected)
            $isApiContext = (
                strpos($_SERVER['REQUEST_URI'] ?? '', '/api/') !== false ||
                (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
            );
            
            // If API context, throw exception instead of die() with HTML
            if ($isApiContext) {
                throw new PDOException("Database connection failed: " . $e->getMessage());
            }
            
            // Hiển thị lỗi chi tiết trong development mode (only for non-API requests)
            if (ini_get('display_errors')) {
                $errorMsg = "Database connection failed.\n\n";
                $errorMsg .= "Error: " . $e->getMessage() . "\n\n";
                $errorMsg .= "Configuration:\n";
                $errorMsg .= "- Host: " . DB_HOST . "\n";
                $errorMsg .= "- Database: " . DB_NAME_MYSQL . "\n";
                $errorMsg .= "- User: " . DB_USER . "\n";
                $errorMsg .= "- Password: " . (empty(DB_PASS) ? '(empty)' : '***') . "\n\n";
                $errorMsg .= "Troubleshooting:\n";
                $errorMsg .= "1. Check if MySQL is running in XAMPP Control Panel\n";
                $errorMsg .= "2. Verify database credentials in includes/config.php\n";
                $errorMsg .= "3. Make sure database 'book_online' exists\n";
                $errorMsg .= "4. Run debug-database.php for detailed diagnostics\n";
                
                die("<pre style='background:#f8d7da;padding:15px;border-radius:5px;font-family:monospace;'>" . htmlspecialchars($errorMsg) . "</pre>");
            } else {
                die("Database connection failed. Please check your configuration.");
            }
        }
    }
    
    /**
     * Get singleton instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get PDO connection
     */
    public function getConnection() {
        return $this->conn;
    }
    
    /**
     * Execute prepared statement
     * @param string $sql SQL query với placeholders
     * @param array $params Parameters cho prepared statement
     * @return PDOStatement
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query failed: " . $e->getMessage() . " | SQL: " . $sql);
            throw $e;
        }
    }
    
    /**
     * Fetch all rows
     * @param string $sql SQL query
     * @param array $params Parameters
     * @return array
     */
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Fetch one row
     * @param string $sql SQL query
     * @param array $params Parameters
     * @return array|false
     */
    public function fetchOne($sql, $params = []) {
        $result = $this->query($sql, $params)->fetch(PDO::FETCH_ASSOC);
        return $result ? $result : false;
    }
    
    /**
     * Execute query (INSERT, UPDATE, DELETE)
     * @param string $sql SQL query
     * @param array $params Parameters
     * @return PDOStatement
     */
    public function execute($sql, $params = []) {
        return $this->query($sql, $params);
    }
    
    /**
     * Get last insert ID
     * @return string
     */
    public function lastInsertId() {
        return $this->conn->lastInsertId();
    }
    
    /**
     * Begin transaction
     */
    public function beginTransaction() {
        return $this->conn->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() {
        return $this->conn->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() {
        return $this->conn->rollBack();
    }
}

