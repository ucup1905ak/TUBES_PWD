<?php
/**
 * GET /api/admin/user/{id}/pets
 * Returns all pets for a specific user
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

// Get all pets for the user
$stmt = $DB_CONN->prepare('
    SELECT 
        id_pet,
        nama_pet,
        jenis_pet,
        ras,
        umur,
        jenis_kelamin,
        warna,
        alergi,
        catatan_medis
    FROM Pet
    WHERE id_user = ?
    ORDER BY nama_pet ASC
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

$pets = [];
while ($row = $result->fetch_assoc()) {
    $pets[] = $row;
}

$stmt->close();

http_response_code(200);
echo json_encode([
    'status' => 200,
    'success' => true,
    'pets' => $pets
]);
