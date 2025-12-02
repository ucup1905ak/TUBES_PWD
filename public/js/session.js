// Session Management Utility
const SessionManager = {
    // Check if user has a valid session
    isLoggedIn: function() {
        const sessionToken = localStorage.getItem('session_token');
        const expiresAt = localStorage.getItem('session_expires_at');
        
        if (!sessionToken || !expiresAt) {
            return false;
        }
        
        // Check if session has expired
        if (new Date(expiresAt) <= new Date()) {
            this.clearSession();
            return false;
        }
        
        return true;
    },
    
    // Get session token
    getToken: function() {
        return localStorage.getItem('session_token');
    },
    
    // Set session data
    setSession: function(sessionToken, expiresAt) {
        localStorage.setItem('session_token', sessionToken);
        localStorage.setItem('session_expires_at', expiresAt);
    },
    
    // Clear session data
    clearSession: function() {
        localStorage.removeItem('session_token');
        localStorage.removeItem('session_expires_at');
    },
    
    // Redirect to login if not logged in
    requireLogin: function() {
        if (!this.isLoggedIn()) {
            window.location.href = '/login';
            return false;
        }
        return true;
    },
    
    // Redirect to dashboard if already logged in
    redirectIfLoggedIn: function() {
        if (this.isLoggedIn()) {
            window.location.href = '/my';
            return true;
        }
        return false;
    },
    
    // Logout and redirect
    logout: function() {
        this.clearSession();
        window.location.href = '/';
    }
};
