<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\OrderMailer;
use App\Models\CheckoutModel;
use App\Models\CartModel;
use App\Models\AccountModel;
use DomainException;

final class CheckoutController extends Controller
{
    public function index(): void
    {
        $_SESSION['checkout_token'] ??= bin2hex(random_bytes(32));
        $error = $_SESSION['checkout_error'] ?? '';
        $old = $_SESSION['checkout_old'] ?? [];
        unset($_SESSION['checkout_error'], $_SESSION['checkout_old']);
        $user = current_user();
        $profile = $user ? (new AccountModel())->profile((int) $user['id']) : [];
        $this->render('checkout/index', ['title' => 'Thông tin đặt hàng', 'user' => $user, 'profile' => $profile, 'error' => $error, 'old' => $old]);
    }

    public function store(): void
    {
       
        if (!str_contains($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json')) {
            $_SESSION['checkout_error'] = 'Chưa gửi được giỏ hàng. Vui lòng kiểm tra lại thông tin và bấm Đặt hàng.';
            $_SESSION['checkout_old'] = [];
            foreach (['name' => 80, 'phone' => 30, 'province' => 80, 'ward' => 80, 'address' => 200, 'note' => 500] as $key => $limit) {
                $_SESSION['checkout_old'][$key] = mb_substr(post_string($key), 0, $limit);
            }
            header('Location: ' . url('checkout'), true, 303);
            return;
        }
        header('Content-Type: application/json; charset=UTF-8');
        $user = current_user();
        $model = new CheckoutModel();
        try {
            $token = post_string('checkout_token');
            if (!preg_match('/^[a-f0-9]{64}$/D', $token)) { throw new DomainException('Phiên đặt hàng không hợp lệ. Vui lòng tải lại trang.'); }
          
            $order = $model->byToken($token, $user['id']);
            if (!$order) {
                if (!hash_equals($_SESSION['checkout_token'] ?? '', $token)) {
                    throw new DomainException('Phiên đặt hàng đã hết hạn. Vui lòng tải lại trang.');
                }
                $data = [];
                foreach (['name' => [2, 80], 'province' => [2, 80], 'ward' => [2, 80], 'address' => [5, 200], 'note' => [0, 500]] as $key => [$min, $max]) {
                    $data[$key] = post_string($key);
                    if (mb_strlen($data[$key]) < $min || mb_strlen($data[$key]) > $max) {
                        throw new DomainException('Vui lòng kiểm tra họ tên, địa chỉ và ghi chú theo độ dài cho phép.');
                    }
                }
                $data['phone'] = preg_replace('/[\s.-]/', '', post_string('phone'));
                if (!preg_match('/^(0|\+84)[0-9]{9}$/D', $data['phone'])) { throw new DomainException('Số điện thoại không hợp lệ.'); }
                if (!filter_var($user['email'], FILTER_VALIDATE_EMAIL)) { throw new DomainException('Email tài khoản không hợp lệ.'); }
                if (post_string('payment') !== 'cod') { throw new DomainException('Hiện chỉ hỗ trợ thanh toán khi nhận hàng (COD).'); }
                $raw = post_string('items');
                if (strlen($raw) > 20000) { throw new DomainException('Giỏ hàng quá lớn.'); }
                $items = json_decode($raw, true);
                if (!is_array($items)) { throw new DomainException('Dữ liệu giỏ hàng không hợp lệ.'); }
                $order = $model->create($user, $data, $items, $token);
                // Lưu đơn xong: xoá giỏ đã đồng bộ trong MySQL.
                (new CartModel())->clear((int) $user['id']);
                unset($_SESSION['checkout_token']);
               
                $mailStatus = (new OrderMailer())->send($order);
                $model->recordMailStatus((int) $order['maDH'], $mailStatus);
            }
            echo json_encode(['ok' => true, 'url' => url('order-success', ['id' => $order['maDH']])], JSON_UNESCAPED_UNICODE);
        } catch (DomainException $error) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'message' => $error->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }

    public function success(): void
    {
        $user = current_user();
        $id = (int) query('id', '0');
        $order = $user && $id > 0 ? (new CheckoutModel())->find($id, $user['id']) : null;
        if ($id > 0 && !$order) { $this->notFound(); return; }
        $this->render('checkout/success', ['title' => 'Thông tin đặt hàng', 'order' => $order]);
    }
}
