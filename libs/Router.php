<?php
declare(strict_types=1);
namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->routes[$path]['GET'] = $handler;
    }

    public function post(string $path, array $handler): void
    {
        $this->routes[$path]['POST'] = $handler;
    }

    public function dispatch(string $path): void
    {
        if (!isset($this->routes[$path])) {
            http_response_code(404);
            (new Controller())->render('errors/404', ['title' => 'Không tìm thấy trang']);
            return;
        }
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        if (!isset($this->routes[$path][$method])) {
            http_response_code(405);
            header('Allow: ' . implode(', ', array_keys($this->routes[$path])));
            (new Controller())->render('errors/405', ['title' => 'Phương thức không được hỗ trợ']);
            return;
        }
        if ($method === 'POST' && !csrf_valid()) {
            http_response_code(403);
            (new Controller())->render('errors/403', ['title' => 'Phiên gửi biểu mẫu không hợp lệ']);
            return;
        }
        [$class, $action] = $this->routes[$path][$method];
        (new $class())->$action();
    }
}
