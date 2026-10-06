<?php

declare(strict_types=1);

require __DIR__ . '/config/bootstrap.php';

header('Content-Type: text/html; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

$router = new App\Core\Router();

require ROOT_PATH . '/config/routes.php';


// =========================
// KIỂM TRA HTTP METHOD
// =========================

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if (!in_array($method, ['GET', 'POST'], true)) {

    http_response_code(405);

    header('Allow: GET, POST');

    (new App\Core\Controller())->render(
        'errors/405',
        [
            'title' => 'Phương thức không được hỗ trợ'
        ]
    );

    exit;
}


// =========================
// DISPATCH ROUTE
// =========================

$router->dispatch(
    query('route', 'home')
);