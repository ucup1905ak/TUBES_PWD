<?php
// Get current user from session token

function getCurrentUser(mysqli $DB_CONN, string $sessionToken): array {
    // Validate session token
    $stmt = $DB_CONN->prepare("
        SELECT us.id_user, u.nama_lengkap, u.email, u.no_telp, u.alamat, u.foto_profil, u.role 
        FROM User_Session us
        JOIN User u ON us.id_user = u.id_user
        WHERE us.session_token = ? AND us.expires_at > NOW()
        LIMIT 1
    ");
    
    if (!$stmt) {
        return [
            'status' => 500,
            'error' => 'Database error: ' . $DB_CONN->error
        ];
    }
    
    $stmt->bind_param("s", $sessionToken);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $stmt->close();
        return [
            'status' => 401,
            'error' => 'Invalid or expired session token.'
        ];
    }
    
    $user = $result->fetch_assoc();
    $stmt->close();
    
    // Base64 encode profile photo if exists
    if ($user && !empty($user['foto_profil'])) {
        $user['foto_profil'] = base64_encode($user['foto_profil']);
    }
    
    return [
        'status' => 200,
        'success' => true,
        'user' => $user
    ];
}
