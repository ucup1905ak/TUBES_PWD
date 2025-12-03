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

    // === NEW FIELDS FROM FRONTEND ===
    $kamar = $input['kamar'] ?? null;
    $layanan = isset($input['layanan']) ? json_encode($input['layanan']) : json_encode([]);
    $durasi = (int)($input['durasi'] ?? 0);
    $total_biaya = (int)($input['total_biaya'] ?? 0);

    $id_pet = (int)$input['id_pet'];
    $tgl_checkin = $input['tgl_checkin'];
    $tgl_checkout = $input['tgl_checkout'];
    $status = 'aktif';

    // Insert penitipan + new fields
    $stmt = $DB_CONN->prepare("
        INSERT INTO Penitipan 
        (id_user, id_pet, tgl_checkin, tgl_checkout, kamar, layanan, durasi, total_biaya, status_penitipan)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        return ['status' => 500, 'success' => false, 'error' => $DB_CONN->error];
    }

    $stmt->bind_param(
        "iissssiss",
        $userId,
        $id_pet,
        $tgl_checkin,
        $tgl_checkout,
        $kamar,
        $layanan,
        $durasi,
        $total_biaya,
        $status
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

    // kamar
    if (isset($input['kamar'])) {
        $updateFields[] = "kamar = ?";
        $params[] = $input['kamar'];
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

