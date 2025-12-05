<?php
/**
 * GET /api/paket
 * Returns all available room packages
 */

$conn = $GLOBALS['DB_CONN'];

$result = $conn->query("SELECT * FROM Paket_Kamar ORDER BY harga_per_hari ASC");

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

$paket = [];
while ($row = $result->fetch_assoc()) {
    $paket[] = [
        'id_paket' => (int)$row['id_paket'],
        'nama_paket' => $row['nama_paket'],
        'deskripsi' => $row['deskripsi'],
        'harga_per_hari' => (float)$row['harga_per_hari'],
        'fasilitas' => $row['fasilitas']
    ];
}

http_response_code(200);
echo json_encode([
    'status' => 200,
    'success' => true,
    'paket' => $paket
]);
