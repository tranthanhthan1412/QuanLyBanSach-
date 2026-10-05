<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\OrderModel;

final class AccountController extends Controller
{
    public function index(): void
    {
        $this->render('account/index', ['title' => 'Tài khoản mẫu']);
    }

    public function orders(): void
    {
        $this->render('account/orders', [
            'title' => 'Đơn hàng mẫu',
            'orders' => (new OrderModel())->all(),
        ]);
    }

    public function show(): void
    {
        $order = (new OrderModel())->find(query('id'));
        if ($order === null) {
            $this->notFound();
            return;
        }

        $this->render('account/show', ['title' => 'Đơn ' . $order['id'], 'order' => $order]);
    }
}
