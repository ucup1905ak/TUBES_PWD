<?php

/**
 * Get pets for the authenticated user
 */
function handleGetHewan(mysqli $DB_CONN, string $sessionToken, ?int $petId = null): array {
    include_once __DIR__ . '/../auth/get_me.php';
    
    // Validate session
    $userResponse = getCurrentUser($DB_CONN, $sessionToken);
    if ($userResponse['status'] !== 200) {
        return $userResponse;
    }
    
    $userId = $userResponse['user']['id_user'];
    
    if ($petId !== null) {
        // Get specific pet
        $stmt = $DB_CONN->prepare('
            SELECT id_pet, id_user, nama_pet, jenis_pet, ras, umur, jenis_kelamin, warna, alergi, catatan_medis, foto_pet 
            FROM Pet 
            WHERE id_pet = ? AND id_user = ?
        ');
        if (!$stmt) {
            return ['status' => 500, 'success' => false, 'error' => 'Database error'];
        }
        $stmt->bind_param('ii', $petId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $pet = $result->fetch_assoc();
        $stmt->close();
        
        if (!$pet) {
            return ['status' => 404, 'success' => false, 'error' => 'Pet not found'];
        }
        
        return ['status' => 200, 'success' => true, 'pet' => $pet];
    }
    
    // Get all pets for user
    $stmt = $DB_CONN->prepare('
        SELECT id_pet, id_user, nama_pet, jenis_pet, ras, umur, jenis_kelamin, warna, alergi, catatan_medis, foto_pet 
        FROM Pet 
        WHERE id_user = ?
        ORDER BY id_pet DESC
    ');
    if (!$stmt) {
        return ['status' => 500, 'success' => false, 'error' => 'Database error'];
    }
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $pets = [];
    while ($row = $result->fetch_assoc()) {
        $pets[] = $row;
    }
    $stmt->close();
    
    return ['status' => 200, 'success' => true, 'pets' => $pets];
}
