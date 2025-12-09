<?php

/**
 * Add a new pet for the authenticated user
 */
function validatePetInput(array $input): array {
    $errors = [];
    
    if (empty(trim($input['nama_pet'] ?? ''))) {
        $errors[] = 'Nama pet is required.';
    } elseif (strlen(trim($input['nama_pet'])) > 100) {
        $errors[] = 'Nama pet must not exceed 100 characters.';
    }
    
    if (isset($input['jenis_pet']) && strlen(trim($input['jenis_pet'])) > 50) {
        $errors[] = 'Jenis pet must not exceed 50 characters.';
    }
    
    if (isset($input['ras']) && strlen(trim($input['ras'])) > 50) {
        $errors[] = 'Ras must not exceed 50 characters.';
    }
    
    if (isset($input['umur']) && !is_numeric($input['umur'])) {
        $errors[] = 'Umur must be a number.';
    }
    
    if (isset($input['jenis_kelamin']) && strlen(trim($input['jenis_kelamin'])) > 10) {
        $errors[] = 'Jenis kelamin must not exceed 10 characters.';
    }
    
    if (isset($input['warna']) && strlen(trim($input['warna'])) > 50) {
        $errors[] = 'Warna must not exceed 50 characters.';
    }
    
    return $errors;
}

function handleTambahHewan(mysqli $DB_CONN, string $sessionToken, array $input): array {
    include_once __DIR__ . '/../auth/get_me.php';
    
    // Validate session
    $userResponse = getCurrentUser($DB_CONN, $sessionToken);
    if ($userResponse['status'] !== 200) {
        return $userResponse;
    }
    
    $userId = $userResponse['user']['id_user'];
    
    // Validate input
    $errors = validatePetInput($input);
    if (!empty($errors)) {
        return [
            'status' => 400,
            'success' => false,
            'error' => 'Validation failed',
            'details' => $errors
        ];
    }
    
    // Prepare data
    $nama_pet = trim($input['nama_pet']);
    $jenis_pet = trim($input['jenis_pet'] ?? '');
    $ras = trim($input['ras'] ?? '');
    $umur = isset($input['umur']) && $input['umur'] !== '' ? (int)$input['umur'] : null;
    $jenis_kelamin = trim($input['jenis_kelamin'] ?? '');
    $warna = trim($input['warna'] ?? '');
    $alergi = trim($input['alergi'] ?? '');
    $catatan_medis = trim($input['catatan_medis'] ?? '');
    $foto_pet = trim($input['foto_pet'] ?? '');
    
    // Insert pet
    $stmt = $DB_CONN->prepare('
        INSERT INTO Pet (id_user, nama_pet, jenis_pet, ras, umur, jenis_kelamin, warna, alergi, catatan_medis, foto_pet)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    
    if (!$stmt) {
        return ['status' => 500, 'success' => false, 'error' => 'Database error: ' . $DB_CONN->error];
    }
    
    $stmt->bind_param('isssisssss', $userId, $nama_pet, $jenis_pet, $ras, $umur, $jenis_kelamin, $warna, $alergi, $catatan_medis, $foto_pet);
    
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return ['status' => 500, 'success' => false, 'error' => 'Failed to add pet: ' . $error];
    }
    
    $petId = $stmt->insert_id;
    $stmt->close();
    
    return [
        'status' => 201,
        'success' => true,
        'message' => 'Pet added successfully.',
        'pet_id' => $petId
    ];
}

function handleUpdateHewan(mysqli $DB_CONN, string $sessionToken, int $petId, array $input): array {
    include_once __DIR__ . '/../auth/get_me.php';
    
    // Validate session
    $userResponse = getCurrentUser($DB_CONN, $sessionToken);
    if ($userResponse['status'] !== 200) {
        return $userResponse;
    }
    
    $userId = $userResponse['user']['id_user'];
    
    // Check pet ownership
    $stmt = $DB_CONN->prepare('SELECT id_pet FROM Pet WHERE id_pet = ? AND id_user = ?');
    $stmt->bind_param('ii', $petId, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        $stmt->close();
        return ['status' => 404, 'success' => false, 'error' => 'Pet not found'];
    }
    $stmt->close();
    
    // Build dynamic update query
    $updateFields = [];
    $params = [];
    $types = '';
    
    $fields = ['nama_pet', 'jenis_pet', 'ras', 'jenis_kelamin', 'warna', 'alergi', 'catatan_medis', 'foto_pet'];
    foreach ($fields as $field) {
        if (isset($input[$field])) {
            $updateFields[] = "$field = ?";
            $params[] = trim($input[$field]);
            $types .= 's';
        }
    }
    
    if (isset($input['umur'])) {
        $updateFields[] = 'umur = ?';
        $params[] = (int)$input['umur'];
        $types .= 'i';
    }
    
    if (empty($updateFields)) {
        return ['status' => 400, 'success' => false, 'error' => 'No fields to update.'];
    }
    
    $params[] = $petId;
    $types .= 'i';
    
    $sql = 'UPDATE Pet SET ' . implode(', ', $updateFields) . ' WHERE id_pet = ?';
    $stmt = $DB_CONN->prepare($sql);
    if (!$stmt) {
        return ['status' => 500, 'success' => false, 'error' => 'Database error'];
    }
    
    $stmt->bind_param($types, ...$params);
    if (!$stmt->execute()) {
        $error = $stmt->error;
        $stmt->close();
        return ['status' => 500, 'success' => false, 'error' => 'Failed to update pet: ' . $error];
    }
    $stmt->close();
    
    return ['status' => 200, 'success' => true, 'message' => 'Pet updated successfully.'];
}

function handleDeleteHewan(mysqli $DB_CONN, string $sessionToken, int $petId): array {
    include_once __DIR__ . '/../auth/get_me.php';
    
    // Validate session
    $userResponse = getCurrentUser($DB_CONN, $sessionToken);
    if ($userResponse['status'] !== 200) {
        return $userResponse;
    }
    
    $userId = $userResponse['user']['id_user'];
    
    // Delete pet (only if owned by user)
    $stmt = $DB_CONN->prepare('DELETE FROM Pet WHERE id_pet = ? AND id_user = ?');
    if (!$stmt) {
        return ['status' => 500, 'success' => false, 'error' => 'Database error'];
    }
    
    $stmt->bind_param('ii', $petId, $userId);
    $stmt->execute();
    
    if ($stmt->affected_rows === 0) {
        $stmt->close();
        return ['status' => 404, 'success' => false, 'error' => 'Pet not found'];
    }
    
    $stmt->close();
    return ['status' => 200, 'success' => true, 'message' => 'Pet deleted successfully.'];
}
