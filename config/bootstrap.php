<?php

declare(strict_types=1);

session_start();

define('ROOT_PATH', dirname(__DIR__));

spl_autoload_register(function (string $class): void {

    $namespaces = [
        'App\\Controllers\\' => '/controller/',
        'App\\Models\\' => '/model/',
        'App\\Core\\' => '/libs/',
    ];

    foreach ($namespaces as $prefix => $directory) {

        if (str_starts_with($class, $prefix)) {

            $path = ROOT_PATH . $directory
                . str_replace('\\', '/', substr($class, strlen($prefix)))
                . '.php';

            if (is_file($path)) {
                require $path;
            }

            return;
        }
    }
});

require ROOT_PATH . '/libs/helpers.php';

// Kết nối và khởi tạo CSDL
require_once ROOT_PATH . '/model/database.php';

$database = new Database();
$db = $database->getConnection();