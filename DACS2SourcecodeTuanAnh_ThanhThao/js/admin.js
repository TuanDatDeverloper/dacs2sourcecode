/**
 * Admin JavaScript - BookOnline
 * Xử lý các chức năng admin
 */

class AdminHandler {
    /**
     * Lấy danh sách users
     */
    async getUsers(page = 1, filters = {}) {
        try {
            const params = new URLSearchParams({
                page: page,
                limit: 20,
                ...filters
            });
            
            const response = await fetch(`api/admin/users.php?${params}`);
            return await response.json();
        } catch (error) {
            console.error('Error getting users:', error);
            return { success: false, message: error.message };
        }
    }
    
    /**
     * Lấy thông tin user
     */
    async getUser(userId) {
        try {
            const response = await fetch('api/admin/users.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'get',
                    user_id: userId
                })
            });
            
            return await response.json();
        } catch (error) {
            console.error('Error getting user:', error);
            return { success: false, message: error.message };
        }
    }
    
    /**
     * Cập nhật user
     */
    async updateUser(userId, data) {
        try {
            const response = await fetch('api/admin/users.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    user_id: userId,
                    ...data
                })
            });
            
            return await response.json();
        } catch (error) {
            console.error('Error updating user:', error);
            return { success: false, message: error.message };
        }
    }
    
    /**
     * Xóa user
     */
    async deleteUser(userId) {
        try {
            const response = await fetch('api/admin/users.php', {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ user_id: userId })
            });
            
            return await response.json();
        } catch (error) {
            console.error('Error deleting user:', error);
            return { success: false, message: error.message };
        }
    }
    
    /**
     * Khóa/Mở khóa user
     */
    async toggleUserActive(userId, isActive) {
        try {
            const response = await fetch('api/admin/users.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: isActive ? 'unban' : 'ban',
                    user_id: userId
                })
            });
            
            return await response.json();
        } catch (error) {
            console.error('Error toggling user:', error);
            return { success: false, message: error.message };
        }
    }
    
    /**
     * Lấy admin stats
     */
    async getStats() {
        try {
            const response = await fetch('api/admin/stats.php');
            return await response.json();
        } catch (error) {
            console.error('Error getting stats:', error);
            return { success: false, message: error.message };
        }
    }
    
    /**
     * Lấy admin logs
     */
    async getLogs(limit = 50) {
        try {
            const response = await fetch(`api/admin/logs.php?limit=${limit}`);
            return await response.json();
        } catch (error) {
            console.error('Error getting logs:', error);
            return { success: false, message: error.message };
        }
    }
}

// Export globally
window.AdminHandler = new AdminHandler();

