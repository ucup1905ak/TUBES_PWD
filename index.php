<?php

// Load environment variables
require_once __DIR__ . '/src/config/env.php';

include __DIR__ . '/src/routing/router.php';
include __DIR__ . '/src/api/backend.php';

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

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
});
$router->add("/my", function (): void {
    readfile(__DIR__ . '/public/dashboard.php');
    exit;
});
$router->add("/profile", function (): void {
    readfile(__DIR__ . '/public/pages/profil.xhtml');
    // include 'public/pages';
    exit;
});
$router->add("/logout", function (): void {
    session_start();
    // Destroy the session to log out the user.
    session_unset();
    session_destroy();
    header('Location: /');
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
    $apiBackend = new BACKEND($router);
    $env = loadEnvToArray('.env');
    $apiBackend->connectDB(
        $env['DB_HOST'] ?? 'localhost',
        (int)($env['DB_PORT'] ?? 3306),
        $env['DB_USER'] ?? 'root',
        $env['DB_PASSWORD'] ?? '123',
        $env['DB_NAME'] ?? 'pwd'
    );
    $apiBackend->setupDatabase();
    $apiBackend->run($path);
    exit;
}



$router->dispatch($path);