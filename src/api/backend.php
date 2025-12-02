<?php
require_once __DIR__ . '/../routing/router.php';
include __DIR__ . '/../config/database_setup.php';
class BACKEND{
    private $router;
    private $DB_CONN;
    public function connectDB() : void {
        // Load environment variables
        $env = loadEnvToArray(__DIR__ . '/../../.env');
        
        // Get database configuration from environment
        $dbHost = $_ENV['DB_HOST'] ?? $env['DB_HOST'] ?? 'localhost';
        $dbPort = (int)($_ENV['DB_PORT'] ?? $env['DB_PORT'] ?? 3306);
        $dbUser = $_ENV['DB_USER'] ?? $env['DB_USER'] ?? 'root';
        $dbPass = $_ENV['DB_PASSWORD'] ?? $env['DB_PASSWORD'] ?? '';
        $dbName = $_ENV['DB_NAME'] ?? $env['DB_NAME'] ?? 'pwd';
        
        // Check if Azure MySQL (SSL required)
        $useSSL = $_ENV['DB_USE_SSL'] ?? $env['DB_USE_SSL'] ?? 'false';
        $sslCert = $_ENV['DB_SSL_CERT'] ?? $env['DB_SSL_CERT'] ?? '';
        
        $con = mysqli_init();
        
        if ($useSSL === 'true' && !empty($sslCert)) {
            // Azure MySQL with SSL
            $certPath = __DIR__ . '/' . $sslCert;
            if (file_exists($certPath)) {
                mysqli_ssl_set($con, NULL, NULL, $certPath, NULL, NULL);
                mysqli_real_connect($con, $dbHost, $dbUser, $dbPass, $dbName, $dbPort, NULL, MYSQLI_CLIENT_SSL);
            } else {
                // Fallback without SSL if cert not found
                mysqli_real_connect($con, $dbHost, $dbUser, $dbPass, $dbName, $dbPort);
            }
        } else {
            // Standard MySQL connection (localhost/cPanel)
            mysqli_real_connect($con, $dbHost, $dbUser, $dbPass, $dbName, $dbPort);
        }
        
        if (!$con) {
            error_log('Database connection failed: ' . mysqli_connect_error());
            http_response_code(500);
            echo json_encode(['error' => 'Database connection failed']);
            exit;
        }
        
        $this->DB_CONN = $con;
    }
    public function setupDatabase(): void{
        // echo '<br>setting up database.';
        $DB = new Database_setup($this->DB_CONN);
        $DB->initializeTables();
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

             
        $this->router->add("/api/hewan", function() :void {
            $this->getHewan();
        });
        $this->router->add("/api/penitipan/jumlah", function(): void {
            $this->getJumlahPenitipan();
        });
        $this->router->add("/api/user/{id}", function(): void {
            $this->getUser();
        });
        $this->router->add("/api/auth/me", function(): void {
            $this->getMe();
        });
        //POST endpoints
        $this->router->add("/api/auth/register", function(): void {
            $this->postRegister();
        }); 
        $this->router->add("/api/hewan/tambah", function(): void {
            $this->postTambahHewan();
        });
        $this->router->add("/api/penitipan/tambah", function(): void {
            $this->postTambahPenitipan();
        });

        $this->router->add("/api/auth/login", function(): void {
            $this->postLogin();
        });

    }
    public function run($path): void{
               
        header('Content-Type: application/json');
        $this->router->dispatch($path);
    }
   
    private function getHewan() {
        include __DIR__ . '/hewan/get_hewan.php';
    }
    private function getJumlahPenitipan() {
        include __DIR__ . '/penitipan/get_jumlah_penitipan.php';
    }
    private function getUser() {
        include __DIR__ . '/user/get_user.php';
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

        // Call the getCurrentUser function
        $response = getCurrentUser($this->DB_CONN, $sessionToken);

        // Set HTTP status code and output JSON response
        http_response_code($response['status']);
        header('Content-Type: application/json');
        echo json_encode($response);
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
        include __DIR__ . '/hewan/post_tambah_hewan.php';
    }
    private function postTambahPenitipan() {
        include __DIR__ . '/penitipan/post_tambah_penitipan.php';
    }
    private function postLogin() {
        include __DIR__ . '/auth/post_login.php';

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

        // Set the HTTP response code
        http_response_code($response['status']);

        // Set the Content-Type header
        header('Content-Type: application/json');

        // Send the JSON response
        echo json_encode($response);

        exit;
    }

}


?>