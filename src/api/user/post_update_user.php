<?php

function validateUpdateInput(array $input): array {
    $errors = [];

    // All fields are optional for update, but if provided, validate them
    if (isset($input['nama_lengkap']) && strlen(trim($input['nama_lengkap'])) > 100) {
        $errors[] = 'Username must not exceed 100 characters.';
    }

    if (isset($input['no_telp']) && strlen(trim($input['no_telp'])) > 15) {
        $errors[] = 'Phone number must not exceed 15 characters.';
    }

    if (isset($input['role']) && !in_array($input['role'], ['user', 'admin'])) {
        $errors[] = 'Invalid role. Must be "user" or "admin".';
    }

    return $errors;
}

function handleUpdateUser(mysqli $DB_CONN, string $sessionToken, array $input): array {
    // Validate session token and get user
    include_once __DIR__ . '/../auth/get_me.php';
    $userResponse = getCurrentUser($DB_CONN, $sessionToken, false);
    
    if ($userResponse['status'] !== 200) {
        return $userResponse;
    }

    $currentUser = $userResponse['user'];
    $userId = $currentUser['id_user'];

    // Validate input
    $errors = validateUpdateInput($input);
    if (!empty($errors)) {
        return [
            'status' => 400,
            'success' => false,
            'error' => 'Validation failed',
            'details' => $errors
        ];
    }

    // Build dynamic update query based on provided fields
    $updateFields = [];
    $params = [];
    $types = '';

    if (isset($input['nama_lengkap']) && !empty(trim($input['nama_lengkap']))) {
        $updateFields[] = 'nama_lengkap = ?';
        $params[] = trim($input['nama_lengkap']);
        $types .= 's';
    }

    if (isset($input['no_telp'])) {
        $updateFields[] = 'no_telp = ?';
        $params[] = trim($input['no_telp']);
        $types .= 's';
    }

    if (isset($input['alamat'])) {
        $updateFields[] = 'alamat = ?';
        $params[] = trim($input['alamat']);
        $types .= 's';
    }

    if (isset($input['role']) && in_array($input['role'], ['user', 'admin'])) {
        $updateFields[] = 'role = ?';
        $params[] = $input['role'];
        $types .= 's';
    }

    if (empty($updateFields)) {
        return [
            'status' => 400,
            'success' => false,
            'error' => 'No fields to update.'
        ];
    }

    // Add user ID to params
    $params[] = $userId;
    $types .= 'i';

    $sql = 'UPDATE `User` SET ' . implode(', ', $updateFields) . ' WHERE id_user = ?';
    
    $stmt = $DB_CONN->prepare($sql);
    if (!$stmt) {
        return [
            'status' => 500,
            'success' => false,
            'error' => 'Database error: ' . $DB_CONN->error
        ];
    }

    $stmt->bind_param($types, ...$params);
    
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return [
            'status' => 500,
            'success' => false,
            'error' => 'Failed to update user: ' . $error
        ];
    }

    $stmt->close();

    // Fetch updated user data
    $updatedUserResponse = getCurrentUser($DB_CONN, $sessionToken, true);

    return [
        'status' => 200,
        'success' => true,
        'message' => 'User updated successfully.',
        'user' => $updatedUserResponse['user'] ?? null
    ];
}
