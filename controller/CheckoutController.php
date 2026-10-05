<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;

final class CheckoutController extends Controller
{
    public function index(): void
    {
        $this->render('checkout/index', ['title' => 'Thông tin đặt hàng']);
    }

    public function success(): void
    {
        $this->render('checkout/success', ['title' => 'Đặt hàng mô phỏng thành công']);
    }
}
