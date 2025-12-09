<?php

// Disable HTML error display and catch errors as JSON
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set error handler for uncaught exceptions and errors
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 500,
        'success' => false,
        'error' => 'Internal server error',
        'message' => $errstr,
        'file' => $errfile,
        'line' => $errline
    ]);
    exit;
});

set_exception_handler(function($exception) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 500,
        'success' => false,
        'error' => 'Internal server error',
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine()
    ]);
    exit;
});

// Load environment variables
require_once __DIR__ . '/src/config/env.php';

include __DIR__ . '/src/routing/router.php';
include __DIR__ . '/src/api/backend.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Helper function to validate session
function validateSession(bool $requireAdmin = false): ?array {
    $sessionToken = null;
    
    // Check for Bearer token in Authorization header (from API calls)
    $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';
    if (preg_match('/Bearer\s+(.+)/', $authHeader, $matches)) {
        $sessionToken = $matches[1];
    }
    
    // Check for session token in cookie (from browser navigation)
    if (!$sessionToken && isset($_COOKIE['session_token'])) {
        $sessionToken = $_COOKIE['session_token'];
    }
    
    // If no session token found
    if (!$sessionToken) {
        return null;
    }
    
    // Validate session with database
    require_once __DIR__ . '/src/config/env.php';
    $env = loadEnvToArray(__DIR__ . '/.env');
    
    // Prefer environment variables
    $dbHost = $_ENV['DB_HOST'] ?? $env['DB_HOST'] ?? 'localhost';
    $dbPort = (int)($_ENV['DB_PORT'] ?? $env['DB_PORT'] ?? 3306);
    $dbUser = $_ENV['DB_USER'] ?? $env['DB_USER'] ?? 'root';
    $dbPass = $_ENV['DB_PASSWORD'] ?? $env['DB_PASSWORD'] ?? '123';
    $dbName = $_ENV['DB_NAME'] ?? $env['DB_NAME'] ?? 'pwd';
    
    $conn = new mysqli($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
    
    if ($conn->connect_error) {
        return null;
    }
    
    include_once __DIR__ . '/src/api/auth/get_me.php';
    $userResponse = getCurrentUser($conn, $sessionToken);
    $conn->close();
    
    if ($userResponse['status'] !== 200 || !isset($userResponse['user'])) {
        // Invalid session, clear cookie
        setcookie('session_token', '', time() - 3600, '/');
        return null;
    }
    
    $user = $userResponse['user'];
    
    // Check admin requirement
    if ($requireAdmin && $user['role'] !== 'admin') {
        return null;
    }
    
    return $user;
}

// //ini buat ak bisa akses tanpa trailing slash
// if ($path !== "/" && substr($path, -1) === "/") {
//     $path = rtrim($path, "/");
// }

$router = new Router();

$router->add("/", function (): void {
    // Landing page - let frontend handle session check
    readfile(__DIR__ . '/public/pages/landing.xhtml');
    exit;
});
$router->add("/login", function (): void {
    readfile(__DIR__ . '/public/pages/login.xhtml');
    exit;
});
$router->add("/register", function (): void {
    readfile(__DIR__ . '/public/pages/register.xhtml');
    exit;
});$router->add("/my", function (): void {
    $user = validateSession();
    
    if (!$user) {
        // No valid session, serve dashboard.php which will check localStorage and redirect
        readfile(__DIR__ . '/public/dashboard.php');
        exit;
    }
    
    // Route based on role
    if ($user['role'] === 'admin') {
        readfile(__DIR__ . '/public/pages/dashboard_admin.xhtml');
    } else {
        readfile(__DIR__ . '/public/pages/dashboard_user.xhtml');
    }
    exit;
});

$router->add("/admin", function (): void {
    $user = validateSession(true); // Require admin
    
    if (!$user) {
        // Not authorized, redirect to login
        header('Location: /login');
        exit;
    }
    
    // User is admin, serve admin dashboard
    readfile(__DIR__ . '/public/pages/dashboard_admin.xhtml');
    exit;
});


$router->add("/pages/dashboard_user.xhtml", function (): void {
    readfile(__DIR__ . '/public/pages/dashboard_user.xhtml');
    exit;
});
$router->add("/pages/dashboard_admin.xhtml", function (): void {
    readfile(__DIR__ . '/public/pages/dashboard_admin.xhtml');
    exit;
});
$router->add("/profile", function (): void {
    $user = validateSession();
    if (!$user) {
        header('Location: /login');
        exit;
    }
    readfile(__DIR__ . '/public/pages/profil.xhtml');
    exit;
});
$router->add("/titip", function (): void {
    $user = validateSession();
    if (!$user) {
        header('Location: /login');
        exit;
    }
    readfile(__DIR__ . '/public/pages/titip.xhtml');
    exit;
});
$router->add("/riwayat", function (): void {
    $user = validateSession();
    if (!$user) {
        header('Location: /login');
        exit;
    }
    readfile(__DIR__ . '/public/pages/riwayat.xhtml');
    exit;
});
$router->add("/logout", function (): void {
    // Start or resume the session to ensure we can clear it
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    // Clear all session variables
    $_SESSION = [];

    // Delete the session cookie if set
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    // Also clear any app auth token cookies
    if (isset($_COOKIE['session_token'])) {
        setcookie('session_token', '', time() - 3600, '/');
        unset($_COOKIE['session_token']);
    }

    // Finally destroy the session
    session_destroy();

    // Serve a tiny page that clears localStorage then redirects home
    readfile(__DIR__ . '/public/pages/logout.xhtml');
    exit;
});

// Admin routes
$router->add("/admin/users", function (): void {
    $user = validateSession(true);
    if (!$user) {
        header('Location: /login');
        exit;
    }
    readfile(__DIR__ . '/public/pages/all_user.xhtml');
    exit;
});
$router->add("/admin/layanan", function (): void {
    $user = validateSession(true);
    if (!$user) {
        header('Location: /login');
        exit;
    }
    readfile(__DIR__ . '/public/pages/layanan.xhtml');
    exit;
});
$router->add("/admin/paket", function (): void {
    $user = validateSession(true);
    if (!$user) {
        header('Location: /login');
        exit;
    }
    readfile(__DIR__ . '/public/pages/paket.xhtml');
    exit;
});
$router->add("/admin/kelola", function (): void {
    $user = validateSession(true);
    if (!$user) {
        header('Location: /login');
        exit;
    }
    readfile(__DIR__ . '/public/pages/kelola.xhtml');
    exit;
});
$router->add("/admin/detail", function (): void {
    $user = validateSession(true);
    if (!$user) {
        header('Location: /login');
        exit;
    }
    readfile(__DIR__ . '/public/pages/detail_user.xhtml');
    exit;
});
$router->add("/help", function (): void {
    readfile(__DIR__ . '/public/pages/help.xhtml');
    exit;
});
$router->add("/features", function (): void {
    readfile(__DIR__ . '/public/pages/feature.html');
    exit;
});
$router->add("/packages", function (): void {
    readfile(__DIR__ . '/public/pages/package.html');
    exit;
});

$router->add("/test/env", function (): void {
    header('Content-Type: application/json');
    $obj = loadEnvToArray('.env');
    echo json_encode($obj);
    exit;
});


// Forward all /api requests to the API router
if (strpos($path, '/api') === 0) {
    header('Content-Type: application/json');
    $apiBackend = new BACKEND($router);
    $apiBackend->connectDB();
    $apiBackend->setupDatabase();
    $apiBackend->run($path);
    exit;
}

// Serve static files from /static/* mapped to public directory
if (strpos($path, '/static/') === 0) {
    // Map /static/* to public/*
    $filePath = __DIR__ . '/public/' . substr($path, 8); // Remove '/static/' prefix
    
    if (file_exists($filePath) && is_file($filePath)) {
        // Determine content type based on file extension
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $contentTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'json' => 'application/json',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'eot' => 'application/vnd.ms-fontobject'
        ];
        
        $contentType = $contentTypes[$extension] ?? 'application/octet-stream';
        header('Content-Type: ' . $contentType);
        readfile($filePath);
        exit;
    } else {
        http_response_code(404);
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'error' => 'File not found']);
        exit;
    }
}

$router->dispatch($path);