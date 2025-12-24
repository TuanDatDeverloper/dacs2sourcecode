// ============================================
// AUTHENTICATION UTILITY - PHP VERSION
// ============================================

const AUTH_KEY = 'bookOnline_user';

// Cache for auth status (to avoid too many API calls)
let authCache = {
    loggedIn: null,
    user: null,
    lastCheck: 0
};
const CACHE_DURATION = 5000; // 5 seconds

/**
 * Check if user is logged in (from PHP session)
 * @returns {Promise<boolean>}
 */
async function isLoggedIn() {
    // Check cache first
    const now = Date.now();
    if (authCache.loggedIn !== null && (now - authCache.lastCheck) < CACHE_DURATION) {
        return authCache.loggedIn;
    }
    
    try {
        if (typeof window.APIClient === 'undefined') {
            // Fallback to localStorage if API client not loaded
            const user = localStorage.getItem(AUTH_KEY);
            return !!user;
        }
        
        const result = await window.APIClient.checkAuth();
        authCache.loggedIn = result.logged_in || false;
        authCache.user = result.user || null;
        authCache.lastCheck = now;
        
        // Sync with localStorage for compatibility
        if (authCache.loggedIn && authCache.user) {
            localStorage.setItem(AUTH_KEY, JSON.stringify(authCache.user));
        } else {
            localStorage.removeItem(AUTH_KEY);
        }
        
        return authCache.loggedIn;
    } catch (error) {
        console.error('Error checking auth:', error);
        // Fallback to localStorage
        const user = localStorage.getItem(AUTH_KEY);
        return !!user;
    }
}

/**
 * Get current user (from PHP session)
 * @returns {Promise<Object|null>}
 */
async function getCurrentUser() {
    // Check cache first
    const now = Date.now();
    if (authCache.user !== null && (now - authCache.lastCheck) < CACHE_DURATION) {
        return authCache.user;
    }
    
    try {
        if (typeof window.APIClient === 'undefined') {
            // Fallback to localStorage
            const user = localStorage.getItem(AUTH_KEY);
            return user ? JSON.parse(user) : null;
        }
        
        const result = await window.APIClient.checkAuth();
        authCache.loggedIn = result.logged_in || false;
        authCache.user = result.user || null;
        authCache.lastCheck = now;
        
        // Sync with localStorage
        if (authCache.user) {
            localStorage.setItem(AUTH_KEY, JSON.stringify(authCache.user));
        } else {
            localStorage.removeItem(AUTH_KEY);
        }
        
        return authCache.user;
    } catch (error) {
        console.error('Error getting user:', error);
        // Fallback to localStorage
        const user = localStorage.getItem(AUTH_KEY);
        return user ? JSON.parse(user) : null;
    }
}

/**
 * Set user as logged in (sync with localStorage)
 * @param {Object} userData
 */
function setUser(userData) {
    if (typeof window === 'undefined') return;
    try {
        localStorage.setItem(AUTH_KEY, JSON.stringify(userData));
        authCache.user = userData;
        authCache.loggedIn = true;
        authCache.lastCheck = Date.now();
        // Dispatch event for other scripts
        window.dispatchEvent(new CustomEvent('authChange', { detail: { loggedIn: true, user: userData } }));
    } catch (error) {
        console.error('Error setting user:', error);
    }
}

/**
 * Login with email and password (calls PHP API)
 * @param {string} email
 * @param {string} password
 * @returns {Promise<Object>} {success: boolean, message: string, user: Object|null}
 */
async function login(email, password) {
    try {
        if (typeof window.APIClient === 'undefined') {
            // Fallback to demo user if API client not loaded
            if (email === 'nguoidung1@bookonline.com' && password === '1234') {
                const userData = {
                    email: 'nguoidung1@bookonline.com',
                    name: 'Người Dùng 1',
                    coins: 500
                };
                setUser(userData);
                return { success: true, message: 'Đăng nhập thành công', user: userData };
            }
            return { success: false, message: 'Email hoặc mật khẩu không đúng' };
        }
        
        const result = await window.APIClient.login(email, password);
        
        if (result.success && result.user) {
            setUser(result.user);
            // Clear cache to force refresh
            authCache.loggedIn = true;
            authCache.user = result.user;
            authCache.lastCheck = Date.now();
        }
        
        return result;
    } catch (error) {
        console.error('Login error:', error);
        return { success: false, message: 'Lỗi đăng nhập: ' + error.message };
    }
}

/**
 * Logout user (calls PHP API)
 * @returns {Promise<void>}
 */
async function logout() {
    if (typeof window === 'undefined') return;
    
    try {
        if (typeof window.APIClient !== 'undefined') {
            await window.APIClient.logout();
        }
    } catch (error) {
        console.error('Logout error:', error);
    }
    
    // Clear local storage and cache
    localStorage.removeItem(AUTH_KEY);
    authCache.loggedIn = false;
    authCache.user = null;
    authCache.lastCheck = 0;
    
    window.dispatchEvent(new CustomEvent('authChange', { detail: { loggedIn: false, user: null } }));
}

/**
 * Register new user (calls PHP API)
 * @param {string} email
 * @param {string} password
 * @param {string} fullName
 * @returns {Promise<Object>}
 */
async function register(email, password, fullName = '') {
    try {
        if (typeof window.APIClient === 'undefined') {
            return { success: false, message: 'API client not loaded' };
        }
        
        const result = await window.APIClient.register(email, password, fullName);
        
        if (result.success && result.user) {
            setUser(result.user);
        }
        
        return result;
    } catch (error) {
        console.error('Register error:', error);
        return { success: false, message: 'Lỗi đăng ký: ' + error.message };
    }
}

// Export for non-module usage (immediate initialization)
if (typeof window !== 'undefined') {
    window.Auth = {
        isLoggedIn,
        getCurrentUser,
        setUser,
        logout,
        login,
        register
    };
}

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        isLoggedIn,
        getCurrentUser,
        setUser,
        logout,
        login,
        register
    };
}
