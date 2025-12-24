// ============================================
// API CLIENT - BookOnline
// Wrapper cho tất cả PHP API calls
// ============================================

// API Base URL - Use relative path
const API_BASE = 'api';

// Helper to get full API URL
function getApiUrl(endpoint) {
    // Remove leading slash if present
    endpoint = endpoint.replace(/^\//, '');
    
    // If endpoint already includes 'api/', use as is
    if (endpoint.startsWith('api/')) {
        return endpoint;
    }
    
    // Otherwise, prepend API_BASE
    return API_BASE + '/' + endpoint;
}

class APIClient {
    /**
     * Generic request method
     */
    async request(endpoint, options = {}) {
        // If endpoint is already a full URL or starts with http, use as is
        if (endpoint.startsWith('http://') || endpoint.startsWith('https://')) {
            var url = endpoint;
        } else {
            // Use getApiUrl helper
            var url = getApiUrl(endpoint);
        }
        const config = {
            method: options.method || 'GET',
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        };
        
        // Handle body
        if (options.body && typeof options.body === 'object') {
            config.body = JSON.stringify(options.body);
        }
        
        try {
            const response = await fetch(url, config);
            
            // Handle non-JSON responses
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                if (response.ok) {
                    return { success: true };
                } else {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }
            }
            
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || `HTTP ${response.status}: ${response.statusText}`);
            }
            
            return data;
        } catch (error) {
            console.error('API Error:', error);
            throw error;
        }
    }
    
    // ============================================
    // AUTHENTICATION METHODS
    // ============================================
    
    async login(email, password) {
        try {
            const formData = new FormData();
            formData.append('action', 'login');
            formData.append('email', email);
            formData.append('password', password);
            
            const response = await fetch(getApiUrl('auth.php'), {
                method: 'POST',
                body: formData
            });
            
            // Check content type
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Non-JSON response:', text);
                throw new Error('Server trả về dữ liệu không hợp lệ. Vui lòng thử lại.');
            }
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Login API error:', error);
            if (error.message.includes('Unexpected token')) {
                throw new Error('Lỗi kết nối server. Vui lòng kiểm tra lại.');
            }
            throw error;
        }
    }
    
    async register(email, password, fullName = '', username = '') {
        try {
            const formData = new FormData();
            formData.append('action', 'register');
            formData.append('email', email);
            formData.append('password', password);
            formData.append('full_name', fullName);
            if (username) {
                formData.append('username', username);
            }
            
            const response = await fetch(getApiUrl('auth.php'), {
                method: 'POST',
                body: formData
            });
            
            // Check content type
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Non-JSON response:', text);
                throw new Error('Server trả về dữ liệu không hợp lệ. Vui lòng thử lại.');
            }
            
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Register API error:', error);
            if (error.message.includes('Unexpected token')) {
                throw new Error('Lỗi kết nối server. Vui lòng kiểm tra lại.');
            }
            throw error;
        }
    }
    
    async logout() {
        try {
            const response = await fetch(getApiUrl('auth.php?action=logout'), {
                method: 'POST'
            });
            return await response.json();
        } catch (error) {
            // Even if error, consider logout successful
            return { success: true };
        }
    }
    
    async checkAuth() {
        return await this.request(getApiUrl('auth.php?check=1'));
    }
    
    // ============================================
    // BOOKS METHODS
    // ============================================
    
    async getBooks(status = 'all', filters = {}) {
        let url = `books.php?status=${status}`;
        
        if (filters.search) {
            url += `&search=${encodeURIComponent(filters.search)}`;
        }
        if (filters.category) {
            url += `&category=${encodeURIComponent(filters.category)}`;
        }
        
        return await this.request(url);
    }
    
    async getBook(id) {
        return await this.request(`books.php?id=${id}`);
    }
    
    async addBook(bookData) {
        return await this.request('books.php', {
            method: 'POST',
            body: bookData
        });
    }
    
    async updateBook(bookId, updates) {
        return await this.request('books.php', {
            method: 'PUT',
            body: { book_id: bookId, ...updates }
        });
    }
    
    async deleteBook(bookId) {
        return await this.request(`books.php?id=${bookId}`, {
            method: 'DELETE'
        });
    }
    
    // ============================================
    // PROGRESS METHODS
    // ============================================
    
    async getProgress(bookId) {
        return await this.request(`progress.php?book_id=${bookId}`);
    }
    
    async updateProgress(bookId, progress, currentPage = null) {
        const body = {
            book_id: bookId,
            progress: progress
        };
        
        if (currentPage !== null) {
            body.current_page = currentPage;
        }
        
        return await this.request('progress.php', {
            method: 'PUT',
            body: body
        });
    }
    
    // ============================================
    // QUIZ METHODS
    // ============================================
    
    async generateQuiz(bookId, numQuestions = 10) {
        return await this.request('quiz.php', {
            method: 'POST',
            body: {
                action: 'generate',
                book_id: bookId,
                num_questions: numQuestions
            }
        });
    }
    
    async submitQuiz(quizId, answers) {
        return await this.request('quiz.php', {
            method: 'POST',
            body: {
                action: 'submit',
                quiz_id: quizId,
                session_id: quizId, // Support both
                answers: answers
            }
        });
    }
    
    async getQuizResults(quizId) {
        return await this.request(`quiz.php?id=${quizId}`);
    }
    
    // ============================================
    // SHOP METHODS
    // ============================================
    
    async getShopItems(category = 'all') {
        let url = 'shop.php';
        if (category !== 'all') {
            url += `?category=${category}`;
        }
        return await this.request(url);
    }
    
    async purchaseItem(itemId) {
        return await this.request('shop.php', {
            method: 'POST',
            body: {
                action: 'purchase',
                item_id: itemId
            }
        });
    }
    
    // ============================================
    // INVENTORY METHODS
    // ============================================
    
    async getInventory() {
        return await this.request('inventory.php');
    }
    
    async equipItem(itemId) {
        return await this.request('inventory.php', {
            method: 'PUT',
            body: {
                action: 'equip',
                item_id: itemId
            }
        });
    }
    
    // ============================================
    // BOOKSHELF METHODS
    // ============================================
    
    async getBookshelfLayout() {
        return await this.request('bookshelf.php');
    }
    
    // ============================================
    // EMAIL METHODS
    // ============================================
    
    async sendEmailReminder() {
        return await this.request('email.php', {
            method: 'POST',
            body: {
                action: 'send_reminder'
            }
        });
    }
    
    async sendCustomEmail(to, subject, message) {
        return await this.request('email.php', {
            method: 'POST',
            body: {
                action: 'send_custom',
                to: to,
                subject: subject,
                message: message
            }
        });
    }
    
    async sendBulkReminders() {
        return await this.request('email.php', {
            method: 'POST',
            body: {
                action: 'send_bulk_reminders'
            }
        });
    }
    
    async getEmailLogs(limit = 50) {
        return await this.request(`email.php?limit=${limit}`);
    }
    
    async saveBookshelfLayout(layoutData) {
        return await this.request('bookshelf.php', {
            method: 'PUT',
            body: {
                layout_data: layoutData
            }
        });
    }
    
    // ============================================
    // STATS METHODS
    // ============================================
    
    async getStats() {
        return await this.request('stats.php');
    }
}

// Export globally
window.APIClient = new APIClient();

// Log for debugging
console.log('APIClient initialized');

