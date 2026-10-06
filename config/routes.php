<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\ProductController;
use App\Controllers\CartController;
use App\Controllers\CheckoutController;
use App\Controllers\AuthController;
use App\Controllers\AccountController;


// =========================
// HOME
// =========================

$router->get(
    'home',
    [HomeController::class, 'index']
);


// =========================
// PRODUCTS
// =========================

$router->get(
    'products',
    [ProductController::class, 'index']
);

$router->get(
    'product',
    [ProductController::class, 'show']
);


// =========================
// CART
// =========================

$router->get(
    'cart',
    [CartController::class, 'index']
);


// =========================
// CHECKOUT
// =========================

$router->get(
    'checkout',
    [CheckoutController::class, 'index']
);

$router->get(
    'order-success',
    [CheckoutController::class, 'success']
);


// =========================
// AUTH
// =========================

$router->get(
    'login',
    [AuthController::class, 'login']
);

$router->get(
    'register',
    [AuthController::class, 'register']
);

$router->get(
    'logout',
    [AuthController::class, 'logout']
);


// =========================
// ACCOUNT
// =========================

$router->get(
    'account',
    [AccountController::class, 'index']
);

$router->get(
    'orders',
    [AccountController::class, 'orders']
);

$router->get(
    'order',
    [AccountController::class, 'show']
);