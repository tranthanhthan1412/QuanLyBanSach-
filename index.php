<?php
declare(strict_types=1);

require __DIR__ . '/config/bootstrap.php';

header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

$router = new App\Core\Router();
require ROOT_PATH . '/config/routes.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
    http_response_code(405);
    header('Allow: GET');
    (new App\Core\Controller())->render('errors/405', ['title' => 'Chức năng mô phỏng']);
    exit;
}

$router->dispatch(query('route', 'home'));
