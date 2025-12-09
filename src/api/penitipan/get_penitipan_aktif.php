<?php
/**
 * GET /api/penitipan/aktif
 * Returns active penitipan for the authenticated user
 */

include_once __DIR__ . '/../auth/get_me.php';

$sessionToken = $GLOBALS['session_token'] ?? null;

if (empty($sessionToken)) {
    http_response_code(401);
    echo json_encode([
        'status' => 401,
        'success' => false,
        'error' => 'Authorization required'
    ]);
    return;
}

$conn = $GLOBALS['DB_CONN'];

// Validate session and get user
$userResponse = getCurrentUser($conn, $sessionToken);
if ($userResponse['status'] !== 200) {
    http_response_code($userResponse['status']);
    echo json_encode($userResponse);
    return;
}

$userId = $userResponse['user']['id_user'];

// Get active penitipan (status aktif or berlangsung, not cancelled)
$stmt = $conn->prepare('
    SELECT 
        p.id_penitipan,
        p.id_pet,
        p.tgl_checkin,
        p.tgl_checkout,
        p.nama_paket,
        p.layanan,
        p.total_biaya,
        p.durasi,
        p.status_penitipan,
        pet.nama_pet AS nama_pet,
        pet.jenis_pet AS jenis_pet,
        pet.ras AS ras
    FROM Penitipan p
    LEFT JOIN Pet pet ON pet.id_pet = p.id_pet
    WHERE p.id_user = ? 
        AND p.status_penitipan IN ("aktif", "berlangsung", "pending")
        AND (p.status IS NULL OR p.status = "active")
        AND p.deleted_at IS NULL
    ORDER BY p.tgl_checkin DESC
');

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        'status' => 500,
        'success' => false,
        'error' => 'Database error',
        'details' => $conn->error
    ]);
    return;
}

$stmt->bind_param('i', $userId);
$stmt->execute();
$result = $stmt->get_result();

$penitipan = [];
while ($row = $result->fetch_assoc()) {
    // Decode layanan JSON
    if (!empty($row['layanan'])) {
        $row['layanan'] = json_decode($row['layanan'], true);
    } else {
        $row['layanan'] = [];
    }
    
    $penitipan[] = $row;
}

$stmt->close();

http_response_code(200);
echo json_encode([
    'status' => 200,
    'success' => true,
    'penitipan' => $penitipan
]);
