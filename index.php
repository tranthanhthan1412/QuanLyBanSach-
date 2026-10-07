<?php
declare(strict_types=1);
require __DIR__ . '/config/bootstrap.php';
header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');
header('Cache-Control: no-store');

ob_start();
try {
    $router = new App\Core\Router();
    require ROOT_PATH . '/config/routes.php';
    $router->dispatch(query('route', 'home'));
    ob_end_flush();
} catch (Throwable $error) {
    ob_end_clean();
    error_log((string) $error);
    http_response_code(503);
    require ROOT_PATH . '/view/errors/503.php';
}
