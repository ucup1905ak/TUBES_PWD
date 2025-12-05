<?php
/**
 * GET /api/admin/users
 * Returns all users (admin only)
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

// Get all users
$stmt = $conn->prepare('
    SELECT 
        id_user, 
        nama_lengkap, 
        email, 
        no_telp, 
        alamat, 
        role
    FROM User
    ORDER BY id_user DESC
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

$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

$stmt->close();

http_response_code(200);
echo json_encode([
    'status' => 200,
    'success' => true,
    'users' => $users
]);
