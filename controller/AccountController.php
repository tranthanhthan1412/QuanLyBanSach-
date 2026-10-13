<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\OrderModel;
use App\Models\CheckoutModel;
use App\Models\AccountModel;
use DomainException;

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
        $model = new AccountModel();
        $profile = $model->profile((int) $user['id']);
        $error = '';
        $success = $_SESSION['account_success'] ?? '';
        unset($_SESSION['account_success']);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $profile = [
                'name' => post_string('name'),
                'phone' => post_string('phone'),
                'address' => post_string('address'),
            ];
            try {
                $model->updateProfile((int) $user['id'], $profile);
                $_SESSION['account_success'] = 'Đã cập nhật thông tin cá nhân.';
                header('Location: ' . url('account'), true, 303);
                return;
            } catch (DomainException $exception) {
                http_response_code(422);
                $error = $exception->getMessage();
                $success = '';
            }
        }

        $this->render('account/index', [
            'title' => 'Tài khoản',
            'user' => $user,
            'profile' => $profile,
            'error' => $error,
            'success' => $success,
        ]);
    }

    public function password(): void
    {
        $user = $this->requireLogin();
        $error = '';
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                (new AccountModel())->changePassword(
                    (int) $user['id'],
                    post_string('current_password', false),
                    post_string('password', false),
                    post_string('confirm', false)
                );
                $_SESSION = [];
                session_regenerate_id(true);
                $_SESSION['auth_success'] = 'Đổi mật khẩu thành công. Vui lòng đăng nhập lại.';
                header('Location: ' . url('login'), true, 303);
                return;
            } catch (DomainException $exception) {
                http_response_code(422);
                $error = $exception->getMessage();
            }
        }
        $this->render('account/password', ['title' => 'Đổi mật khẩu', 'user' => $user, 'error' => $error]);
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

        $order = (new CheckoutModel())->find(
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
