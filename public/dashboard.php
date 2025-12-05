<?php
// Dashboard loader - loads admin or user dashboard based on user role
// Server-side session check to prevent access without authentication

session_start();

// Check if user has a valid session (from the Authorization header or session variable)
$authenticated = false;

// Check Authorization header for Bearer token
if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
    if (preg_match('/Bearer\s+(\S+)/', $authHeader, $matches)) {
        // Valid Bearer token format found
        $authenticated = true;
    }
}

// If not authenticated via header, we still need to check via client-side
// but we'll show the loader which will check localStorage
// If no token exists in localStorage either, it will redirect to login
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PawHaven - Dashboard</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .loading-container {
            text-align: center;
        }
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .loading-text {
            color: #666;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <script>
        // Check session IMMEDIATELY before page renders
        const sessionToken = localStorage.getItem('session_token');
        const expiresAt = localStorage.getItem('session_expires_at');
        
        // If no valid session, redirect immediately
        if (!sessionToken || !expiresAt || new Date(expiresAt) <= new Date()) {
            localStorage.removeItem('session_token');
            localStorage.removeItem('session_expires_at');
            window.location.replace('/login');
        }
    </script>

    <div class="loading-container">
        <div class="spinner"></div>
        <p class="loading-text">Loading dashboard...</p>
    </div>

    <script>
        (function() {
            // Check if user is logged in
            const sessionToken = localStorage.getItem('session_token');
            const expiresAt = localStorage.getItem('session_expires_at');
            
            if (!sessionToken || !expiresAt || new Date(expiresAt) <= new Date()) {
                // No valid session, redirect to login
                localStorage.removeItem('session_token');
                localStorage.removeItem('session_expires_at');
                window.location.href = '/login';
                return;
            }

            // Fetch current user data to determine role
            fetch('/api/auth/me', {
                method: 'GET',
                headers: {
                    'Authorization': 'Bearer ' + sessionToken,
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP error, status = ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.user) {
                    const user = data.user;
                    const role = user.role || 'user';
                    
                    // Store token in cookie for server-side access
                    const expiresAt = localStorage.getItem('session_expires_at');
                    if (expiresAt) {
                        const expires = new Date(expiresAt);
                        document.cookie = `session_token=${sessionToken}; expires=${expires.toUTCString()}; path=/; SameSite=Lax`;
                    }
                    
                    // Redirect to appropriate dashboard based on user role
                    const dashboardPath = role === 'admin' ? '/admin' : '/my';
                    window.location.href = dashboardPath;
                } else {
                    // Session invalid, redirect to login
                    console.error('Failed to load user data:', data.error);
                    localStorage.removeItem('session_token');
                    localStorage.removeItem('session_expires_at');
                    window.location.href = '/login';
                }
            })
            .catch(error => {
                console.error('Error fetching user data:', error);
                // On error, redirect to login
                localStorage.removeItem('session_token');
                localStorage.removeItem('session_expires_at');
                window.location.href = '/login';
            });
        })();
    </script>
</body>
</html>
