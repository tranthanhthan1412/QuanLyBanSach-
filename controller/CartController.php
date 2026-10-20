<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\CartModel;
use DomainException;

final class CartController extends Controller
{
    public function index(): void
    {
        $this->render('cart/index', ['title' => 'Giỏ hàng của bạn']);
    }

    /** API JSON: GET đọc, POST gộp/đặt số lượng/xoá giỏ. */
    public function sync(): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store, private');

        $user = current_user();
        if (!$user) {
            http_response_code(($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' ? 200 : 401);
            echo json_encode(['authenticated' => false, 'items' => []]);
            return;
        }

        $userId = (int) $user['id'];
        $model = new CartModel();
        try {
            if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET') {
                $_SESSION['csrf_token'] ??= bin2hex(random_bytes(32));
                $result = [
                    'authenticated' => true,
                    'userId' => $userId,
                    'csrf' => $_SESSION['csrf_token'],
                    'items' => $model->items($userId),
                ];
            } else {
                $csrf = $_SESSION['csrf_token'] ?? '';
                if (!is_string($csrf) || $csrf === '' ||
                    !hash_equals($csrf, post_string('csrf_token', false))) {
                    throw new DomainException('Phiên làm việc đã hết hạn. Vui lòng tải lại trang.');
                }
                $action = post_string('action');
                if ($action === 'merge') {
                    $raw = post_string('items', false);
                    if (strlen($raw) > 20000) throw new DomainException('Giỏ hàng quá lớn.');
                    $items = json_decode($raw, true);
                    if (!is_array($items) || !array_is_list($items)) {
                        throw new DomainException('Dữ liệu giỏ không hợp lệ.');
                    }
                    $saved = $model->mergeGuest($userId, post_string('transfer_id'), $items);
                } elseif ($action === 'set') {
                    $id = post_string('id');
                    $qty = post_string('qty');
                    if (!ctype_digit($id) || !ctype_digit($qty)) {
                        throw new DomainException('ID sách hoặc số lượng không hợp lệ.');
                    }
                    $saved = $model->setQuantity($userId, (int) $id, (int) $qty);
                } elseif ($action === 'clear') {
                    $saved = $model->clear($userId);
                } else {
                    throw new DomainException('Thao tác giỏ hàng không hợp lệ.');
                }
                $result = ['ok' => true, 'items' => $saved];
            }
            echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (DomainException $error) {
            http_response_code(422);
            echo json_encode(['ok' => false, 'message' => $error->getMessage()], JSON_UNESCAPED_UNICODE);
        }
    }
}
