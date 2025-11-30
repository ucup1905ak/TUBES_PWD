<?php
///api/auth/register

function handleRegister(mysqli $DB_CONN, array $input): array {
    // --- 1. Validate Input ---
    $nama_lengkap = trim($input['nama_lengkap'] ?? '');
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $no_telp = trim($input['no_telp'] ?? ''); // Optional
    $alamat = trim($input['alamat'] ?? '');   // Optional

    $errors = [];
    if (empty($nama_lengkap)) {
        $errors[] = 'nama_lengkap is required.';
    }
    if (empty($email)) {
        $errors[] = 'email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }
    if (empty($password)) {
        $errors[] = 'password is required.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }

    if (!empty($errors)) {
        return [
            'status' => 400,
            'success' => false,
            'error' => 'Input validation failed',
            'details' => $errors
        ];
    }

    // --- 2. Check if user already exists ---
    try {
        $stmt = $DB_CONN->prepare('SELECT id_user FROM `User` WHERE email = ? LIMIT 1');
        if (!$stmt) {
            throw new Exception('Database prepare statement failed on check.');
        }
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $stmt->close();
            return [
                'status' => 409,
                'success' => false,
                'error' => 'A user with this email address already exists.'
            ];
        }
        $stmt->close();

        // --- 3. Hash Password ---
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        if ($hashed_password === false) {
            throw new Exception('Failed to hash password.');
        }

        // --- 4. Insert New User ---
        $stmt_insert = $DB_CONN->prepare(
            'INSERT INTO `User` (nama_lengkap, email, password, no_telp, alamat) VALUES (?, ?, ?, ?, ?)'
        );
        if (!$stmt_insert) {
            throw new Exception('Database prepare statement failed on insert.');
        }
        
        $stmt_insert->bind_param('sssss', $nama_lengkap, $email, $hashed_password, $no_telp, $alamat);
        
        if ($stmt_insert->execute()) {
            $new_user_id = $stmt_insert->insert_id;  // Auto-incremented user ID
            $stmt_insert->close();
            return [
                'status' => 201,
                'success' => true,
                'message' => 'User registered successfully.',
                'user_id' => $new_user_id
            ];
        } else {
            throw new Exception('Failed to execute insert statement.');
        }

    } catch (Exception $e) {
        return [
            'status' => 500,
            'success' => false,
            'error' => 'An unexpected error occurred.',
            'details' => $e->getMessage()
        ];
    }
}


