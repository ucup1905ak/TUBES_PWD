<?php
// src/api/auth/post_login.php
ob_start(); // Start output buffering

// Accept JSON payload or standard form-encoded POST
$input = null;
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (stripos($contentType, 'application/json') !== false) {
    $raw = file_get_contents('php://input');
    $input = json_decode($raw, true);
} else {
    $input = $_POST;
}

function handleLogin(mysqli $DB_CONN, array $input): array {
    $email = trim($input['email'] ?? $input['username'] ?? '');
    $password = $input['password'] ?? '';

    if ($email === '' || $password === '') {
        return [
            'status' => 400,
            'success' => false,
            'error' => 'Missing credentials'
        ];
    }

    // Query the DB for user by email (or adapt to username)
    $stmt = $DB_CONN->prepare('SELECT id_user, nama_lengkap, email, password FROM `User` WHERE email = ? LIMIT 1');
    if (!$stmt) {
        return [
            'status' => 500,
            'success' => false,
            'error' => 'Server error'
        ];
    }
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user || !password_verify($password, $user['password'])) {
        return [
            'status' => 401,
            'success' => false,
            'error' => 'Invalid credentials'
        ];
    }

    // Login success: create session
    session_start();
    $_SESSION['user_id'] = (int) $user['id_user'];
    $_SESSION['user_name'] = $user['nama_lengkap'];
    $_SESSION['email'] = $user['email'];

    // Don't return password
    unset($user['password']);

    return [
        'status' => 200,
        'success' => true,
        'user' => $user
    ];
}

