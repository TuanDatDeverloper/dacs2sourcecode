/**
 * Google OAuth Authentication Handler
 * Xử lý đăng nhập bằng Google
 */

class GoogleAuth {
    constructor() {
        this.clientId = null;
        this.initialized = false;
        this.init();
    }
    
    async init() {
        try {
            // Lấy Google Client ID từ server
            const response = await fetch('api/google-auth.php');
            const data = await response.json();
            
            if (data.enabled && data.client_id) {
                this.clientId = data.client_id;
                this.initializeGoogleSignIn();
            } else {
                console.warn('Google OAuth chưa được cấu hình');
                this.disableGoogleButton();
            }
        } catch (error) {
            console.error('Error initializing Google Auth:', error);
            this.disableGoogleButton();
        }
    }
    
    initializeGoogleSignIn() {
        if (typeof google === 'undefined') {
            console.error('Google Identity Services library not loaded');
            this.disableGoogleButton();
            return;
        }
        
        google.accounts.id.initialize({
            client_id: this.clientId,
            callback: this.handleCredentialResponse.bind(this),
            auto_select: false,
            cancel_on_tap_outside: true
        });
        
        // Render button
        const googleBtn = document.getElementById('googleLoginBtn');
        if (googleBtn) {
            google.accounts.id.renderButton(
                googleBtn,
                {
                    theme: 'outline',
                    size: 'large',
                    width: '100%',
                    text: 'signin_with',
                    locale: 'vi'
                }
            );
            
            // Also show One Tap prompt (optional)
            google.accounts.id.prompt((notification) => {
                if (notification.isNotDisplayed() || notification.isSkippedMoment()) {
                    // One Tap không hiển thị, không sao
                }
            });
        }
        
        this.initialized = true;
    }
    
    async handleCredentialResponse(response) {
        const idToken = response.credential;
        
        if (!idToken) {
            this.showMessage('Không thể lấy thông tin từ Google', 'error');
            return;
        }
        
        // Disable button
        const googleBtn = document.getElementById('googleLoginBtn');
        if (googleBtn) {
            googleBtn.disabled = true;
            googleBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Đang xử lý...';
        }
        
        try {
            // Verify token với server
            // Send as JSON
            const result = await fetch('api/google-auth.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'verify',
                    id_token: idToken
                })
            });
            
            const data = await result.json();
            
            if (data.success && data.user) {
                // Set user data
                if (typeof window.Auth !== 'undefined' && window.Auth.setUser) {
                    window.Auth.setUser(data.user);
                }
                
                this.showMessage('Đăng nhập thành công! Đang chuyển hướng...', 'success');
                
                // Redirect - Nếu là admin thì chuyển đến admin panel
                setTimeout(() => {
                    const urlParams = new URLSearchParams(window.location.search);
                    const redirect = urlParams.get('redirect');
                    
                    if (redirect) {
                        window.location.href = redirect;
                    } else {
                        // Check if user is admin
                        const isAdmin = data.user && data.user.is_admin == 1;
                        if (isAdmin) {
                            window.location.href = 'admin/index.php';
                        } else {
                            window.location.href = 'dashboard.php';
                        }
                    }
                }, 500);
            } else {
                this.showMessage(data.message || 'Đăng nhập thất bại', 'error');
                if (googleBtn) {
                    googleBtn.disabled = false;
                    googleBtn.innerHTML = '<i class="fab fa-google text-red-500 text-lg mr-2"></i><span>Đăng nhập với Google</span>';
                }
            }
        } catch (error) {
            console.error('Google login error:', error);
            this.showMessage('Lỗi: ' + error.message, 'error');
            if (googleBtn) {
                googleBtn.disabled = false;
                googleBtn.innerHTML = '<i class="fab fa-google text-red-500 text-lg mr-2"></i><span>Đăng nhập với Google</span>';
            }
        }
    }
    
    disableGoogleButton() {
        const googleBtn = document.getElementById('googleLoginBtn');
        if (googleBtn) {
            googleBtn.style.display = 'none';
        }
    }
    
    showMessage(message, type) {
        const messageContainer = document.getElementById('messageContainer');
        if (!messageContainer) return;
        
        const bgColor = type === 'success' 
            ? 'bg-green-100 border-green-400 text-green-700' 
            : 'bg-red-100 border-red-400 text-red-700';
        
        messageContainer.innerHTML = `
            <div class="border ${bgColor} px-4 py-3 rounded relative" role="alert">
                <span class="block sm:inline">${message}</span>
            </div>
        `;
        
        // Auto hide after 5 seconds
        setTimeout(() => {
            messageContainer.innerHTML = '';
        }, 5000);
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.GoogleAuth = new GoogleAuth();
    });
} else {
    window.GoogleAuth = new GoogleAuth();
}

