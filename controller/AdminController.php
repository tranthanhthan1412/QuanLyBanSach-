<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Core\Controller;
use App\Models\AdminModel;
use App\Models\RevenueReportModel;
use App\Models\WarehouseModel;
use DomainException;

final class AdminController extends Controller
{
    private function model(): AdminModel { return new AdminModel(); }

    private function page(string $view, string $title, array $data = []): void
    {
        $user = current_user();
        $flash = $_SESSION['admin_flash'] ?? null;
        unset($_SESSION['admin_flash']);
        extract($data, EXTR_SKIP);
        require ROOT_PATH . '/view/layouts/admin.php';
    }

    private function redirect(string $route, string $message, string $type = 'success', array $query = []): never
    {
        $_SESSION['admin_flash'] = compact('message', 'type');
        header('Location: ' . url($route, $query), true, 303);
        exit;
    }

    private function mutation(callable $work, string $route, string $success, array $query = []): never
    {
        try { $work(); }
        catch (DomainException $error) { $this->redirect($route, $error->getMessage(), 'error', $query); }
        $this->redirect($route, $success, 'success', $query);
    }

    public function index(): void
    {
        $model = $this->model();
        $this->page('index', 'Tổng quan cửa hàng', [
            'stats' => $model->overview(), 'recent' => $model->orders('', '', 1)['rows'],
            'lowStock' => $model->lowStock(),
        ]);
    }
	
	// Hiển thị báo cáo bán hàng theo khoảng thời gian
	public function reports(): void
	{
	    $today = new \DateTimeImmutable(
	        'now',
	        new \DateTimeZone('Asia/Ho_Chi_Minh')
	    );

	    $from = query('from', $today->format('Y-m-01'));
	    $to = query('to', $today->format('Y-m-d'));

	    $summary = null;
	    $bestSellers = [];
	    $error = '';

	    try {
	        $report = new RevenueReportModel();

	        $summary = $report->summary($from, $to);
	        $bestSellers = $report->bestSellers($from, $to);
	    } catch (DomainException $exception) {
	        $error = $exception->getMessage();
	    }

	    $this->page('reports', 'Báo cáo theo khoảng thời gian', [
	        'from' => $from,
	        'to' => $to,
	        'summary' => $summary,
	        'bestSellers' => $bestSellers,
	        'error' => $error,
	    ]);
	}

    public function products(): void
    {
        $model = $this->model();
        $this->page('products', 'Sản phẩm', [
            'listing' => $model->products(query('q'), query('visibility'), query('category'), (int) query('page', '1')),
            'categories' => $model->options('categories'),
        ]);
    }

    public function productForm(): void
    {
        $id = (int) query('id', '0');
        $model = $this->model();
        $product = $id ? $model->product($id) : ['maSach' => 0, 'tenSach' => '', 'moTa' => '', 'giaTien' => '', 'tonKho' => 0, 'maTL' => '', 'maTG' => '', 'maNXB' => '', 'hinhAnh' => '', 'an' => 0, 'phienBan' => 0];
        if (!$product) { $this->notFound(); return; }
        $error = '';
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $newImage = null;
            foreach (['tenSach', 'moTa', 'giaTien', 'tonKho', 'maTL', 'maTG', 'maNXB', 'an', 'phienBan'] as $key) { $product[$key] = post_string($key); }
            try {
                if (mb_strlen($product['tenSach']) < 2 || mb_strlen($product['tenSach']) > 255) { throw new DomainException('Tên sách cần từ 2 đến 255 ký tự.'); }
                if (strlen($product['moTa']) > 60000) { throw new DomainException('Mô tả quá dài (tối đa 60.000 byte).'); }
                if (!preg_match('/^\d{1,10}(\.\d{1,2})?$/D', $product['giaTien']) || (float) $product['giaTien'] > 9999999999) { throw new DomainException('Giá phải từ 0 đến 9.999.999.999, tối đa hai chữ số thập phân.'); }
                if (!preg_match('/^\d{1,7}$/D', $product['tonKho'])) { throw new DomainException('Tồn kho phải là số nguyên từ 0 đến 9.999.999.'); }
                if (!in_array($product['an'], ['0', '1'], true)) { throw new DomainException('Trạng thái hiển thị không hợp lệ.'); }
                foreach (['maTL' => 'categories', 'maTG' => 'authors', 'maNXB' => 'publishers'] as $key => $kind) {
                    if (!ctype_digit($product[$key]) || !$model->catalogItem($kind, (int) $product[$key])) { throw new DomainException('Vui lòng chọn thể loại, tác giả và nhà xuất bản hợp lệ.'); }
                }
                $newImage = $this->uploadImage();
                if ($newImage !== null) { $product['hinhAnh'] = $newImage; }
                elseif (post_string('remove_image') === '1') { $product['hinhAnh'] = ''; }
                $model->saveProduct($product, $id, (int) $product['phienBan'], current_user()['id']);
                $this->redirect('admin-products', $id ? 'Đã cập nhật sách.' : 'Đã thêm sách mới.');
            } catch (\Throwable $exception) {
                if ($newImage !== null) { unlink(ROOT_PATH . '/public/assets/' . $newImage); }
                if (!$exception instanceof DomainException) { throw $exception; }
                $product['hinhAnh'] = $id ? ($model->product($id)['hinhAnh'] ?? '') : '';
                $error = $exception->getMessage();
                http_response_code(422);
            }
        }
        $this->page('product-form', $id ? 'Chỉnh sửa sách' : 'Thêm sách mới', [
            'product' => $product, 'error' => $error,
            'categories' => $model->options('categories'), 'authors' => $model->options('authors'), 'publishers' => $model->options('publishers'),
        ]);
    }

    private function uploadImage(): ?string
    {
        $file = $_FILES['image'] ?? null;
        if ($file === null || ($file['error'] ?? null) === UPLOAD_ERR_NO_FILE) { return null; }
        if (!is_array($file) || ($file['error'] ?? -1) !== UPLOAD_ERR_OK || !is_string($file['tmp_name'] ?? null) || !is_uploaded_file($file['tmp_name'])) { throw new DomainException('Không thể nhận ảnh tải lên.'); }
        if (filesize($file['tmp_name']) > 2 * 1024 * 1024) { throw new DomainException('Ảnh bìa tối đa 2 MB.'); }
        $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);
        $extension = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
        $size = @getimagesize($file['tmp_name']);
        if (!$extension || !$size || $size[0] > 6000 || $size[1] > 6000 || ($size['mime'] ?? '') !== $mime) { throw new DomainException('Chỉ nhận ảnh JPG, PNG, WebP hợp lệ, kích thước tối đa 6000 × 6000.'); }
        $path = 'images/upload-' . bin2hex(random_bytes(16)) . '.' . $extension;
        if (!move_uploaded_file($file['tmp_name'], ROOT_PATH . '/public/assets/' . $path)) { throw new DomainException('Không thể lưu ảnh bìa.'); }
        return $path;
    }

    public function deleteProduct(): void
    {
        $this->mutation(fn () => $this->model()->deleteProduct((int) post_string('id'), (int) post_string('version'), current_user()['id']), 'admin-products', 'Đã xóa sách.');
    }

    private function kind(): string
    {
        $kind = query('kind', 'categories');
        if (!isset(AdminModel::CATALOGS[$kind])) { throw new DomainException('Danh mục không hợp lệ.'); }
        return $kind;
    }

    public function catalog(): void
    {
        try { $kind = $this->kind(); } catch (DomainException) { $this->notFound(); return; }
        $model = $this->model();
        $id = (int) query('id', '0');
        $item = $id ? $model->catalogItem($kind, $id) : ['id' => 0, 'name' => ''];
        if (!$item) { $this->notFound(); return; }
        $error = '';
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $item['name'] = post_string('name');
            try {
                if (mb_strlen($item['name']) < 2 || mb_strlen($item['name']) > AdminModel::CATALOGS[$kind][4]) { throw new DomainException('Tên quá ngắn hoặc vượt độ dài cho phép.'); }
                $model->saveCatalog($kind, $id, $item['name'], current_user()['id']);
                $this->redirect('admin-catalog', 'Đã lưu danh mục.', 'success', ['kind' => $kind]);
            } catch (DomainException $exception) { $error = $exception->getMessage(); http_response_code(422); }
        }
        $this->page('catalog', AdminModel::CATALOGS[$kind][3], [
            'kind' => $kind, 'item' => $item, 'error' => $error,
            'listing' => $model->catalog($kind, query('q'), (int) query('page', '1')),
        ]);
    }

    public function deleteCatalog(): void
    {
        try { $kind = $this->kind(); } catch (DomainException) { $this->notFound(); return; }
        $this->mutation(fn () => $this->model()->deleteCatalog($kind, (int) post_string('id'), current_user()['id']), 'admin-catalog', 'Đã xóa danh mục.', ['kind' => $kind]);
    }

    /** Danh sách phiếu nhập kho. */
    public function stock(): void
    {
        $this->page('stock', 'Quản lý nhập kho', [
            'listing' => (new WarehouseModel())->receipts((int) query('page', '1')),
        ]);
    }

    /** Lập phiếu nhập; POST tạo phiếu + cộng tồn kho nguyên tử. */
    public function stockCreate(): void
    {
        $model = new WarehouseModel();
        $_SESSION['stock_entry_token'] ??= bin2hex(random_bytes(32));
        $token = $_SESSION['stock_entry_token'];
        $error = '';
        $form = [
            'date' => date('Y-m-d'), 'supplier' => '', 'note' => '',
            'items' => array_fill(0, 3, ['book_id' => '', 'quantity' => '', 'unit_price' => '']),
        ];

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $form['date'] = post_string('date');
            $form['supplier'] = post_string('supplier');
            $form['note'] = post_string('note');
            $ids = $_POST['book_id'] ?? [];
            $quantities = $_POST['quantity'] ?? [];
            $prices = $_POST['unit_price'] ?? [];
            try {
                $submittedToken = post_string('entry_token');
                if (!preg_match('/^[a-f0-9]{64}$/D', $submittedToken)) {
                    throw new DomainException('Phiên nhập kho không hợp lệ. Hãy tải lại trang.');
                }
                // Retry sau khi nhận phản hồi lỗi mạng: không nhập kho lần thứ hai.
                if (!hash_equals($token, $submittedToken)) {
                    $previous = $model->byToken($submittedToken);
                    if ($previous !== null) {
                        $this->redirect('admin-stock-detail', 'Phiếu nhập đã được ghi nhận trước đó.', 'success', ['id' => $previous]);
                    }
                    throw new DomainException('Phiên nhập kho đã hết hạn. Hãy tải lại trang.');
                }
                if (!is_array($ids) || !is_array($quantities) || !is_array($prices)) {
                    throw new DomainException('Danh sách sách nhập không hợp lệ.');
                }
                $count = max(count($ids), count($quantities), count($prices));
                if ($count > 20) {
                    throw new DomainException('Một phiếu nhập tối đa 20 đầu sách.');
                }
                $form['items'] = [];
                for ($i = 0; $i < $count; $i++) {
                    $form['items'][] = [
                        'book_id' => is_scalar($ids[$i] ?? null) ? trim((string) $ids[$i]) : '',
                        'quantity' => is_scalar($quantities[$i] ?? null) ? trim((string) $quantities[$i]) : '',
                        'unit_price' => is_scalar($prices[$i] ?? null) ? trim((string) $prices[$i]) : '',
                    ];
                }
                $id = $model->create($form, $submittedToken, (int) current_user()['id']);
                unset($_SESSION['stock_entry_token']);
                $this->redirect('admin-stock-detail', 'Đã lập phiếu nhập và cộng tồn kho.', 'success', ['id' => $id]);
            } catch (DomainException $exception) {
                $error = $exception->getMessage();
                http_response_code(422);
            }
        }

        $this->page('stock-form', 'Lập phiếu nhập kho', [
            'books' => $model->books(), 'form' => $form, 'error' => $error, 'token' => $token,
        ]);
    }

    /** Chi tiết phiếu đã xác nhận: chỉ đọc, không cho sửa/xóa chứng từ. */
    public function stockDetail(): void
    {
        $id = (int) query('id', '0');
        $receipt = $id > 0 ? (new WarehouseModel())->receipt($id) : null;
        if (!$receipt) { $this->notFound(); return; }
        $this->page('stock-detail', 'Phiếu nhập #' . $id, ['receipt' => $receipt]);
    }

    public function orders(): void
    {
        $this->page('orders', 'Đơn hàng', ['listing' => $this->model()->orders(query('q'), query('status'), (int) query('page', '1'))]);
    }

    public function order(): void
    {
        $id = (int) query('id', '0');
        $order = $this->model()->order($id);
        if (!$order) { $this->notFound(); return; }
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->mutation(function () use ($id): void {
                $note = post_string('note');
                if (mb_strlen($note) > 500) { throw new DomainException('Ghi chú tối đa 500 ký tự.'); }
                if (post_string('status') === 'DaHuy' && $note === '') { throw new DomainException('Vui lòng nhập lý do hủy đơn.'); }
                $this->model()->updateOrder($id, post_string('expected'), post_string('status'), $note, current_user()['id']);
            }, 'admin-order', 'Đã cập nhật trạng thái đơn hàng.', ['id' => $id]);
        }
        $this->page('order', 'Đơn hàng #' . $id, ['order' => $order]);
    }

    public function users(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
            $this->mutation(function (): void {
                $blocked = post_string('blocked');
                if (!in_array($blocked, ['0', '1'], true)) { throw new DomainException('Trạng thái không hợp lệ.'); }
                $this->model()->updateUser((int) post_string('id'), post_string('role'), (int) $blocked, current_user()['id']);
            }, 'admin-users', 'Đã cập nhật quyền và trạng thái tài khoản.');
        }
        $this->page('users', 'Tài khoản', ['listing' => $this->model()->users(query('q'), (int) query('page', '1'))]);
    }

    public function logs(): void
    {
        $this->page('logs', 'Nhật ký hoạt động', ['listing' => $this->model()->logs((int) query('page', '1'))]);
    }
}
