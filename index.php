<?php
declare(strict_types=1);
include __DIR__ . '/src/routing/router.php';
include __DIR__ . '/src/api/backend.php';
// include __DIR__ . '/vendor/autoload.php';
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$router = new Router();

$router->add("/", function (): void {
    session_start();
    // If the user is logged in, redirect to the dashboard.
    if (isset($_SESSION['user_id'])) {
        header('Location: /dashboard.php');
        exit;
    }

    // Otherwise, redirect to the landing page.
    include __DIR__ . '/public/pages/landing.xhtml';
    exit;
});
$router->add("/login", function (): void {
    header('Location: /login.xhtml');
    exit;
});
$router->add("/logout", function (): void {
    session_start();
    // Destroy the session to log out the user.
    session_unset();
    session_destroy();
    header('Location: /index.xhtml');
    exit;
});



// Forward all /api requests to the API router
if (strpos($path, '/api') === 0) {
    $apiBackend = new BACKEND($router);
    $apiBackend->connectDB('localhost', 
                                3306, 
                                'root', 
                                '123', 
                                'pwd');
    $apiBackend->setupDatabase();
    $apiBackend->run($path);
    exit;
}



$router->dispatch($path);