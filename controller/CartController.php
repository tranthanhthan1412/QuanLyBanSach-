<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

final class CartController extends Controller
{
    public function index(): void
    {
        $this->render('cart/index', ['title' => 'Giỏ hàng của bạn']);
    }
}
