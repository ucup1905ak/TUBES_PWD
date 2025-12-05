<?php
/**
 * GET /api/admin/dashboard
 * Returns admin dashboard statistics
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

// Get total users
$totalUsers = 0;
$result = $conn->query("SELECT COUNT(*) as count FROM User");
if ($result) {
    $row = $result->fetch_assoc();
    $totalUsers = (int)$row['count'];
}

// Get total pets
$totalPet = 0;
$result = $conn->query("SELECT COUNT(*) as count FROM Pet");
if ($result) {
    $row = $result->fetch_assoc();
    $totalPet = (int)$row['count'];
}

// Get total penitipan
$totalPenitipan = 0;
$result = $conn->query("SELECT COUNT(*) as count FROM Penitipan");
if ($result) {
    $row = $result->fetch_assoc();
    $totalPenitipan = (int)$row['count'];
}

// Get total income from penitipan
$totalIncome = 0;
$result = $conn->query("SELECT SUM(total_biaya) as total FROM Penitipan WHERE status_penitipan IN ('selesai', 'aktif')");
if ($result) {
    $row = $result->fetch_assoc();
    $totalIncome = (float)($row['total'] ?? 0);
}

http_response_code(200);
echo json_encode([
    'status' => 200,
    'success' => true,
    'totalUsers' => $totalUsers,
    'totalPet' => $totalPet,
    'totalPenitipan' => $totalPenitipan,
    'totalIncome' => $totalIncome
]);
