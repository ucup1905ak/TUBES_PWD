<?php

/**
 * Add, update, or delete penitipan
 */

/**
 * Register or get existing pet for a user
 * Prevents duplicate pet entries
 */
function registerOrGetPet(mysqli $DB_CONN, int $userId, array $petData): array {
    include_once __DIR__ . '/../hewan/post_tambah_hewan.php';
    
    // Validate pet input
    $errors = validatePetInput($petData);
    if (!empty($errors)) {
        return [
            'status' => 400,
            'success' => false,
            'error' => 'Pet validation failed',
            'details' => $errors
        ];
    }
    
    $nama_pet = trim($petData['nama_pet']);
    
    // Check if pet with same name already exists for this user
    $checkStmt = $DB_CONN->prepare('SELECT id_pet FROM Pet WHERE id_user = ? AND nama_pet = ?');
    if (!$checkStmt) {
        return ['status' => 500, 'success' => false, 'error' => 'Database error'];
    }
    
    $checkStmt->bind_param('is', $userId, $nama_pet);
    $checkStmt->execute();
    $result = $checkStmt->get_result();
    
    if ($result->num_rows > 0) {
        // Pet already exists
        $row = $result->fetch_assoc();
        $checkStmt->close();
        return [
            'status' => 200,
            'success' => true,
            'message' => 'Pet already exists',
            'pet_id' => $row['id_pet'],
            'is_existing' => true
        ];
    }
    
    $checkStmt->close();
    
    // Create new pet
    $jenis_pet = trim($petData['jenis_pet'] ?? '');
    $ras = trim($petData['ras'] ?? '');
    $umur = isset($petData['umur']) && $petData['umur'] !== '' ? (int)$petData['umur'] : null;
    $jenis_kelamin = trim($petData['jenis_kelamin'] ?? '');
    $warna = trim($petData['warna'] ?? '');
    $alergi = trim($petData['alergi'] ?? '');
    $catatan_medis = trim($petData['catatan_medis'] ?? '');
    $foto_pet = trim($petData['foto_pet'] ?? '');
    
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
        'message' => 'Pet added successfully',
        'pet_id' => $petId,
        'is_existing' => false
    ];
}

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

    // Validate required fields
    $errors = validatePenitipanInput($input);
    if (!empty($errors)) {
        return [
            'status' => 400,
            'success' => false,
            'error' => 'Validation failed',
            'details' => $errors
        ];
    }

    // === HANDLE PET REGISTRATION/RETRIEVAL INDEPENDENTLY ===
    // If pet data is provided, register or get existing pet
    $id_pet = null;
    if (!empty($input['id_pet'])) {
        // Use existing pet ID from frontend
        $id_pet = (int)$input['id_pet'];
    } elseif (!empty($input['pet_data'])) {
        // Register or get existing pet
        $petResponse = registerOrGetPet($DB_CONN, $userId, $input['pet_data']);
        if ($petResponse['status'] !== 200 && $petResponse['status'] !== 201) {
            return $petResponse;
        }
        $id_pet = $petResponse['pet_id'];
    } else {
        return [
            'status' => 400,
            'success' => false,
            'error' => 'Either id_pet or pet_data must be provided'
        ];
    }

    // === NEW FIELDS FROM FRONTEND ===
    $nama_paket = $input['kamar'] ?? $input['nama_paket'] ?? null;
    $layanan = isset($input['layanan']) ? json_encode($input['layanan']) : json_encode([]);
    $durasi = (int)($input['durasi'] ?? 0);
    $total_biaya = (int)($input['total_biaya'] ?? 0);

    $tgl_checkin = $input['tgl_checkin'];
    $tgl_checkout = $input['tgl_checkout'];
    $status = 'aktif';

    // Insert penitipan with all fields
    $stmt = $DB_CONN->prepare("
        INSERT INTO Penitipan 
        (id_user, id_pet, tgl_checkin, tgl_checkout, nama_paket, layanan, durasi, total_biaya, status_penitipan)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        return ['status' => 500, 'success' => false, 'error' => $DB_CONN->error];
    }

    $stmt->bind_param(
        "iissssiis",
        $userId,       // i
        $id_pet,       // i
        $tgl_checkin,  // s
        $tgl_checkout, // s
        $nama_paket,   // s
        $layanan,      // s
        $durasi,       // i
        $total_biaya,  // i
        $status        // s
    );

    if (!$stmt->execute()) {
        return ['status' => 500, 'success' => false, 'error' => $stmt->error];
    }

    $insertId = $stmt->insert_id;
    $stmt->close();

    return [
        'status' => 201,
        'success' => true,
        'message' => 'Penitipan created successfully',
        'penitipan_id' => $insertId
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

    // Check ownership
    $stmt = $DB_CONN->prepare("SELECT id_penitipan FROM Penitipan WHERE id_penitipan = ? AND id_user = ?");
    $stmt->bind_param("ii", $penitipanId, $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows === 0) {
        return ['status' => 404, 'success' => false, 'error' => 'Penitipan not found'];
    }
    $stmt->close();

    // Build update query
    $updateFields = [];
    $params = [];
    $types = '';

    // id_pet
    if (isset($input['id_pet'])) {
        $updateFields[] = "id_pet = ?";
        $params[] = (int)$input['id_pet'];
        $types .= "i";
    }

    // tanggal
    if (isset($input['tgl_checkin'])) {
        $updateFields[] = "tgl_checkin = ?";
        $params[] = $input['tgl_checkin'];
        $types .= "s";
    }

    if (isset($input['tgl_checkout'])) {
        $updateFields[] = "tgl_checkout = ?";
        $params[] = $input['tgl_checkout'];
        $types .= "s";
    }

    // kamar/nama_paket
    if (isset($input['kamar'])) {
        $updateFields[] = "nama_paket = ?";
        $params[] = $input['kamar'];
        $types .= "s";
    }

    if (isset($input['nama_paket'])) {
        $updateFields[] = "nama_paket = ?";
        $params[] = $input['nama_paket'];
        $types .= "s";
    }

    // layanan → json
    if (isset($input['layanan'])) {
        $updateFields[] = "layanan = ?";
        $params[] = json_encode($input['layanan']);
        $types .= "s";
    }

    // durasi
    if (isset($input['durasi'])) {
        $updateFields[] = "durasi = ?";
        $params[] = (int)$input['durasi'];
        $types .= "i";
    }

    // total biaya
    if (isset($input['total_biaya'])) {
        $updateFields[] = "total_biaya = ?";
        $params[] = (int)$input['total_biaya'];
        $types .= "i";
    }

    // status
    if (isset($input['status_penitipan'])) {
        $updateFields[] = "status_penitipan = ?";
        $params[] = $input['status_penitipan'];
        $types .= "s";
    }

    // Kalau kosong → error
    if (empty($updateFields)) {
        return ['status' => 400, 'success' => false, 'error' => 'No fields to update'];
    }

    // Add WHERE id
    $params[] = $penitipanId;
    $types .= "i";

    // Build SQL
    $sql = "UPDATE Penitipan SET " . implode(", ", $updateFields) . " WHERE id_penitipan = ?";

    $stmt = $DB_CONN->prepare($sql);
    if (!$stmt) {
        return ['status' => 500, 'success' => false, 'error' => 'Database error: '.$DB_CONN->error];
    }

    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $stmt->close();

    return ['status' => 200, 'success' => true, 'message' => 'Penitipan updated successfully'];
}

function handleDeletePenitipan(mysqli $DB_CONN, string $sessionToken, int $penitipanId): array {
    include_once __DIR__ . '/../auth/get_me.php';

    // Validate session
    $userResponse = getCurrentUser($DB_CONN, $sessionToken);
    if ($userResponse['status'] !== 200) {
        return $userResponse;
    }

    $userId = $userResponse['user']['id_user'];

    // Delete only if owned by user
    $stmt = $DB_CONN->prepare("DELETE FROM Penitipan WHERE id_penitipan = ? AND id_user = ?");
    if (!$stmt) {
        return ['status' => 500, 'success' => false, 'error' => 'Database error'];
    }

    $stmt->bind_param("ii", $penitipanId, $userId);
    $stmt->execute();

    if ($stmt->affected_rows === 0) {
        return ['status' => 404, 'success' => false, 'error' => 'Penitipan not found'];
    }

    $stmt->close();
    return ['status' => 200, 'success' => true, 'message' => 'Penitipan deleted successfully'];
}

