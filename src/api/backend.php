<?php
// Set JSON header and error handling for API responses
header('Content-Type: application/json; charset=utf-8');

// Ensure errors are returned as JSON, not HTML
if (php_sapi_name() !== 'cli') {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

require_once __DIR__ . '/../routing/router.php';
include __DIR__ . '/../config/database_setup.php';
class BACKEND{
    private $router;
    private $DB_CONN;
    public function connectDB($DB_HOST,
                                    $DB_PORT,
                                    $DB_USER,
                                    $DB_PASSWORD,
                                    $DB_NAME
                                    ) : void {
                                        // echo 'connecting to db';
        $con = new mysqli($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME, $DB_PORT);

        if ($con->connect_errno) {
            die("Connection failed: " . $con->connect_error);
        }

        $this->DB_CONN = $con;
        $setup = new Database_setup($this->DB_CONN);
        $setup->initializeTables();
    }
    public function __construct($router){
        $this->router = $router;
        //GET endpoints
        $this->router->add("/api", function() :void {
            http_response_code(200);
            $test = ["halo" => true, "status"=> true];
            echo json_encode($test);
        });
        // Test endpoint for registering a new account with username and password from path
        $this->router->add("/api/test/register", function(): void {
            include_once __DIR__ . '/auth/post_register.php';

            // Accept JSON payload or standard form-encoded POST
            $input = null;
            $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
            if (stripos($contentType, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            $input = json_decode($raw, true);
            } else {
            $input = $_POST;
            }

            // Provide default values if not set
            $testUser = [
            'username' => $input['username'] ?? '',
            'email' => ($input['username'] ?? '') . '@example.com',
            'password' => $input['password'] ?? '',
            'confirmPassword' => $input['password'] ?? '',
            'telepon' => $input['telepon'] ?? '081234567890',
            'alamat' => $input['alamat'] ?? 'Jl. Uji Coba No. 123'
            ];

            $response = handleRegister($this->DB_CONN, $testUser);
            http_response_code($response['status']);
            header('Content-Type: application/json');
            echo json_encode($response);
        });

        // GET endpoint for current user info
        $this->router->add("/api/auth/me", function(): void {
            $this->getMe();
        });
        $this->router->add("/api/auth/me/photo", function(): void {
            $this->getMePhoto();
        });
        //POST endpoints
        $this->router->add("/api/auth/register", function(): void {
            $this->postRegister();
        });
        $this->router->add("/api/auth/login", function(): void {
            $this->postLogin();
        });

        // User routes - specific paths BEFORE parameterized {id}
        $this->router->add("/api/user/update", function(): void {
            $this->postUpdateUser();
        });
        $this->router->add("/api/user/delete", function(): void {
            $this->deleteUser();
        });
        // Parameterized user route MUST come after specific routes
        $this->router->add("/api/user/{id}", function($id): void {
            $this->getUser($id);
        });

        // GET endpoints for hewan - specific routes BEFORE parameterized routes
        $this->router->add("/api/hewan", function() :void {
            $this->getHewan();
        });
        // Hewan routes - specific paths BEFORE parameterized {id}
        $this->router->add("/api/hewan/tambah", function(): void {
            $this->postTambahHewan();
        });
        $this->router->add("/api/hewan/update/{id}", function($id): void {
            $this->postUpdateHewan($id);
        });
        $this->router->add("/api/hewan/delete/{id}", function($id): void {
            $this->deleteHewan($id);
        });
        // Parameterized hewan route MUST come after specific routes
        $this->router->add("/api/hewan/{id}", function($id) :void {
            $this->getHewanById($id);
        });
        
        // GET endpoints for penitipan - specific routes BEFORE parameterized routes
        $this->router->add("/api/penitipan/jumlah", function(): void {
            $this->getJumlahPenitipan();
        });
        $this->router->add("/api/penitipan/aktif", function(): void {
            $this->getPenitipanAktif();
        });
        // Penitipan routes - specific paths BEFORE parameterized {id}
        $this->router->add("/api/penitipan/tambah", function(): void {
            $this->postTambahPenitipan();
        });
        $this->router->add("/api/penitipan/update/{id}", function($id): void {
            $this->postUpdatePenitipan($id);
        });
        $this->router->add("/api/penitipan/delete/{id}", function($id): void {
            $this->deletePenitipan($id);
        });
        // Parameterized penitipan route MUST come after specific routes
        $this->router->add("/api/penitipan/{id}", function($id): void {
            $this->getPenitipanById($id);
        });
        $this->router->add("/api/penitipan", function(): void {
            $this->getPenitipan();
        });

    }
    public function run($path): void{
        $this->router->dispatch($path);
    }
    
    private function getSessionToken(): string {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        
        $sessionToken = '';
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $sessionToken = $matches[1];
        }
        
        if (empty($sessionToken)) {
            $sessionToken = $_GET['session_token'] ?? $_POST['session_token'] ?? '';
        }
        
        return $sessionToken;
    }
    
    private function getInput(): array {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            return json_decode($raw, true) ?? [];
        }
        return $_POST;
    }
   
    private function getHewan() {
        include_once __DIR__ . '/hewan/get_hewan.php';
        
        $sessionToken = $this->getSessionToken();
        if (empty($sessionToken)) {
            http_response_code(401);
            echo json_encode(['status' => 401, 'error' => 'Authorization required.']);
            exit;
        }
        
        $response = handleGetHewan($this->DB_CONN, $sessionToken);
        http_response_code($response['status']);
        echo json_encode($response);
        exit;
    }
    
    private function getHewanById($id) {
        include_once __DIR__ . '/hewan/get_hewan.php';
        
        $sessionToken = $this->getSessionToken();
        if (empty($sessionToken)) {
            http_response_code(401);
            echo json_encode(['status' => 401, 'error' => 'Authorization required.']);
            exit;
        }
        
        $response = handleGetHewan($this->DB_CONN, $sessionToken, (int)$id);
        http_response_code($response['status']);
        echo json_encode($response);
        exit;
    }
    
    private function getJumlahPenitipan() {
        include_once __DIR__ . '/penitipan/get_jumlah_penitipan.php';
        
        $sessionToken = $this->getSessionToken();
        $response = handleGetJumlahPenitipan($this->DB_CONN, $sessionToken);
        http_response_code($response['status']);
        echo json_encode($response);
        exit;
    }
    
    private function getPenitipan() {
        include_once __DIR__ . '/penitipan/get_jumlah_penitipan.php';
        
        $sessionToken = $this->getSessionToken();
        if (empty($sessionToken)) {
            http_response_code(401);
            echo json_encode(['status' => 401, 'error' => 'Authorization required.']);
            exit;
        }
        
        $response = handleGetPenitipan($this->DB_CONN, $sessionToken);
        http_response_code($response['status']);
        echo json_encode($response);
        exit;
    }
    
    private function getPenitipanAktif() {
        include_once __DIR__ . '/penitipan/get_jumlah_penitipan.php';
        
        $sessionToken = $this->getSessionToken();
        if (empty($sessionToken)) {
            http_response_code(401);
            echo json_encode(['status' => 401, 'error' => 'Authorization required.']);
            exit;
        }
        
        $response = handleGetPenitipanAktif($this->DB_CONN, $sessionToken);
        http_response_code($response['status']);
        echo json_encode($response);
        exit;
    }
    
    private function getPenitipanById($id) {
        include_once __DIR__ . '/penitipan/get_jumlah_penitipan.php';
        
        $sessionToken = $this->getSessionToken();
        if (empty($sessionToken)) {
            http_response_code(401);
            echo json_encode(['status' => 401, 'error' => 'Authorization required.']);
            exit;
        }
        
        $response = handleGetPenitipan($this->DB_CONN, $sessionToken, (int)$id);
        http_response_code($response['status']);
        echo json_encode($response);
        exit;
    }
    
    private function getUser($id) {
        include_once __DIR__ . '/user/get_user.php';
        
        $userId = (int)$id;
        if ($userId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Invalid user ID']);
            exit;
        }
        
        $stmt = $this->DB_CONN->prepare('SELECT id_user, nama_lengkap, email, no_telp, alamat, foto_profil, role FROM `User` WHERE id_user = ? LIMIT 1');
        if (!$stmt) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Database error']);
            exit;
        }
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        
        if ($user && !empty($user['foto_profil'])) {
            $user['foto_profil'] = base64_encode($user['foto_profil']);
        }
        
        if (!$user) {
            http_response_code(404);
            echo json_encode(['success' => false, 'error' => 'User not found']);
            exit;
        }
        
        http_response_code(200);
        echo json_encode(['success' => true, 'user' => $user]);
        exit;
    }
    private function getMe() {
        include_once __DIR__ . '/auth/get_me.php';

        // Get Authorization header
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        
        // Extract token from "Bearer <token>" format
        $sessionToken = '';
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $sessionToken = $matches[1];
        }
        
        // If no bearer token, check query parameter or body
        if (empty($sessionToken)) {
            $sessionToken = $_GET['session_token'] ?? $_POST['session_token'] ?? '';
        }
        
        if (empty($sessionToken)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 400,
                'error' => 'Session token is required.'
            ]);
            exit;
        }

        $includePhoto = false;
        if (isset($_GET['include_photo'])) {
            $includePhoto = filter_var($_GET['include_photo'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $includePhoto = $includePhoto ?? false;
        }

        // Call the getCurrentUser function
        $response = getCurrentUser($this->DB_CONN, $sessionToken, $includePhoto);

        // Set HTTP status code and output JSON response
        http_response_code($response['status']);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
    
    private function getMePhoto() {
        include_once __DIR__ . '/auth/get_me.php';

        $sessionToken = $this->getSessionToken();
        if (empty($sessionToken)) {
            http_response_code(401);
            echo json_encode(['status' => 401, 'success' => false, 'error' => 'Authorization required.']);
            exit;
        }

        $response = getCurrentUser($this->DB_CONN, $sessionToken, true);
        if ($response['status'] !== 200) {
            http_response_code($response['status']);
            echo json_encode($response);
            exit;
        }

        $user = $response['user'] ?? [];
        $hasPhoto = $user['has_foto_profil'] ?? false;
        $photo = $user['foto_profil'] ?? null;

        http_response_code(200);
        echo json_encode([
            'status' => 200,
            'success' => true,
            'has_foto_profil' => $hasPhoto,
            'foto_profil' => $photo
        ]);
        exit;
    }
    private function postRegister() {
        include_once __DIR__ . '/auth/post_register.php';

        // Accept JSON payload or standard form-encoded POST
        $input = null;
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            $input = json_decode($raw, true);
        } else {
            $input = $_POST;
        }

        // Call the handleRegister function
        $response = handleRegister($this->DB_CONN, $input);

        // Set HTTP status code and output JSON response
        http_response_code($response['status']);
        header('Content-Type: application/json');
        echo json_encode($response);
    }
    private function postTambahHewan() {
        include_once __DIR__ . '/hewan/post_tambah_hewan.php';
        
        $sessionToken = $this->getSessionToken();
        if (empty($sessionToken)) {
            http_response_code(401);
            echo json_encode(['status' => 401, 'error' => 'Authorization required.']);
            exit;
        }
        
        $input = $this->getInput();
        $response = handleTambahHewan($this->DB_CONN, $sessionToken, $input);
        http_response_code($response['status']);
        echo json_encode($response);
        exit;
    }
    
    private function postUpdateHewan($id) {
        include_once __DIR__ . '/hewan/post_tambah_hewan.php';
        
        $sessionToken = $this->getSessionToken();
        if (empty($sessionToken)) {
            http_response_code(401);
            echo json_encode(['status' => 401, 'error' => 'Authorization required.']);
            exit;
        }
        
        $input = $this->getInput();
        $response = handleUpdateHewan($this->DB_CONN, $sessionToken, (int)$id, $input);
        http_response_code($response['status']);
        echo json_encode($response);
        exit;
    }
    
    private function deleteHewan($id) {
        include_once __DIR__ . '/hewan/post_tambah_hewan.php';
        
        $sessionToken = $this->getSessionToken();
        if (empty($sessionToken)) {
            http_response_code(401);
            echo json_encode(['status' => 401, 'error' => 'Authorization required.']);
            exit;
        }
        
        $response = handleDeleteHewan($this->DB_CONN, $sessionToken, (int)$id);
        http_response_code($response['status']);
        echo json_encode($response);
        exit;
    }
    
    private function postTambahPenitipan() {
        include_once __DIR__ . '/penitipan/post_tambah_penitipan.php';
        
        $sessionToken = $this->getSessionToken();
        if (empty($sessionToken)) {
            http_response_code(401);
            echo json_encode(['status' => 401, 'error' => 'Authorization required.']);
            exit;
        }
        
        $input = $this->getInput();
        $response = handleTambahPenitipan($this->DB_CONN, $sessionToken, $input);
        http_response_code($response['status']);
        echo json_encode($response);
        exit;
    }
    
    private function postUpdatePenitipan($id) {
        include_once __DIR__ . '/penitipan/post_tambah_penitipan.php';
        
        $sessionToken = $this->getSessionToken();
        if (empty($sessionToken)) {
            http_response_code(401);
            echo json_encode(['status' => 401, 'error' => 'Authorization required.']);
            exit;
        }
        
        $input = $this->getInput();
        $response = handleUpdatePenitipan($this->DB_CONN, $sessionToken, (int)$id, $input);
        http_response_code($response['status']);
        echo json_encode($response);
        exit;
    }
    
    private function deletePenitipan($id) {
        include_once __DIR__ . '/penitipan/post_tambah_penitipan.php';
        
        $sessionToken = $this->getSessionToken();
        if (empty($sessionToken)) {
            http_response_code(401);
            echo json_encode(['status' => 401, 'error' => 'Authorization required.']);
            exit;
        }
        
        $response = handleDeletePenitipan($this->DB_CONN, $sessionToken, (int)$id);
        http_response_code($response['status']);
        echo json_encode($response);
        exit;
    }
    private function postLogin() {
        // Prevent accidental output from included file breaking headers
        ob_start();
        include_once __DIR__ . '/auth/post_login.php';

        // Accept JSON payload or standard form-encoded POST
        $input = null;
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            $input = json_decode($raw, true);
        } else {
            $input = $_POST;
        }

        // Call the handleLogin function and get the response
        $response = handleLogin($this->DB_CONN, $input);

        // Discard any buffered output from the included file so headers can be set safely
        if (ob_get_level() > 0) {
            ob_end_clean();
        }

        // Set the HTTP response code
        http_response_code($response['status']);

        // Set the Content-Type header
        header('Content-Type: application/json');

        // Send the JSON response
        echo json_encode($response);

        exit;
    }

    private function postUpdateUser() {
        include_once __DIR__ . '/user/post_update_user.php';

        // Get Authorization header
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
        
        // Extract token from "Bearer <token>" format
        $sessionToken = '';
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $sessionToken = $matches[1];
        }
        
        if (empty($sessionToken)) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode([
                'status' => 401,
                'error' => 'Authorization required.'
            ]);
            exit;
        }

        // Accept JSON payload or standard form-encoded POST
        $input = null;
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            $input = json_decode($raw, true);
        } else {
            $input = $_POST;
        }

        // Call the handleUpdateUser function
        $response = handleUpdateUser($this->DB_CONN, $sessionToken, $input);

        http_response_code($response['status']);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    private function deleteUser() {
        include_once __DIR__ . '/auth/get_me.php';
        
        $sessionToken = $this->getSessionToken();
        if (empty($sessionToken)) {
            http_response_code(401);
            echo json_encode(['status' => 401, 'success' => false, 'error' => 'Authorization required.']);
            exit;
        }
        
        // Get current user
        $userResponse = getCurrentUser($this->DB_CONN, $sessionToken);
        if ($userResponse['status'] !== 200) {
            http_response_code($userResponse['status']);
            echo json_encode($userResponse);
            exit;
        }
        
        $userId = $userResponse['user']['id_user'];
        
        // Delete the user (cascade will handle related records)
        $stmt = $this->DB_CONN->prepare('DELETE FROM `User` WHERE id_user = ?');
        if (!$stmt) {
            http_response_code(500);
            echo json_encode(['status' => 500, 'success' => false, 'error' => 'Database error']);
            exit;
        }
        
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        
        if ($stmt->affected_rows === 0) {
            $stmt->close();
            http_response_code(404);
            echo json_encode(['status' => 404, 'success' => false, 'error' => 'User not found']);
            exit;
        }
        
        $stmt->close();
        
        http_response_code(200);
        echo json_encode(['status' => 200, 'success' => true, 'message' => 'Account deleted successfully.']);
        exit;
    }

}


?>