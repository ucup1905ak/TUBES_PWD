<?php
// For now, we'll allow the frontend to check session via localStorage
// In a production app, you'd validate the session_token against the database
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PawHaven - Dashboard</title>
    <link rel="stylesheet" href="/public/css/dashboard.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
        }

        .dashboard-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
        }

        .sidebar-header {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .sidebar-header h2 {
            font-size: 24px;
            margin-bottom: 5px;
        }

        .sidebar-header p {
            font-size: 12px;
            opacity: 0.8;
        }

        .sidebar-menu {
            list-style: none;
        }

        .sidebar-menu li {
            margin: 5px 0;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 15px 20px;
            color: white;
            text-decoration: none;
            transition: all 0.3s;
        }

        .sidebar-menu a:hover,
        .sidebar-menu a.active {
            background: rgba(255, 255, 255, 0.1);
            border-left: 4px solid white;
            padding-left: 16px;
        }

        .sidebar-menu a span {
            margin-right: 10px;
            font-size: 20px;
        }

        .sidebar-footer {
            position: absolute;
            bottom: 20px;
            left: 20px;
            right: 20px;
        }

        .logout-btn {
            width: 100%;
            padding: 12px;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Main Content Styles */
        .main-content {
            margin-left: 250px;
            flex: 1;
            padding: 30px;
        }

        .top-bar {
            background: white;
            padding: 20px 30px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .top-bar h1 {
            font-size: 28px;
            color: #333;
        }

        .user-greeting {
            color: #667eea;
            font-weight: 600;
        }

        .profile-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .profile-picture-container {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid #667eea;
            margin-right: 30px;
        }

        .profile-picture-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-info {
            flex: 1;
        }

        .profile-info h2 {
            font-size: 24px;
            color: #333;
            margin-bottom: 5px;
        }

        .profile-info .user-role {
            color: #667eea;
            font-size: 14px;
            font-weight: 600;
        }

        .profile-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .detail-item {
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }

        .detail-item strong {
            display: block;
            color: #666;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .detail-item span {
            display: block;
            color: #333;
            font-size: 16px;
        }

        .loading {
            text-align: center;
            padding: 40px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="dashboard-wrapper">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>🐾 PawHaven</h2>
                <p>Pet Boarding Service</p>
            </div>
            <ul class="sidebar-menu">
                <li><a href="/my" class="active"><span>🏠</span> Dashboard</a></li>
                <li><a href="/my/profile"><span>👤</span> My Profile</a></li>
                <li><a href="/my/pets"><span>🐕</span> My Pets</a></li>
                <li><a href="/my/bookings"><span>📅</span> Bookings</a></li>
                <li><a href="/my/history"><span>📋</span> History</a></li>
                <li><a href="/my/settings"><span>⚙️</span> Settings</a></li>
            </ul>
            <div class="sidebar-footer">
                <button class="logout-btn" id="logout-button">🚪 Logout</button>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="top-bar">
                <h1>Welcome back, <span class="user-greeting" id="user-name-display">User</span>!</h1>
                <div>
                    <span id="current-date"></span>
                </div>
            </div>

            <div class="profile-section">
                <div class="profile-header">
                    <div class="profile-picture-container">
                        <img id="profile-picture" src="/public/img/default-avatar.png" alt="User Profile Picture">
                    </div>
                    <div class="profile-info">
                        <h2 id="user-full-name">Loading...</h2>
                        <p class="user-role">Customer</p>
                    </div>
                </div>

                <div class="profile-details">
                    <div class="detail-item">
                        <strong>User ID</strong>
                        <span id="user-id">Loading...</span>
                    </div>
                    <div class="detail-item">
                        <strong>Email Address</strong>
                        <span id="user-email">Loading...</span>
                    </div>
                    <div class="detail-item">
                        <strong>Phone Number</strong>
                        <span id="user-phone">Loading...</span>
                    </div>
                    <div class="detail-item">
                        <strong>Address</strong>
                        <span id="user-address">Loading...</span>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Check if user is logged in
        const sessionToken = localStorage.getItem('session_token');
        const expiresAt = localStorage.getItem('session_expires_at');
        
        if (!sessionToken || !expiresAt || new Date(expiresAt) <= new Date()) {
            // No valid session, redirect to login
            localStorage.removeItem('session_token');
            localStorage.removeItem('session_expires_at');
            window.location.href = '/login';
        }

        // Display current date
        const dateElement = document.getElementById('current-date');
        const today = new Date();
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        dateElement.textContent = today.toLocaleDateString('en-US', options);

        document.addEventListener('DOMContentLoaded', () => {
            // Fetch current user data using session token
            const sessionToken = localStorage.getItem('session_token');
            
            if (sessionToken) {
                fetch('/api/auth/me', {
                    method: 'GET',
                    headers: {
                        'Authorization': 'Bearer ' + sessionToken,
                        'Content-Type': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.user) {
                        const user = data.user;
                        
                        // Update UI with user data
                        document.getElementById('user-name-display').textContent = user.nama_lengkap || 'User';
                        document.getElementById('user-full-name').textContent = user.nama_lengkap || 'N/A';
                        document.getElementById('user-id').textContent = user.id_user || 'N/A';
                        document.getElementById('user-email').textContent = user.email || 'N/A';
                        document.getElementById('user-phone').textContent = user.no_telp || 'N/A';
                        document.getElementById('user-address').textContent = user.alamat || 'N/A';
                        
                        // Update profile picture if available
                        if (user.foto_profil) {
                            const profilePic = document.getElementById('profile-picture');
                            profilePic.src = 'data:image/jpeg;base64,' + user.foto_profil;
                        }
                    } else {
                        console.error('Failed to load user data:', data.error);
                    }
                })
                .catch(error => {
                    console.error('Error fetching user data:', error);
                });
            }

            // Handle logout
            document.getElementById('logout-button').addEventListener('click', () => {
                if (confirm('Are you sure you want to logout?')) {
                    // Clear session from localStorage
                    localStorage.removeItem('session_token');
                    localStorage.removeItem('session_expires_at');
                    
                    // Redirect to landing page
                    window.location.href = '/';
                }
            });
        });
    </script>
</body>
</html>
