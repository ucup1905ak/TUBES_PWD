<?php
// src/api/auth/post_login.php

function handleLogin(mysqli $DB_CONN, array $input): array {
    // Validate input - accept either email or username
    $identifier = $input['email'] ?? $input['username'] ?? '';
    if (empty($identifier) || empty($input['password'])) {
        return [
            'status' => 400,
            'error' => 'Email/username and password are required.'
        ];
    }
    
    // Fetch user by email or username (nama_lengkap)
    $stmt = $DB_CONN->prepare("SELECT id_user, password FROM User WHERE email = ? OR nama_lengkap = ?");
    if (!$stmt) {
        return [
            'status' => 500,
            'error' => 'Database error: ' . $DB_CONN->error
        ];
    }
    $stmt->bind_param("ss", $identifier, $identifier);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows === 0) {
        return [
            'status' => 401,
            'error' => 'Invalid email or password.'
        ];
    }

    $stmt->bind_result($id_user, $hashed_password);
    $stmt->fetch();

    // Since the password is already hashed (SHA-256) on the client,
    // compare the hashes directly (assuming the DB stores SHA-256 hashes as well)
    if ($input['password'] !== $hashed_password) {
        return [
            'status' => 401,
            'error' => 'Invalid email or password.'
        ];
    }

    // Generate session token
    $session_token = bin2hex(random_bytes(32));
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? null;
    $expires_at = date('Y-m-d H:i:s', time() + 60 * 60 * 24); // 24 hours

    // Store session in User_Session table
    $insert = $DB_CONN->prepare("INSERT INTO User_Session (id_user, session_token, ip_address, expires_at) VALUES (?, ?, ?, ?)");
    if (!$insert) {
        return [
            'status' => 500,
            'error' => 'Database error: ' . $DB_CONN->error
        ];
    }
    $insert->bind_param("isss", $id_user, $session_token, $ip_address, $expires_at);
    if (!$insert->execute()) {
        return [
            'status' => 500,
            'error' => 'Failed to create session.'
        ];
    }

    return [
        'status' => 200,
        'session_token' => $session_token,
        'expires_at' => $expires_at
    ];
}


// Architecture Overview
// Frontend (Client Side)

// HTML forms for login/registration
// JavaScript for API calls and UI updates
// Stores session token (usually in cookies or localStorage)
?>


