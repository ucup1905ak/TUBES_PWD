<?php

/**
 * Get penitipan count or list for authenticated user
 */
function handleGetJumlahPenitipan(mysqli $DB_CONN, ?string $sessionToken = null): array {
    // If no session token, return total count (for admin dashboard)
    if (empty($sessionToken)) {
        $result = $DB_CONN->query('SELECT COUNT(*) as total FROM Penitipan');
        if (!$result) {
            return ['status' => 500, 'success' => false, 'error' => 'Database error'];
        }
        $row = $result->fetch_assoc();
        return ['status' => 200, 'success' => true, 'total' => (int)$row['total']];
    }
    
    include_once __DIR__ . '/../auth/get_me.php';
    
    // Validate session
    $userResponse = getCurrentUser($DB_CONN, $sessionToken);
    if ($userResponse['status'] !== 200) {
        return $userResponse;
    }
    
    $userId = $userResponse['user']['id_user'];
    
    // Get count for this user
    $stmt = $DB_CONN->prepare('SELECT COUNT(*) as total FROM Penitipan WHERE id_user = ?');
    if (!$stmt) {
        return ['status' => 500, 'success' => false, 'error' => 'Database error'];
    }
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    
    return ['status' => 200, 'success' => true, 'total' => (int)$row['total']];
}

function handleGetPenitipan(mysqli $DB_CONN, string $sessionToken, ?int $penitipanId = null, ?string $status = null): array {
    include_once __DIR__ . '/../auth/get_me.php';
    
    // Validate session
    $userResponse = getCurrentUser($DB_CONN, $sessionToken);
    if ($userResponse['status'] !== 200) {
        return $userResponse;
    }
    
    $userId = $userResponse['user']['id_user'];
    
    if ($penitipanId !== null) {
        // Get specific penitipan
        $stmt = $DB_CONN->prepare('
            SELECT p.*, pet.nama_pet, pet.jenis_pet, pk.nama_paket, pk.harga_per_hari
            FROM Penitipan p
            LEFT JOIN Pet pet ON p.id_pet = pet.id_pet
            LEFT JOIN Paket_Kamar pk ON p.id_paket = pk.id_paket
            WHERE p.id_penitipan = ? AND p.id_user = ?
        ');
        if (!$stmt) {
            return ['status' => 500, 'success' => false, 'error' => 'Database error'];
        }
        $stmt->bind_param('ii', $penitipanId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $penitipan = $result->fetch_assoc();
        $stmt->close();
        
        if (!$penitipan) {
            return ['status' => 404, 'success' => false, 'error' => 'Penitipan not found'];
        }
        
        return ['status' => 200, 'success' => true, 'penitipan' => $penitipan];
    }
    
    // Get all penitipan for user
    $sql = '
        SELECT p.*, pet.nama_pet, pet.jenis_pet, pk.nama_paket, pk.harga_per_hari
        FROM Penitipan p
        LEFT JOIN Pet pet ON p.id_pet = pet.id_pet
        LEFT JOIN Paket_Kamar pk ON p.id_paket = pk.id_paket
        WHERE p.id_user = ?
    ';
    
    if ($status !== null) {
        $sql .= ' AND p.status_penitipan = ?';
    }
    
    $sql .= ' ORDER BY p.tgl_checkin DESC';
    
    $stmt = $DB_CONN->prepare($sql);
    if (!$stmt) {
        return ['status' => 500, 'success' => false, 'error' => 'Database error'];
    }
    
    if ($status !== null) {
        $stmt->bind_param('is', $userId, $status);
    } else {
        $stmt->bind_param('i', $userId);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $penitipanList = [];
    while ($row = $result->fetch_assoc()) {
        $penitipanList[] = $row;
    }
    $stmt->close();
    
    return ['status' => 200, 'success' => true, 'penitipan' => $penitipanList];
}

function handleGetPenitipanAktif(mysqli $DB_CONN, string $sessionToken): array {
    return handleGetPenitipan($DB_CONN, $sessionToken, null, 'aktif');
}
