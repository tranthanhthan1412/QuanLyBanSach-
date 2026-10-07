<?php

declare(strict_types=1);

session_start([
    'use_strict_mode' => true,
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
    'cookie_secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);

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

// Connections are shared and opened lazily. Schema setup is CLI-only.
require_once ROOT_PATH . '/model/database.php';
