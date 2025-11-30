<?php
require_once __DIR__ . '/../routing/router.php';
include __DIR__ . '/../config/database_setup.php';
class BACKEND{
    private $router;
    private $DB_CONN;
    public function connectDB($DB_HOST,
                                    $DB_PORT,
                                    $DB_USER,
                                    $DB_PASSWORD,
                                    $DB_NAME,) : void {

        $this->DB_CONN = new mysqli($DB_HOST, $DB_USER, $DB_PASSWORD, $DB_NAME, $DB_PORT);
        if ($this->DB_CONN->connect_error) {  
            die("Connection failed: " . $this->DB_CONN->connect_error);
        }
    }
    public function setupDatabase(): void{
        $DB = new Database_setup($this->DB_CONN);
        $DB->initializeTables();
    }
    public function __construct($router){
        $this->router = $router;
        //GET endpoints
        $this->router->add("/api/hewan", function() :void {
            $this->getHewan();
        });
        $this->router->add("/api/penitipan/jumlah", function(): void {
            $this->getJumlahPenitipan();
        });
        $this->router->add("/api/user/{id}", function(): void {
            $this->getUser();
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
        // Call the handleLogin function and get the response
        $response = handleLogin($this->DB_CONN, $input);

        // Set the HTTP response code
        http_response_code($response['status']);

        // Set the Content-Type header
        header('Content-Type: application/json');

        // Send the JSON response
        echo json_encode($response);

        // End output buffering
        ob_end_flush();
        exit;
    }

}