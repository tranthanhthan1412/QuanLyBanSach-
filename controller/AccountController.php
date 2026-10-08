<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\OrderModel;

final class AccountController extends Controller
{
    private function requireLogin(): array
    {
        $user = current_user();
        if ($user === null) {
            header('Location: ' . url('login'));
            exit;
        }

        return $user;
    }


    /**
     * Trang tài khoản
     */
    public function index(): void
    {
        $user = $this->requireLogin();

        $this->render('account/index', [
            'title' => 'Tài khoản',
            'user' => $user
        ]);
    }


    /**
     * Danh sách đơn hàng
     */
    public function orders(): void
    {
        $user = $this->requireLogin();

        $orderModel = new OrderModel();

        $orders = $orderModel->getByUser(
            (int)$user['id']
        );

        $this->render('account/orders', [
            'title' => 'Đơn hàng',
            'user' => $user,
            'orders' => $orders
        ]);
    }


    /**
     * Chi tiết đơn hàng
     */
    public function show(): void
    {
        $user = $this->requireLogin();

        $orderId = (int) query('id', '0');

        if ($orderId <= 0) {
            header('Location: ' . url('orders'));
            exit;
        }

        $orderModel = new OrderModel();

        $order = $orderModel->getById(
            $orderId,
            (int)$user['id']
        );

        if (!$order) {
            http_response_code(404);

            $this->render('errors/404', [
                'title' => 'Không tìm thấy đơn hàng'
            ]);

            return;
        }

        $this->render('account/show', [
            'title' => 'Chi tiết đơn hàng',
            'user' => $user,
            'order' => $order
        ]);
    }
}
