<?php
/**
 * POST /api/paket/tambah
 * Add a new room package (admin only)
 */

$user = $GLOBALS['user'] ?? null;

if (!$user || $user['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode([
        'status' => 403,
        'success' => false,
        'error' => 'Forbidden',
        'details' => 'Admin access required'
    ]);
    return;
}

$conn = $GLOBALS['DB_CONN'];

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (stripos($contentType, 'application/json') !== false) {
    $input = json_decode(file_get_contents('php://input'), true);
} else {
    $input = $_POST;
}

$nama_paket = trim($input['nama_paket'] ?? '');
$deskripsi = trim($input['deskripsi'] ?? '');
$harga_per_hari = floatval($input['harga_per_hari'] ?? 0);
$fasilitas = trim($input['fasilitas'] ?? '');

if (empty($nama_paket) || $harga_per_hari <= 0) {
    http_response_code(400);
    echo json_encode([
        'status' => 400,
        'success' => false,
        'error' => 'Validation failed',
        'details' => 'Nama paket and harga_per_hari are required'
    ]);
    return;
}

$stmt = $conn->prepare("INSERT INTO Paket_Kamar (nama_paket, deskripsi, harga_per_hari, fasilitas) VALUES (?, ?, ?, ?)");
$stmt->bind_param('ssds', $nama_paket, $deskripsi, $harga_per_hari, $fasilitas);

if ($stmt->execute()) {
    http_response_code(201);
    echo json_encode([
        'status' => 201,
        'success' => true,
        'message' => 'Package added successfully',
        'id_paket' => $conn->insert_id
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 500,
        'success' => false,
        'error' => 'Database error',
        'details' => $conn->error
    ]);
}

$stmt->close();
