<?php
/**
 * GET /api/admin/user/{id}/penitipan
 * Returns all penitipan (including history) for a specific user
 * Admin only
 */

$user = $GLOBALS['user'] ?? null;
$DB_CONN = $GLOBALS['DB_CONN'] ?? null;
$userId = $GLOBALS['target_user_id'] ?? null;

if (!$user || !$DB_CONN || !$userId) {
    http_response_code(500);
    echo json_encode([
        'status' => 500,
        'success' => false,
        'error' => 'Missing required globals'
    ]);
    exit;
}

// Verify admin role
if ($user['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'status' => 403,
        'success' => false,
        'error' => 'Admin access required'
    ]);
    exit;
}

// Get all penitipan for the user (including cancelled/deleted)
$stmt = $DB_CONN->prepare('
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
        p.status,
        p.deleted_at,
        pet.nama_pet AS nama_pet,
        pet.jenis_pet AS jenis_pet
    FROM Penitipan p
    LEFT JOIN Pet pet ON pet.id_pet = p.id_pet
    WHERE p.id_user = ?
    ORDER BY p.tgl_checkin DESC
');

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        'status' => 500,
        'success' => false,
        'error' => 'Database error',
        'details' => $DB_CONN->error
    ]);
    exit;
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
