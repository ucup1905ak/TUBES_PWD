<?php
/**
 * GET /api/layanan
 * Returns all available services
 */

$conn = $GLOBALS['DB_CONN'];

$result = $conn->query("SELECT * FROM Layanan ORDER BY nama_layanan ASC");

if (!$result) {
    http_response_code(500);
    echo json_encode([
        'status' => 500,
        'success' => false,
        'error' => 'Database error',
        'details' => $conn->error
    ]);
    return;
}

$layanan = [];
while ($row = $result->fetch_assoc()) {
    $layanan[] = [
        'id_layanan' => (int)$row['id_layanan'],
        'nama_layanan' => $row['nama_layanan'],
        'deskripsi' => $row['deskripsi'],
        'harga' => (float)$row['harga']
    ];
}

http_response_code(200);
echo json_encode([
    'status' => 200,
    'success' => true,
    'layanan' => $layanan
]);
