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
    
    // Prefer $_ENV values if set, otherwise load from .env file
    $env = [
        'DB_HOST'     => $_ENV['DB_HOST']     ?? null,
        'DB_PORT'     => $_ENV['DB_PORT']     ?? null,
        'DB_USER'     => $_ENV['DB_USER']     ?? null,
        'DB_PASSWORD' => $_ENV['DB_PASSWORD'] ?? null,
        'DB_NAME'     => $_ENV['DB_NAME']     ?? null,
    ];

    // If any are missing, load from .env
    if (in_array(null, $env, true)) {
        $fileEnv = loadEnvToArray('.env');
        foreach ($env as $key => $value) {
            if ($value === null && isset($fileEnv[$key])) {
                $env[$key] = $fileEnv[$key];
            }
        }
    }

    $apiBackend->connectDB();
    $apiBackend->setupDatabase();
    $apiBackend->run($path);
    exit;
}



$router->dispatch($path);