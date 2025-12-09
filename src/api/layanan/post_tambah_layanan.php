<?php
/**
 * POST /api/layanan/tambah
 * Add a new service (admin only)
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

$nama_layanan = trim($input['nama_layanan'] ?? '');
$deskripsi = trim($input['deskripsi'] ?? '');
$harga = floatval($input['harga'] ?? 0);

if (empty($nama_layanan) || $harga <= 0) {
    http_response_code(400);
    echo json_encode([
        'status' => 400,
        'success' => false,
        'error' => 'Validation failed',
        'details' => 'Nama layanan and harga are required'
    ]);
    return;
}

$stmt = $conn->prepare("INSERT INTO Layanan (nama_layanan, deskripsi, harga) VALUES (?, ?, ?)");
$stmt->bind_param('ssd', $nama_layanan, $deskripsi, $harga);

if ($stmt->execute()) {
    http_response_code(201);
    echo json_encode([
        'status' => 201,
        'success' => true,
        'message' => 'Service added successfully',
        'id_layanan' => $conn->insert_id
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
