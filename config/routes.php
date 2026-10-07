<?php
declare(strict_types=1);
use App\Controllers\HomeController;
use App\Controllers\ProductController;
use App\Controllers\CartController;
use App\Controllers\CheckoutController;
use App\Controllers\AuthController;
use App\Controllers\AccountController;

$router->get('home', [HomeController::class, 'index']);
$router->get('products', [ProductController::class, 'index']);
$router->get('product', [ProductController::class, 'show']);
$router->get('cart', [CartController::class, 'index']);
$router->get('checkout', [CheckoutController::class, 'index']);
$router->get('order-success', [CheckoutController::class, 'success']);
$router->get('login', [AuthController::class, 'login']);
$router->post('login', [AuthController::class, 'login']);
$router->get('register', [AuthController::class, 'register']);
$router->post('register', [AuthController::class, 'register']);
$router->post('logout', [AuthController::class, 'logout']);
$router->get('account', [AccountController::class, 'index']);
$router->get('orders', [AccountController::class, 'orders']);
$router->get('order', [AccountController::class, 'show']);
