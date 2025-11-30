<?php
session_start();

// If the user is not logged in, redirect them to the login page.
if (!isset($_SESSION['user_id'])) {
    header('Location: /pages/login.xhtml');
    exit;
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard</title>
    <link rel="stylesheet" href="/css/dashboard.css">
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Welcome, <span id="user-name-display"><?php echo htmlspecialchars($user_name); ?></span>!</h1>
            <button id="logout-button">Logout</button>
        </header>
        <main>
            <h2>Your Profile</h2>
            <div class="profile-card">
                <div class="profile-picture-container">
                    <img id="profile-picture" src="/img/default-avatar.png" alt="User Profile Picture">
                </div>
                <div class="profile-details">
                    <p><strong>ID:</strong> <span id="user-id"></span></p>
                    <p><strong>Email:</strong> <span id="user-email"></span></p>
                    <p><strong>Phone:</strong> <span id="user-phone"></span></p>
                    <p><strong>Address:</strong> <span id="user-address"></span></p>
                </div>
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const userId = <?php echo json_encode($user_id); ?>;

            // Fetch user data from the API
            fetch(`/api/user/${userId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Failed to fetch user data');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.user) {
                        const user = data.user;
                        document.getElementById('user-id').textContent = user.id_user;
                        document.getElementById('user-email').textContent = user.email || 'N/A';
                        document.getElementById('user-phone').textContent = user.no_telp || 'N/A';
                        document.getElementById('user-address').textContent = user.alamat || 'N/A';

                        // Handle the profile picture
                        if (user.foto_profil) {
                            // Assuming the API sends a base64 encoded string for the image
                            document.getElementById('profile-picture').src = `data:image/jpeg;base64,${user.foto_profil}`;
                        }
                    } else {
                        throw new Error(data.error || 'Could not parse user data');
                    }
                })
                .catch(error => {
                    console.error('Error fetching profile:', error);
                    alert('Could not load your profile information.');
                });

            // Handle logout
            document.getElementById('logout-button').addEventListener('click', () => {
                fetch('/api/auth/logout', { method: 'POST' })
                    .then(() => {
                        // Redirect to login page after successful logout
                        window.location.href = '/pages/login.xhtml';
                    })
                    .catch(error => console.error('Logout failed:', error));
            });
        });
    </script>
</body>
</html>
