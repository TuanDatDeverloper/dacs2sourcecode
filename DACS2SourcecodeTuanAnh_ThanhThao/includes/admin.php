<?php
/**
 * Admin Class - BookOnline
 * Xử lý quản lý admin và users
 */

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';

class Admin {
    private $db;
    private $auth;
    
    public function __construct() {
        $this->db = new Database();
        $this->auth = new Auth();
    }
    
    /**
     * Kiểm tra user có phải admin không
     * @param int|null $userId User ID (null = current user)
     * @return bool
     */
    public function isAdmin($userId = null) {
        if ($userId === null) {
            if (!$this->auth->isLoggedIn()) {
                return false;
            }
            $userId = $this->auth->getUserId();
        }
        
        if (!$userId) {
            return false;
        }
        
        $user = $this->db->fetchOne(
            "SELECT is_admin FROM users WHERE id = ?",
            [$userId]
        );
        
        if (!$user) {
            return false;
        }
        
        // Check if is_admin is 1 (can be int 1, string '1', or boolean true)
        $isAdmin = $user['is_admin'];
        
        // Debug log
        error_log("Admin check - User ID: $userId, is_admin value: " . var_export($isAdmin, true) . ", type: " . gettype($isAdmin));
        
        return (
            $isAdmin == 1 || 
            $isAdmin === '1' || 
            $isAdmin === true ||
            (int)$isAdmin === 1 ||
            $isAdmin === 'true'
        );
    }
    
    /**
     * Yêu cầu admin (throw exception nếu không phải admin)
     */
    public function requireAdmin() {
        if (!$this->isAdmin()) {
            throw new Exception('Chỉ quản trị viên mới có quyền truy cập');
        }
    }
    
    /**
     * Lấy danh sách users với pagination và filter
     * @param array $options ['page' => int, 'limit' => int, 'search' => string, 'filter' => array]
     * @return array ['users' => array, 'total' => int, 'page' => int, 'limit' => int]
     */
    public function getUsers($options = []) {
        $page = $options['page'] ?? 1;
        $limit = $options['limit'] ?? 20;
        $search = $options['search'] ?? '';
        $filters = $options['filter'] ?? [];
        
        $offset = ($page - 1) * $limit;
        
        // Build WHERE clause
        $where = [];
        $params = [];
        
        if (!empty($search)) {
            $where[] = "(email LIKE ? OR full_name LIKE ? OR username LIKE ?)";
            $searchParam = "%{$search}%";
            $params[] = $searchParam;
            $params[] = $searchParam;
            $params[] = $searchParam;
        }
        
        if (isset($filters['email_verified'])) {
            $where[] = "email_verified = ?";
            $params[] = $filters['email_verified'] ? 1 : 0;
        }
        
        if (isset($filters['is_admin'])) {
            $where[] = "is_admin = ?";
            $params[] = $filters['is_admin'] ? 1 : 0;
        }
        
        if (isset($filters['is_active'])) {
            $where[] = "is_active = ?";
            $params[] = $filters['is_active'] ? 1 : 0;
        }
        
        $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
        
        // Get total count
        $total = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM users {$whereClause}",
            $params
        )['count'];
        
        // Get users
        // Note: LIMIT and OFFSET cannot use placeholders in MySQL/MariaDB, must use direct values
        $limit = (int)$limit;
        $offset = (int)$offset;
        $users = $this->db->fetchAll(
            "SELECT id, email, username, full_name, coins, email_verified, is_admin, is_active, created_at, last_login
             FROM users {$whereClause}
             ORDER BY created_at DESC
             LIMIT {$limit} OFFSET {$offset}",
            $params
        );
        
        // Remove password_hash from results
        foreach ($users as &$user) {
            unset($user['password_hash']);
        }
        
        return [
            'users' => $users,
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'total_pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Lấy thông tin user theo ID
     * @param int $userId
     * @return array|null
     */
    public function getUser($userId) {
        $user = $this->db->fetchOne(
            "SELECT id, email, username, full_name, coins, email_verified, is_admin, is_active, created_at, last_login, google_id, oauth_provider
             FROM users WHERE id = ?",
            [$userId]
        );
        
        if ($user) {
            unset($user['password_hash']);
        }
        
        return $user;
    }
    
    /**
     * Cập nhật thông tin user
     * @param int $userId
     * @param array $data ['email', 'username', 'full_name', 'coins', 'email_verified', 'is_admin', 'is_active']
     * @return array ['success' => bool, 'message' => string]
     */
    public function updateUser($userId, $data) {
        // Validate
        $allowedFields = ['email', 'username', 'full_name', 'coins', 'email_verified', 'is_admin', 'is_active'];
        $updateFields = [];
        $params = [];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateFields[] = "{$field} = ?";
                $params[] = $data[$field];
            }
        }
        
        if (empty($updateFields)) {
            return ['success' => false, 'message' => 'Không có dữ liệu để cập nhật'];
        }
        
        // Check email unique (if updating email)
        if (isset($data['email'])) {
            $existing = $this->db->fetchOne(
                "SELECT id FROM users WHERE email = ? AND id != ?",
                [$data['email'], $userId]
            );
            
            if ($existing) {
                return ['success' => false, 'message' => 'Email đã được sử dụng'];
            }
        }
        
        // Check username unique (if updating username)
        if (isset($data['username']) && !empty($data['username'])) {
            $existing = $this->db->fetchOne(
                "SELECT id FROM users WHERE username = ? AND id != ?",
                [$data['username'], $userId]
            );
            
            if ($existing) {
                return ['success' => false, 'message' => 'Username đã được sử dụng'];
            }
        }
        
        // Update
        $params[] = $userId;
        $this->db->execute(
            "UPDATE users SET " . implode(", ", $updateFields) . " WHERE id = ?",
            $params
        );
        
        // Log action
        $this->logAction('update_user', 'user', $userId, json_encode($data));
        
        return ['success' => true, 'message' => 'Cập nhật thành công'];
    }
    
    /**
     * Xóa user
     * @param int $userId
     * @return array ['success' => bool, 'message' => string]
     */
    public function deleteUser($userId) {
        // Không cho xóa chính mình
        if ($userId == $this->auth->getUserId()) {
            return ['success' => false, 'message' => 'Không thể xóa chính mình'];
        }
        
        // Check user exists
        $user = $this->getUser($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'User không tồn tại'];
        }
        
        // Delete user (cascade sẽ xóa các bảng liên quan)
        $this->db->execute("DELETE FROM users WHERE id = ?", [$userId]);
        
        // Log action
        $this->logAction('delete_user', 'user', $userId, "Deleted user: {$user['email']}");
        
        return ['success' => true, 'message' => 'Đã xóa user thành công'];
    }
    
    /**
     * Khóa/Mở khóa user
     * @param int $userId
     * @param bool $isActive
     * @return array ['success' => bool, 'message' => string]
     */
    public function toggleUserActive($userId, $isActive) {
        // Không cho khóa chính mình
        if ($userId == $this->auth->getUserId() && !$isActive) {
            return ['success' => false, 'message' => 'Không thể khóa chính mình'];
        }
        
        $this->db->execute(
            "UPDATE users SET is_active = ? WHERE id = ?",
            [$isActive ? 1 : 0, $userId]
        );
        
        $action = $isActive ? 'unban_user' : 'ban_user';
        $this->logAction($action, 'user', $userId);
        
        return [
            'success' => true,
            'message' => $isActive ? 'Đã mở khóa user' : 'Đã khóa user'
        ];
    }
    
    /**
     * Lấy thống kê admin
     * @return array
     */
    public function getStats() {
        $stats = [];
        
        // Total users
        $stats['total_users'] = $this->db->fetchOne("SELECT COUNT(*) as count FROM users")['count'];
        
        // New users today
        $stats['new_users_today'] = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()"
        )['count'];
        
        // Unverified users
        $stats['unverified_users'] = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM users WHERE email_verified = 0"
        )['count'];
        
        // Banned users
        $stats['banned_users'] = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM users WHERE is_active = 0"
        )['count'];
        
        // Admin users
        $stats['admin_users'] = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM users WHERE is_admin = 1"
        )['count'];
        
        // Active users (logged in last 7 days)
        $stats['active_users_7d'] = $this->db->fetchOne(
            "SELECT COUNT(*) as count FROM users WHERE last_login >= DATE_SUB(NOW(), INTERVAL 7 DAY)"
        )['count'];
        
        return $stats;
    }
    
    /**
     * Lấy admin logs
     * @param int $limit
     * @return array
     */
    public function getLogs($limit = 50) {
        return $this->db->fetchAll(
            "SELECT al.*, u.email as admin_email, u.full_name as admin_name
             FROM admin_logs al
             LEFT JOIN users u ON al.admin_id = u.id
             ORDER BY al.created_at DESC
             LIMIT ?",
            [$limit]
        );
    }
    
    /**
     * Log admin action
     * @param string $action
     * @param string $targetType
     * @param int|null $targetId
     * @param string|null $details
     */
    private function logAction($action, $targetType = null, $targetId = null, $details = null) {
        try {
            $adminId = $this->auth->getUserId();
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? null;
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            
            $this->db->execute(
                "INSERT INTO admin_logs (admin_id, action, target_type, target_id, details, ip_address, user_agent)
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$adminId, $action, $targetType, $targetId, $details, $ipAddress, $userAgent]
            );
        } catch (Exception $e) {
            // Ignore logging errors
            error_log("Admin log error: " . $e->getMessage());
        }
    }
}
?>

