// ============================================
// NAVIGATION MANAGEMENT
// ============================================

/**
 * Update navigation based on authentication status
 * This function should be called on page load and when auth state changes
 */
function updateNavigation() {
    setTimeout(() => {
        const isLoggedIn = window.Auth && window.Auth.isLoggedIn();
        const user = window.Auth ? window.Auth.getCurrentUser() : null;
        
        // Navigation links
        const navHistoryLink = document.getElementById('nav-history-link');
        const navHistoryLinkMobile = document.getElementById('nav-history-link-mobile');
        const navDashboardLink = document.getElementById('nav-dashboard-link');
        const navDashboardLinkMobile = document.getElementById('nav-dashboard-link-mobile');
        const navShopLink = document.getElementById('nav-shop-link');
        const navShopLinkMobile = document.getElementById('nav-shop-link-mobile');
        const navQuizLink = document.getElementById('nav-quiz-link');
        const navQuizLinkMobile = document.getElementById('nav-quiz-link-mobile');
        const navBookshelfLink = document.getElementById('nav-bookshelf-link');
        const navBookshelfLinkMobile = document.getElementById('nav-bookshelf-link-mobile');
        const navInventoryLink = document.getElementById('nav-inventory-link');
        const navInventoryLinkMobile = document.getElementById('nav-inventory-link-mobile');
        
        // Auth buttons containers
        const authButtons = document.getElementById('auth-buttons');
        const authButtonsMobile = document.getElementById('auth-buttons-mobile');
        const userInfo = document.getElementById('user-info');
        const userInfoMobile = document.getElementById('user-info-mobile');
        
        // Update history link text - always show "Kho sách"
        const navLibraryContainer = document.getElementById('nav-library-container');
        if (navHistoryLink) {
            const linkText = navHistoryLink.querySelector('span');
            if (linkText) {
                linkText.textContent = 'Kho sách';
            } else {
                navHistoryLink.textContent = 'Kho sách';
            }
        }
        if (navHistoryLinkMobile) {
            navHistoryLinkMobile.textContent = 'Kho sách';
        }
        
        // Show/hide dropdown based on login status
        if (navLibraryContainer) {
            if (isLoggedIn) {
                navLibraryContainer.style.display = 'block';
            } else {
                navLibraryContainer.style.display = 'block'; // Always show, but dropdown items may differ
            }
        }
        
        if (isLoggedIn && user) {
            // Show user info, hide auth buttons
            if (authButtons) authButtons.style.display = 'none';
            if (authButtonsMobile) authButtonsMobile.style.display = 'none';
            if (userInfo) userInfo.style.display = 'flex';
            if (userInfoMobile) userInfoMobile.style.display = 'block';
            
            // Update user info
            const userName = user.name || user.username || user.email || 'Người dùng';
            const userCoins = user.coins || 0;
            const userInitials = userName.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
            
            if (document.getElementById('user-name')) {
                document.getElementById('user-name').textContent = userName;
            }
            if (document.getElementById('user-name-mobile')) {
                document.getElementById('user-name-mobile').textContent = userName;
            }
            if (document.getElementById('user-avatar')) {
                document.getElementById('user-avatar').textContent = userInitials;
            }
            if (document.getElementById('user-avatar-mobile')) {
                document.getElementById('user-avatar-mobile').textContent = userInitials;
            }
            if (document.getElementById('user-coins-display')) {
                document.getElementById('user-coins-display').textContent = `${userCoins} Coins`;
            }
            if (document.getElementById('user-coins-display-mobile')) {
                document.getElementById('user-coins-display-mobile').textContent = `${userCoins} Coins`;
            }
            
            // Show protected links
            if (navDashboardLink) navDashboardLink.style.display = 'block';
            if (navDashboardLinkMobile) navDashboardLinkMobile.style.display = 'block';
            if (navShopLink) navShopLink.style.display = 'block';
            if (navShopLinkMobile) navShopLinkMobile.style.display = 'block';
            if (navQuizLink) navQuizLink.style.display = 'block';
            if (navQuizLinkMobile) navQuizLinkMobile.style.display = 'block';
            if (navBookshelfLink) navBookshelfLink.style.display = 'block';
            if (navBookshelfLinkMobile) navBookshelfLinkMobile.style.display = 'block';
            if (navInventoryLink) navInventoryLink.style.display = 'block';
            if (navInventoryLinkMobile) navInventoryLinkMobile.style.display = 'block';
        } else {
            // Show auth buttons, hide user info
            if (authButtons) authButtons.style.display = 'flex';
            if (authButtonsMobile) authButtonsMobile.style.display = 'flex';
            if (userInfo) userInfo.style.display = 'none';
            if (userInfoMobile) userInfoMobile.style.display = 'none';
            
            // Hide protected links
            if (navDashboardLink) navDashboardLink.style.display = 'none';
            if (navDashboardLinkMobile) navDashboardLinkMobile.style.display = 'none';
            if (navShopLink) navShopLink.style.display = 'none';
            if (navShopLinkMobile) navShopLinkMobile.style.display = 'none';
            if (navQuizLink) navQuizLink.style.display = 'none';
            if (navQuizLinkMobile) navQuizLinkMobile.style.display = 'none';
            if (navBookshelfLink) navBookshelfLink.style.display = 'none';
            if (navBookshelfLinkMobile) navBookshelfLinkMobile.style.display = 'none';
            if (navInventoryLink) navInventoryLink.style.display = 'none';
            if (navInventoryLinkMobile) navInventoryLinkMobile.style.display = 'none';
        }
    }, 100);
}

/**
 * Handle logout
 */
function handleLogout() {
    if (window.Auth) {
        window.Auth.logout();
        window.location.href = 'index.html';
    }
}

// Make functions available globally
if (typeof window !== 'undefined') {
    window.updateNavigation = updateNavigation;
    window.handleLogout = handleLogout;
}

