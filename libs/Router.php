<?php
declare(strict_types=1);

namespace App\Core;

final class Router
{
    private array $routes = [];

    public function get(string $path, array $handler): void
    {
        $this->routes[$path] = $handler;
    }

    public function dispatch(string $path): void
    {
        if (!isset($this->routes[$path])) {
            http_response_code(404);
            (new Controller())->render('errors/404', ['title' => 'Không tìm thấy trang']);
            return;
        }
        [$class, $method] = $this->routes[$path];
        (new $class())->$method();
    }
}
