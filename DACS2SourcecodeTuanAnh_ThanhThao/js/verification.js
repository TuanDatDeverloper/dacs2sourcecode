/**
 * Verification JavaScript - BookOnline
 * Xử lý email verification và password reset
 */

class VerificationHandler {
    /**
     * Gửi mã xác nhận email
     */
    async sendEmailVerification() {
        try {
            const response = await fetch('api/verification.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'send_email_verification'
                })
            });
            
            return await response.json();
        } catch (error) {
            console.error('Error sending verification:', error);
            return { success: false, message: error.message };
        }
    }
    
    /**
     * Xác minh mã email
     */
    async verifyEmail(code) {
        try {
            const response = await fetch('api/verification.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'verify_email',
                    code: code
                })
            });
            
            return await response.json();
        } catch (error) {
            console.error('Error verifying email:', error);
            return { success: false, message: error.message };
        }
    }
    
    /**
     * Gửi mã reset password
     */
    async sendPasswordReset(email) {
        try {
            const response = await fetch('api/verification.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'send_password_reset',
                    email: email
                })
            });
            
            return await response.json();
        } catch (error) {
            console.error('Error sending password reset:', error);
            return { success: false, message: error.message };
        }
    }
    
    /**
     * Đặt lại mật khẩu với mã
     */
    async resetPassword(email, code, newPassword) {
        try {
            const response = await fetch('api/verification.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    action: 'reset_password',
                    email: email,
                    code: code,
                    password: newPassword
                })
            });
            
            return await response.json();
        } catch (error) {
            console.error('Error resetting password:', error);
            return { success: false, message: error.message };
        }
    }
}

// Export globally
window.VerificationHandler = new VerificationHandler();

