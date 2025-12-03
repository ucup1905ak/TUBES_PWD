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

    // =============================
    // GET SPECIFIC PENITIPAN
    // =============================
    if ($penitipanId !== null) {
        $stmt = $DB_CONN->prepare('
            SELECT 
                p.id_penitipan,
                p.tgl_checkin,
                p.tgl_checkout,
                p.kamar,
                p.layanan,
                p.total_biaya,
                p.durasi,
                pet.nama_pet,
                pet.jenis_pet,
                pet.ras
            FROM Penitipan p
            LEFT JOIN Pet pet ON pet.id_pet = p.id_pet
            WHERE p.id_penitipan = ? AND p.id_user = ?
        ');
        $stmt->bind_param('ii', $penitipanId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $item = $result->fetch_assoc();
        $stmt->close();

        if (!$item) {
            return ['status' => 404, 'success' => false, 'error' => 'Penitipan not found'];
        }

        // Decode layanan JSON
        if (!empty($item['layanan'])) {
            $item['layanan'] = json_decode($item['layanan'], true);
        } else {
            $item['layanan'] = [];
        }

        return ['status' => 200, 'success' => true, 'penitipan' => $item];
    }

    // =============================
    // GET ALL PENITIPAN BY USER
    // =============================
    $sql = '
        SELECT 
            p.id_penitipan,
            p.tgl_checkin,
            p.tgl_checkout,
            p.kamar,
            p.layanan,
            p.total_biaya,
            p.durasi,
            pet.nama_pet,
            pet.jenis_pet,
            pet.ras
        FROM Penitipan p
        LEFT JOIN Pet pet ON pet.id_pet = p.id_pet
        WHERE p.id_user = ?
    ';

    if ($status !== null) {
        $sql .= ' AND p.status_penitipan = ?';
    }

    $sql .= ' ORDER BY p.tgl_checkin DESC';

    $stmt = $DB_CONN->prepare($sql);

    if ($status !== null) {
        $stmt->bind_param('is', $userId, $status);
    } else {
        $stmt->bind_param('i', $userId);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $list = [];
    while ($row = $result->fetch_assoc()) {
        // Decode layanan JSON
        if (!empty($row['layanan'])) {
            $row['layanan'] = json_decode($row['layanan'], true);
        } else {
            $row['layanan'] = [];
        }

        $list[] = $row;
    }

    $stmt->close();

    return ['status' => 200, 'success' => true, 'penitipan' => $list];
}

function handleGetPenitipanAktif(mysqli $DB_CONN, string $sessionToken): array {
    return handleGetPenitipan($DB_CONN, $sessionToken, null, 'aktif');
}
