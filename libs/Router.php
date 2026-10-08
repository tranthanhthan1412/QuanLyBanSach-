<?php
declare(strict_types=1);
namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(string $path, array $handler, array $roles = []): void
    {
        $this->routes[$path]['GET'] = ['handler' => $handler, 'roles' => $roles];
    }

    public function post(string $path, array $handler, array $roles = []): void
    {
        $this->routes[$path]['POST'] = ['handler' => $handler, 'roles' => $roles];
    }

    public function dispatch(string $path): void
    {
        $user = current_user();
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
        $route = $this->routes[$path][$method];
        if ($route['roles'] !== []) {
            if ($user === null) {
                header('Location: ' . url('login'), true, $method === 'POST' ? 303 : 302);
                return;
            }
            if (!in_array($user['role'], $route['roles'], true)) {
                http_response_code(403);
                (new Controller())->render('errors/403', [
                    'title' => 'Bạn không có quyền truy cập',
                    'message' => 'Trang này chỉ dành cho quản trị viên.',
                ]);
                return;
            }
        }
        if ($method === 'POST' && !csrf_valid()) {
            http_response_code(403);
            (new Controller())->render('errors/403', ['title' => 'Phiên gửi biểu mẫu không hợp lệ']);
            return;
        }
        [$class, $action] = $route['handler'];
        (new $class())->$action();
    }
}
