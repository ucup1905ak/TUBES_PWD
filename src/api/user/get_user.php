<?php

// 3. /api/user/id → ambil data user tertentu

header('Content-Type: application/json');

// This handler is called from /api/index.php, which provides the $DB_CONN variable.    
$id_user = isset($id) ? (int) $id : 0;
if ($id_user <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid user ID']);
    exit;
}


$stmt = $DB_CONN->prepare('SELECT id_user, nama_lengkap, email, no_telp, alamat, foto_profil FROM `User` WHERE id_user = ? LIMIT 1');

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit;
}
$stmt->bind_param('i', $id_user);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if ($user && !empty($user['foto_profil'])) {
    // Base64 encode the BLOB data so it can be sent in JSON
    $user['foto_profil'] = base64_encode($user['foto_profil']);
}

if (!$user) {
    http_response_code(404);
    echo json_encode(['success' => false, 'error' => 'User not found']);
    exit;
}
echo json_encode(['success' => true, 'user' => $user]);
exit;

//response example 
/*
{
    "success": true,
    "user": {
        "id_user": 1,
        "nama_lengkap": "John Doe",
        "email": "john.doe@example.com",
        "no_telp": "08123456789",
        "alamat": "Jl. Merdeka No. 123, Jakarta"

        */