<?php

/**
 * Validates user registration input data and uploaded file.
 *
 * @param array $input The input data from the request containing:
 *                      - username (string): User's full name (required, max 100 chars)
 *                      - email (string): User's email address (required)
 *                      - password (string): SHA-256 hashed password (required)
 *                      - telepon (string): Phone number (required, max 15 chars)
 *                      - alamat (string): Address (required)
 *                      - role (string): User role, 'user' or 'admin' (optional, default: 'user')
 * @param array $file The $_FILES array containing optional 'foto' profile photo upload
 * 
 * @return array Array of validation error messages. Empty array if validation passes.
 */
function validateRegisterInput(array $input, array $file): array {
    $errors = [];

    $username = trim($input['username'] ?? '');
    $email = trim($input['email'] ?? '');
    $password = $input['password'] ?? '';
    $no_telp = trim($input['telepon'] ?? '');
    $alamat = trim($input['alamat'] ?? '');
    $role = trim($input['role'] ?? 'user');

    // Validate role
    if (!in_array($role, ['user', 'admin'])) {
        $errors[] = 'Invalid role. Must be "user" or "admin".';
    }

    if (empty($username)) {
        $errors[] = 'Username is required.';
    } elseif (strlen($username) > 100) {
        $errors[] = 'Username must not exceed 100 characters.';
    }

    if (empty($password)) {
        $errors[] = 'Password is required.';
    }

    if (empty($no_telp)) {
        $errors[] = 'Phone number is required.';
    } elseif (strlen($no_telp) > 15) {
        $errors[] = 'Phone number must not exceed 15 characters.';
    }

    if (empty($alamat)) {
        $errors[] = 'Address is required.';
    }

    // File validation
    if (isset($file['foto']) && $file['foto']['error'] === UPLOAD_ERR_OK) {
        $max_file_size = 16 * 1024 * 1024;
        if ($file['foto']['size'] > $max_file_size) {
            $errors[] = 'File is too large. Maximum size is 16MB.';
        }

        // Get file type from extension as fallback for mime_content_type
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
        $allowed_mime_types = ['image/jpeg', 'image/png', 'image/gif'];
        
        $file_extension = strtolower(pathinfo($file['foto']['name'], PATHINFO_EXTENSION));
        
        // Check extension first
        if (!in_array($file_extension, $allowed_extensions)) {
            $errors[] = 'Invalid file type. Only JPG, PNG, and GIF are allowed.';
        } else {
            // Try to get MIME type using finfo if available
            if (function_exists('finfo_open')) {
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $file_type = finfo_file($finfo, $file['foto']['tmp_name']);
                finfo_close($finfo);
                
                if (!in_array($file_type, $allowed_mime_types)) {
                    $errors[] = 'Invalid file type. Only JPG, PNG, and GIF are allowed.';
                }
            }
            // If finfo is not available, rely on extension check only
        }
    } elseif (isset($file['foto']) && $file['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
        $errors[] = 'An error occurred during file upload.';
    }

    return $errors;
}

/**
 * Reads and returns the binary data from an uploaded profile photo.
 *
 * @param array $file The $_FILES array containing the 'foto' upload
 * @param array &$errors Reference to errors array, will be modified if file read fails
 * 
 * @return string|null Binary image data on success, null if no file or on error
 */
function getProfilePhotoData(array $file, array &$errors): ?string {
    if (isset($file['foto']) && $file['foto']['error'] === UPLOAD_ERR_OK && empty($errors)) {
        $foto_profil_data = file_get_contents($file['foto']['tmp_name']);
        if ($foto_profil_data === false) {
            $errors[] = 'Failed to read the uploaded file.';
            return null;
        }
        return $foto_profil_data;
    }
    return null;
}

/**
 * Checks if a user with the given email or username already exists in the database.
 *
 * @param mysqli $DB_CONN Active MySQL database connection
 * @param string $email Email address to check
 * @param string $username Username (nama_lengkap) to check
 * 
 * @return bool True if user exists, false otherwise
 * 
 * @throws Exception If database query fails
 */
function userExists(mysqli $DB_CONN, string $email, string $username): bool {
    $stmt = $DB_CONN->prepare('SELECT id_user FROM `User` WHERE email = ? OR nama_lengkap = ? LIMIT 1');
    if (!$stmt) {
        throw new Exception('Database prepare statement failed on user check.');
    }
    $stmt->bind_param('ss', $email, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result === false) {
        $stmt->close();
        throw new Exception('Database error during user existence check: ' . $DB_CONN->error);
    }
    $exists = $result->num_rows > 0;
    $stmt->close();
    return $exists;
}

/**
 * Inserts a new user into the database.
 *
 * @param mysqli $DB_CONN Active MySQL database connection
 * @param string $username User's full name (stored as nama_lengkap)
 * @param string $email User's email address
 * @param string $no_telp User's phone number
 * @param string $alamat User's address
 * @param string $password SHA-256 hashed password
 * @param string|null $foto_profil_data Binary image data for profile photo, or null
 * @param string $role User role: 'user' or 'admin' (default: 'user')
 * 
 * @return int The newly created user's ID
 * 
 * @throws Exception If database insert fails
 */
function insertUser(
    mysqli $DB_CONN,
    string $username,
    string $email,
    string $no_telp,
    string $alamat,
    string $password,
    ?string $foto_profil_data,
    string $role = 'user'
): int {
    $stmt_insert = $DB_CONN->prepare(
        'INSERT INTO `User` (nama_lengkap, email, no_telp, alamat, password, foto_profil, role) VALUES (?, ?, ?, ?, ?, ?, ?)'
    );
    if (!$stmt_insert) {
        throw new Exception('Database prepare statement failed on insert.');
    }
    $null = NULL;
    $stmt_insert->bind_param('sssssbs', $username, $email, $no_telp, $alamat, $password, $null, $role);
    $stmt_insert->send_long_data(5, $foto_profil_data);
    if ($stmt_insert->execute()) {
        $new_user_id = $stmt_insert->insert_id;
        $stmt_insert->close();
        return $new_user_id;
    } else {
        $error = $stmt_insert->error;
        $stmt_insert->close();
        throw new Exception('Failed to execute insert statement: ' . $error);
    }
}

/**
 * Main handler for user registration API endpoint.
 * 
 * Validates input, checks for existing users, and creates new user account.
 *
 * @param mysqli $DB_CONN Active MySQL database connection
 * @param array $input Request input data containing user registration fields:
 *                     - username (string): Required, maps to nama_lengkap
 *                     - email (string): Required, must be unique
 *                     - password (string): Required, SHA-256 hashed
 *                     - telepon (string): Required, maps to no_telp
 *                     - alamat (string): Required
 *                     - role (string): Optional, 'user' or 'admin'
 * 
 * @return array Response array with:
 *               - status (int): HTTP status code (201, 400, 409, 500)
 *               - success (bool): Whether registration succeeded
 *               - message (string): Success message (on success)
 *               - user_id (int): New user ID (on success)
 *               - error (string): Error message (on failure)
 *               - details (array): Validation errors (on validation failure)
 * 
 * @example
 * // Success response (201):
 * [
 *     'status' => 201,
 *     'success' => true,
 *     'message' => 'User registered successfully.',
 *     'user_id' => 1
 * ]
 * 
 * // Validation error response (400):
 * [
 *     'status' => 400,
 *     'success' => false,
 *     'error' => 'Input validation failed',
 *     'details' => ['Username is required.']
 * ]
 */
function handleRegister(mysqli $DB_CONN, array $input): array {
    $errors = validateRegisterInput($input, $_FILES);

    $username = trim($input['username'] ?? '');
    $email = trim($input['email'] ?? '');
    $hashed_password = $input['password'] ?? '';
    $no_telp = trim($input['telepon'] ?? '');
    $alamat = trim($input['alamat'] ?? '');
    $role = trim($input['role'] ?? 'user');

    $foto_profil_data = getProfilePhotoData($_FILES, $errors);

    if (!empty($errors)) {
        return [
            'status' => 400,
            'success' => false,
            'error' => 'Input validation failed',
            'details' => $errors
        ];
    }

    try {
        if (userExists($DB_CONN, $email, $username)) {
            return [
                'status' => 409,
                'success' => false,
                'error' => 'A user with this email or username already exists.'
            ];
        }


        $new_user_id = insertUser(
            $DB_CONN,
            $username,
            $email,
            $no_telp,
            $alamat,
            $hashed_password,
            $foto_profil_data,
            $role
        );

        return [
            'status' => 201,
            'success' => true,
            'message' => 'User registered successfully.',
            'user_id' => $new_user_id
        ];
    } catch (Exception $e) {
        return [
            'status' => 500,
            'success' => false,
            'error' => 'An unexpected error occurred.',
            'details' => $e->getMessage()
        ];
    }
}
