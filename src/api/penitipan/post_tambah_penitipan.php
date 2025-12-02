<?php

/**
 * Add, update, or delete penitipan
 */
function validatePenitipanInput(array $input): array {
    $errors = [];
    
    if (empty($input['id_pet'])) {
        $errors[] = 'Pet is required.';
    }
    
    if (empty($input['tgl_checkin'])) {
        $errors[] = 'Check-in date is required.';
    }
    
    if (empty($input['tgl_checkout'])) {
        $errors[] = 'Check-out date is required.';
    }
    
    // Validate date format
    if (!empty($input['tgl_checkin']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $input['tgl_checkin'])) {
        $errors[] = 'Invalid check-in date format. Use YYYY-MM-DD.';
    }
    
    if (!empty($input['tgl_checkout']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $input['tgl_checkout'])) {
        $errors[] = 'Invalid check-out date format. Use YYYY-MM-DD.';
    }
    
    // Validate checkout is after checkin
    if (!empty($input['tgl_checkin']) && !empty($input['tgl_checkout'])) {
        if (strtotime($input['tgl_checkout']) < strtotime($input['tgl_checkin'])) {
            $errors[] = 'Check-out date must be after check-in date.';
        }
    }
    
    return $errors;
}

function handleTambahPenitipan(mysqli $DB_CONN, string $sessionToken, array $input): array {
    include_once __DIR__ . '/../auth/get_me.php';
    
    // Validate session
    $userResponse = getCurrentUser($DB_CONN, $sessionToken);
    if ($userResponse['status'] !== 200) {
        return $userResponse;
    }
    
    $userId = $userResponse['user']['id_user'];
    
    // Validate input
    $errors = validatePenitipanInput($input);
    if (!empty($errors)) {
        return [
            'status' => 400,
            'success' => false,
            'error' => 'Validation failed',
            'details' => $errors
        ];
    }
    
    $id_pet = (int)$input['id_pet'];
    $tgl_checkin = $input['tgl_checkin'];
    $tgl_checkout = $input['tgl_checkout'];
    $id_paket = isset($input['id_paket']) && $input['id_paket'] !== '' ? (int)$input['id_paket'] : null;
    $status = $input['status_penitipan'] ?? 'aktif';
    
    // Verify pet belongs to user
    $stmt = $DB_CONN->prepare('SELECT id_pet FROM Pet WHERE id_pet = ? AND id_user = ?');
    if (!$stmt) {
        return ['status' => 500, 'success' => false, 'error' => 'Database error'];
    }
    $stmt->bind_param('ii', $id_pet, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        $stmt->close();
        return ['status' => 400, 'success' => false, 'error' => 'Pet not found or does not belong to you.'];
    }
    $stmt->close();
    
    // Insert penitipan
    $stmt = $DB_CONN->prepare('
        INSERT INTO Penitipan (id_user, id_pet, tgl_checkin, tgl_checkout, id_paket, status_penitipan)
        VALUES (?, ?, ?, ?, ?, ?)
    ');
    
    if (!$stmt) {
        return ['status' => 500, 'success' => false, 'error' => 'Database error: ' . $DB_CONN->error];
    }
    
    $stmt->bind_param('iissis', $userId, $id_pet, $tgl_checkin, $tgl_checkout, $id_paket, $status);
    
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return ['status' => 500, 'success' => false, 'error' => 'Failed to add penitipan: ' . $error];
    }
    
    $penitipanId = $stmt->insert_id;
    $stmt->close();
    
    return [
        'status' => 201,
        'success' => true,
        'message' => 'Penitipan created successfully.',
        'penitipan_id' => $penitipanId
    ];
}

function handleUpdatePenitipan(mysqli $DB_CONN, string $sessionToken, int $penitipanId, array $input): array {
    include_once __DIR__ . '/../auth/get_me.php';
    
    // Validate session
    $userResponse = getCurrentUser($DB_CONN, $sessionToken);
    if ($userResponse['status'] !== 200) {
        return $userResponse;
    }
    
    $userId = $userResponse['user']['id_user'];
    
    // Check penitipan ownership
    $stmt = $DB_CONN->prepare('SELECT id_penitipan FROM Penitipan WHERE id_penitipan = ? AND id_user = ?');
    $stmt->bind_param('ii', $penitipanId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        $stmt->close();
        return ['status' => 404, 'success' => false, 'error' => 'Penitipan not found'];
    }
    $stmt->close();
    
    // Build dynamic update query
    $updateFields = [];
    $params = [];
    $types = '';
    
    if (isset($input['id_pet'])) {
        $updateFields[] = 'id_pet = ?';
        $params[] = (int)$input['id_pet'];
        $types .= 'i';
    }
    
    if (isset($input['tgl_checkin'])) {
        $updateFields[] = 'tgl_checkin = ?';
        $params[] = $input['tgl_checkin'];
        $types .= 's';
    }
    
    if (isset($input['tgl_checkout'])) {
        $updateFields[] = 'tgl_checkout = ?';
        $params[] = $input['tgl_checkout'];
        $types .= 's';
    }
    
    if (isset($input['id_paket'])) {
        $updateFields[] = 'id_paket = ?';
        $params[] = $input['id_paket'] !== '' ? (int)$input['id_paket'] : null;
        $types .= 'i';
    }
    
    if (isset($input['status_penitipan'])) {
        $updateFields[] = 'status_penitipan = ?';
        $params[] = $input['status_penitipan'];
        $types .= 's';
    }
    
    if (empty($updateFields)) {
        return ['status' => 400, 'success' => false, 'error' => 'No fields to update.'];
    }
    
    $params[] = $penitipanId;
    $types .= 'i';
    
    $sql = 'UPDATE Penitipan SET ' . implode(', ', $updateFields) . ' WHERE id_penitipan = ?';
    $stmt = $DB_CONN->prepare($sql);
    if (!$stmt) {
        return ['status' => 500, 'success' => false, 'error' => 'Database error'];
    }
    
    $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return ['status' => 500, 'success' => false, 'error' => 'Failed to update penitipan: ' . $error];
    }
    $stmt->close();
    
    return ['status' => 200, 'success' => true, 'message' => 'Penitipan updated successfully.'];
}

function handleDeletePenitipan(mysqli $DB_CONN, string $sessionToken, int $penitipanId): array {
    include_once __DIR__ . '/../auth/get_me.php';
    
    // Validate session
    $userResponse = getCurrentUser($DB_CONN, $sessionToken);
    if ($userResponse['status'] !== 200) {
        return $userResponse;
    }
    
    $userId = $userResponse['user']['id_user'];
    
    // Delete penitipan (only if owned by user)
    $stmt = $DB_CONN->prepare('DELETE FROM Penitipan WHERE id_penitipan = ? AND id_user = ?');
    if (!$stmt) {
        return ['status' => 500, 'success' => false, 'error' => 'Database error'];
    }
    
    $stmt->bind_param('ii', $penitipanId, $userId);
    $stmt->execute();
    
    if ($stmt->affected_rows === 0) {
        $stmt->close();
        return ['status' => 404, 'success' => false, 'error' => 'Penitipan not found'];
    }
    
    $stmt->close();
    return ['status' => 200, 'success' => true, 'message' => 'Penitipan deleted successfully.'];
}
