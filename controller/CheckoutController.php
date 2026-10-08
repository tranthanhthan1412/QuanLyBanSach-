<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\CodOrderModel;
use App\Models\OrderModel;
use DomainException;
use Throwable;

final class CheckoutController extends Controller
{
    public function index(): void
    {
        $_SESSION['checkout_nonce'] ??= bin2hex(random_bytes(24));
        $this->render('checkout/index', [
            'title' => 'Thông tin đặt hàng',
            'loggedIn' => current_user() !== null,
        ]);
    }

    private function respond(int $status, array $data): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function place(): void
    {
        $user = current_user();
        if ($user === null) {
            $this->respond(401, ['ok' => false, 'message' => 'Vui lòng đăng nhập trước khi đặt hàng.', 'loginUrl' => url('login')]);
            return;
        }
        $nonce = post_string('checkout_nonce');
        if ($nonce === '' || !hash_equals((string) ($_SESSION['checkout_nonce'] ?? ''), $nonce)) {
            $this->respond(409, ['ok' => false, 'message' => 'Phiên đặt hàng đã hết hạn hoặc đơn đã được gửi. Hãy tải lại trang.']);
            return;
        }
        $name = post_string('name');
        $phone = preg_replace('/[\s.\-]/', '', post_string('phone'));
        $email = post_string('email');
        $province = post_string('province');
        $ward = post_string('ward');
        $address = post_string('address');
        $note = post_string('note');
        $payment = post_string('payment');
        $len = static function (string $text): int {
            return function_exists('mb_strlen') ? mb_strlen($text, 'UTF-8') : strlen($text);
        };
        if ($len($name) < 2 || $len($name) > 80
            || !preg_match('/^(0\d{9}|\+84\d{9})$/D', $phone)
            || ($email !== '' && ($len($email) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL)))
            || $len($province) < 2 || $len($province) > 80
            || $len($ward) < 2 || $len($ward) > 80
            || $len($address) < 5 || $len($address) > 200
            || $len($note) > 500 || $payment !== 'cod') {
            $this->respond(422, ['ok' => false, 'message' => 'Thông tin nhận hàng không hợp lệ. Vui lòng kiểm tra lại biểu mẫu.']);
            return;
        }
        try {
            $items = json_decode(post_string('items'), true, 512, JSON_THROW_ON_ERROR);
            if (!is_array($items) || !array_is_list($items)) {
                throw new DomainException('Giỏ hàng không hợp lệ.');
            }
            $orderId = (new CodOrderModel())->create((int) $user['id'], $items, [
                'name' => $name, 'phone' => $phone, 'email' => $email,
                'address' => implode(', ', [$address, $ward, $province]), 'note' => $note,
            ]);
        } catch (DomainException|\JsonException $error) {
            $this->respond(422, ['ok' => false, 'message' => $error->getMessage()]);
            return;
        } catch (Throwable $error) {
            error_log((string) $error);
            $this->respond(503, ['ok' => false, 'message' => 'Không thể lưu đơn hàng lúc này. Vui lòng thử lại.']);
            return;
        }
        // Invalidate the one-time submission before returning. A second click will not insert twice.
        unset($_SESSION['checkout_nonce']);
        $this->respond(201, [
            'ok' => true,
            'orderId' => $orderId,
            'redirect' => url('order-success', ['id' => $orderId]),
        ]);
    }

    public function success(): void
    {
        $user = current_user();
        if ($user === null) {
            header('Location: ' . url('login'), true, 302);
            return;
        }
        $id = (int) query('id', '0');
        $order = $id > 0 ? (new OrderModel())->getById($id, (int) $user['id']) : null;
        if ($order === null) {
            $this->notFound();
            return;
        }
        $this->render('checkout/success', ['title' => 'Đặt hàng COD thành công', 'order' => $order]);
    }
}
