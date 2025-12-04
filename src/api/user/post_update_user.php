<?php

function validateUpdateInput(array $input): array {
    $errors = [];

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

    // Build dynamic update query
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

    // ===============================
    // 🔥 Tambahan baru: Update Foto Profil
    // ===============================
    if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {

        // Validasi MIME
        $allowed = ['image/jpeg', 'image/png'];
        if (!in_array($_FILES['foto_profil']['type'], $allowed)) {
            return [
                'status' => 400,
                'success' => false,
                'error' => 'Invalid image format'
            ];
        }

        // Ambil binary file
        $imgData = file_get_contents($_FILES['foto_profil']['tmp_name']);

        $updateFields[] = "foto_profil = ?";
        $params[] = $imgData;
        $types .= "b"; // blob
    }

    if (empty($updateFields)) {
        return [
            'status' => 400,
            'success' => false,
            'error' => 'No fields to update.'
        ];
    }

    // Tambahkan ID user
    $params[] = $userId;
    $types .= "i";

    // Query akhir
    $sql = 'UPDATE `User` SET ' . implode(', ', $updateFields) . ' WHERE id_user = ?';
    $stmt = $DB_CONN->prepare($sql);

    if (!$stmt) {
        return [
            'status' => 500,
            'success' => false,
            'error' => 'Database error: ' . $DB_CONN->error
        ];
    }

    // Bind param
    $stmt->bind_param($types, ...$params);

    // Jika ada BLOB, kirim long_data
    if (strpos($types, 'b') !== false) {
        foreach ($params as $i => $param) {
            if ($types[$i] === 'b') {
                $stmt->send_long_data($i, $param);
            }
        }
    }

    if (!$stmt->execute()) {
        $stmt->close();
        return [
            'status' => 500,
            'success' => false,
            'error' => 'Failed to update user: ' . $stmt->error
        ];
    }

    $stmt->close();

    // Fetch ulang user
    $updatedUserResponse = getCurrentUser($DB_CONN, $sessionToken, true);

    return [
        'status' => 200,
        'success' => true,
        'message' => 'User updated successfully.',
        'user' => $updatedUserResponse['user'] ?? null
    ];
}
