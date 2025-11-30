<?php
declare(strict_types=1);
require __DIR__ . '/../src/router.php';
require __DIR__ . '/../src/login/login.php';
require __DIR__ . '/../src/logout.php';
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

$router = new Router();

$router->add("/", function (): void {
    session_start();
    if (isset($_SESSION['username'])) {
        $user = $_SESSION['username'];
        header("Location: /my/");
        exit;
    }else{
    header("Location: /login");
    exit;
    }
});
$router->add("/login", function (): void {
    include __DIR__ . '/../src/login/login.php';
});
$router->add("/logout", function (): void {
    include __DIR__ . '/../src/logout.php';
});

$router->dispatch($path);