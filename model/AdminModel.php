<?php
declare(strict_types=1);
namespace App\Models;

use DomainException;
use PDO;
use PDOException;
use Throwable;

final class AdminModel
{
    public const CATALOGS = [
        'categories' => ['theloai', 'maTL', 'tenTL', 'Thể loại', 100],
        'authors' => ['tacgia', 'maTG', 'tenTG', 'Tác giả', 100],
        'publishers' => ['nhaxuatban', 'maNXB', 'tenNXB', 'Nhà xuất bản', 150],
    ];
    public const STATUSES = ['ChoXacNhan' => 'Chờ xác nhận', 'DaXacNhan' => 'Đã xác nhận', 'DangGiao' => 'Đang giao', 'DaGiao' => 'Đã giao', 'DaHuy' => 'Đã hủy'];
    private PDO $db;
    public function __construct() { $this->db = (new \Database())->getConnection(); }

    private function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    private function transaction(callable $work): mixed
    {
        $this->db->beginTransaction();
        try {
            $result = $work();
            $this->db->commit();
            return $result;
        } catch (Throwable $error) {
            $this->db->rollBack();
            if ($error instanceof PDOException && in_array($error->errorInfo[1] ?? 0, [1062, 1451, 1452], true)) {
                throw new DomainException('Dữ liệu bị trùng hoặc đang được sử dụng. Vui lòng tải lại và kiểm tra thông tin.');
            }
            throw $error;
        }
    }

    private function audit(int $actor, string $action, string $target, string $detail): void
    {
        $this->query('INSERT INTO nhatkyquantri (maND, hanhDong, doiTuong, chiTiet) VALUES (?, ?, ?, ?)', [$actor, $action, $target, $detail]);
    }

    private function paginate(string $from, string $select, array $params, string $order, int $page): array
    {
        $total = (int) $this->query('SELECT COUNT(*) ' . $from, $params)->fetchColumn();
        $pages = max(1, (int) ceil($total / 12));
        $page = max(1, min($pages, $page));
        $offset = ($page - 1) * 12;
        $rows = $this->query("SELECT $select $from ORDER BY $order LIMIT 12 OFFSET $offset", $params)->fetchAll();
        return compact('rows', 'page', 'pages', 'total');
    }

    public function overview(): array
    {
        return $this->db->query("SELECT
            (SELECT COUNT(*) FROM sach) AS books,
            (SELECT COALESCE(SUM(tonKho), 0) FROM sach WHERE an = 0) AS stock,
            (SELECT COUNT(*) FROM sach WHERE an = 0 AND tonKho <= 5) AS low_stock,
            (SELECT COUNT(*) FROM nguoidung nd JOIN vaitro vt ON nd.maVT = vt.maVT WHERE vt.tenVT = 'User') AS customers,
            (SELECT COUNT(*) FROM donhang) AS orders,
            (SELECT COUNT(*) FROM donhang WHERE trangThai IN ('ChoXacNhan', 'Chờ xác nhận', 'Đang xử lý')) AS pending,
            (SELECT COALESCE(SUM(ct.tongTien), 0) FROM chitietdonhang ct JOIN donhang dh ON dh.maDH = ct.maDH
             WHERE dh.trangThai IN ('DaGiao', 'HoanThanh', 'Đã giao', 'Hoàn thành')) AS revenue")->fetch();
    }

    public function lowStock(): array
    {
        return $this->query('SELECT maSach, tenSach, tonKho, hinhAnh FROM sach WHERE an = 0 AND tonKho <= 5 ORDER BY tonKho, maSach LIMIT 6')->fetchAll();
    }

    public function products(string $search, string $visibility, string $category, int $page): array
    {
        $where = 'WHERE (s.tenSach LIKE ? OR tg.tenTG LIKE ?)';
        $params = ['%' . $search . '%', '%' . $search . '%'];
        if (in_array($visibility, ['visible', 'hidden'], true)) { $where .= ' AND s.an = ?'; $params[] = $visibility === 'hidden' ? 1 : 0; }
        if ($visibility === 'low') { $where .= ' AND s.an = 0 AND s.tonKho <= 5'; }
        if ($category !== '') { $where .= ' AND s.maTL = ?'; $params[] = $category; }
        return $this->paginate("FROM sach s JOIN theloai tl ON tl.maTL = s.maTL JOIN tacgia tg ON tg.maTG = s.maTG $where",
            's.*, tl.tenTL, tg.tenTG', $params, 's.maSach DESC', $page);
    }

    public function product(int $id): ?array
    {
        return $this->query('SELECT * FROM sach WHERE maSach = ?', [$id])->fetch() ?: null;
    }

    public function options(string $kind): array
    {
        [$table, $id, $name] = self::CATALOGS[$kind];
        return $this->query("SELECT $id AS id, $name AS name FROM $table ORDER BY $name, $id")->fetchAll();
    }

    public function saveProduct(array $data, int $id, int $version, int $actor): int
    {
        return $this->transaction(function () use ($data, $id, $version, $actor): int {
            $values = [$data['tenSach'], $data['moTa'], $data['giaTien'], $data['tonKho'], $data['maTL'], $data['maTG'], $data['maNXB'], $data['hinhAnh'], $data['an']];
            if ($id > 0) {
                $old = $this->query('SELECT * FROM sach WHERE maSach = ? FOR UPDATE', [$id])->fetch();
                if (!$old) { throw new DomainException('Sách không còn tồn tại.'); }
                if ((int) $old['phienBan'] !== $version) { throw new DomainException('Sách hoặc tồn kho đã thay đổi ở phiên khác. Hãy tải lại trang trước khi sửa.'); }
                $this->query('UPDATE sach SET tenSach = ?, moTa = ?, giaTien = ?, tonKho = ?, maTL = ?, maTG = ?, maNXB = ?, hinhAnh = ?, an = ?, phienBan = phienBan + 1 WHERE maSach = ?', [...$values, $id]);
                $detail = $data['tenSach'] . '; tồn kho: ' . $old['tonKho'] . ' → ' . $data['tonKho'] . '; giá: ' . $old['giaTien'] . ' → ' . $data['giaTien'];
            } else {
                $this->query('INSERT INTO sach (tenSach, moTa, giaTien, tonKho, maTL, maTG, maNXB, hinhAnh, an) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)', $values);
                $id = (int) $this->db->lastInsertId();
                $detail = $data['tenSach'];
            }
            $this->audit($actor, $version > 0 ? 'Cập nhật sách' : 'Thêm sách', 'Sách #' . $id, $detail);
            return $id;
        });
    }

    public function deleteProduct(int $id, int $version, int $actor): void
    {
        $this->transaction(function () use ($id, $version, $actor): void {
            $book = $this->query('SELECT * FROM sach WHERE maSach = ? FOR UPDATE', [$id])->fetch();
            if (!$book) { throw new DomainException('Không tìm thấy sách.'); }
            if ((int) $book['phienBan'] !== $version) { throw new DomainException('Sách đã thay đổi. Vui lòng tải lại trang.'); }
            if ($this->query('SELECT 1 FROM chitietdonhang WHERE maSach = ? LIMIT 1', [$id])->fetchColumn()) {
                throw new DomainException('Sách đã có trong đơn hàng, không thể xóa. Hãy sửa sách và chọn Ẩn khỏi cửa hàng.');
            }
            $this->query('DELETE FROM sach WHERE maSach = ?', [$id]);
            $this->audit($actor, 'Xóa sách', 'Sách #' . $id, $book['tenSach']);
        });
    }

    public function catalog(string $kind, string $search, int $page): array
    {
        [$table, $id, $name] = self::CATALOGS[$kind];
        return $this->paginate("FROM $table t WHERE t.$name LIKE ?", "t.$id AS id, t.$name AS name, (SELECT COUNT(*) FROM sach s WHERE s.$id = t.$id) AS books",
            ['%' . $search . '%'], "t.$id DESC", $page);
    }

    public function catalogItem(string $kind, int $id): ?array
    {
        [$table, $key, $name] = self::CATALOGS[$kind];
        return $this->query("SELECT $key AS id, $name AS name FROM $table WHERE $key = ?", [$id])->fetch() ?: null;
    }

    public function saveCatalog(string $kind, int $id, string $name, int $actor): void
    {
        [$table, $key, $column, $label] = self::CATALOGS[$kind];
        $this->transaction(function () use ($table, $key, $column, $label, $id, $name, $actor): void {
            if ($id > 0) {
                if (!$this->query("SELECT $key FROM $table WHERE $key = ? FOR UPDATE", [$id])->fetch()) { throw new DomainException('Không tìm thấy dữ liệu.'); }
                $this->query("UPDATE $table SET $column = ? WHERE $key = ?", [$name, $id]);
            } else {
                $this->query("INSERT INTO $table ($column) VALUES (?)", [$name]);
                $id = (int) $this->db->lastInsertId();
            }
            $this->audit($actor, 'Lưu ' . $label, $label . ' #' . $id, $name);
        });
    }

    public function deleteCatalog(string $kind, int $id, int $actor): void
    {
        [$table, $key, $column, $label] = self::CATALOGS[$kind];
        $this->transaction(function () use ($table, $key, $column, $label, $id, $actor): void {
            $row = $this->query("SELECT $column AS name FROM $table WHERE $key = ? FOR UPDATE", [$id])->fetch();
            if (!$row) { throw new DomainException('Không tìm thấy dữ liệu.'); }
            if ($this->query("SELECT 1 FROM sach WHERE $key = ? LIMIT 1", [$id])->fetchColumn()) { throw new DomainException('Mục này đang được sử dụng bởi sách. Hãy chuyển sách sang mục khác trước khi xóa.'); }
            $this->query("DELETE FROM $table WHERE $key = ?", [$id]);
            $this->audit($actor, 'Xóa ' . $label, $label . ' #' . $id, $row['name']);
        });
    }

    public static function statusCode(string $status): string
    {
        return match ($status) {
            'Chờ xác nhận', 'Đang xử lý' => 'ChoXacNhan', 'Đã xác nhận' => 'DaXacNhan',
            'Đang giao' => 'DangGiao', 'Đã giao', 'Hoàn thành', 'HoanThanh' => 'DaGiao', 'Đã hủy', 'Hủy' => 'DaHuy',
            default => $status,
        };
    }

    public static function nextStatuses(string $status): array
    {
        return match (self::statusCode($status)) {
            'ChoXacNhan' => ['DaXacNhan', 'DaHuy'], 'DaXacNhan' => ['DangGiao', 'DaHuy'],
            'DangGiao' => ['DaGiao', 'DaHuy'], default => [],
        };
    }

    public function orders(string $search, string $status, int $page): array
    {
        $where = 'WHERE (CAST(dh.maDH AS CHAR) LIKE ? OR nd.tenND LIKE ? OR nd.email LIKE ?)';
        $params = array_fill(0, 3, '%' . $search . '%');
        if (isset(self::STATUSES[$status])) {
            $aliases = match ($status) {
                'ChoXacNhan' => ['ChoXacNhan', 'Chờ xác nhận', 'Đang xử lý'], 'DaXacNhan' => ['DaXacNhan', 'Đã xác nhận'],
                'DangGiao' => ['DangGiao', 'Đang giao'], 'DaGiao' => ['DaGiao', 'HoanThanh', 'Đã giao', 'Hoàn thành'], default => ['DaHuy', 'Đã hủy', 'Hủy'],
            };
            $where .= ' AND dh.trangThai IN (' . implode(',', array_fill(0, count($aliases), '?')) . ')';
            $params = [...$params, ...$aliases];
        }
        return $this->paginate("FROM donhang dh JOIN nguoidung nd ON nd.maND = dh.maND $where",
            'dh.*, nd.tenND, nd.email, (SELECT COALESCE(SUM(tongTien), 0) FROM chitietdonhang WHERE maDH = dh.maDH) AS total', $params, 'dh.maDH DESC', $page);
    }

    public function order(int $id): ?array
    {
        $order = $this->query('SELECT dh.*, nd.tenND, nd.email, nd.SDT, nd.diaChi FROM donhang dh JOIN nguoidung nd ON nd.maND = dh.maND WHERE dh.maDH = ?', [$id])->fetch();
        if (!$order) { return null; }
        $order['items'] = $this->query('SELECT ct.*, s.tenSach, s.tonKho FROM chitietdonhang ct JOIN sach s ON s.maSach = ct.maSach WHERE ct.maDH = ? ORDER BY ct.maSach', [$id])->fetchAll();
        $order['total'] = array_sum(array_column($order['items'], 'tongTien'));
        return $order;
    }

    public function updateOrder(int $id, string $expected, string $status, string $note, int $actor): void
    {
        $this->transaction(function () use ($id, $expected, $status, $note, $actor): void {
            $order = $this->query('SELECT * FROM donhang WHERE maDH = ? FOR UPDATE', [$id])->fetch();
            if (!$order) { throw new DomainException('Không tìm thấy đơn hàng.'); }
            if ($order['trangThai'] !== $expected) { throw new DomainException('Trạng thái đã thay đổi. Hãy tải lại đơn hàng.'); }
            if (!in_array($status, self::nextStatuses($order['trangThai']), true)) { throw new DomainException('Không thể chuyển sang trạng thái này.'); }
            $deducted = (int) $order['daTruKho'];
            $items = $this->query('SELECT * FROM chitietdonhang WHERE maDH = ? ORDER BY maSach FOR UPDATE', [$id])->fetchAll();
            if ($status !== 'DaHuy' && !$items) { throw new DomainException('Đơn hàng chưa có sản phẩm.'); }
            if ($status === 'DaXacNhan' && !$deducted) {
                foreach ($items as $item) {
                    $changed = $this->query('UPDATE sach SET tonKho = tonKho - ?, phienBan = phienBan + 1 WHERE maSach = ? AND tonKho >= ? AND an = 0', [$item['soLuong'], $item['maSach'], $item['soLuong']]);
                    if (!$changed->rowCount()) { throw new DomainException('Sách #' . $item['maSach'] . ' không đủ tồn kho hoặc đã ẩn. Đơn chưa được xác nhận.'); }
                }
                $deducted = 1;
            }
            if ($status === 'DaHuy' && $deducted) {
                foreach ($items as $item) { $this->query('UPDATE sach SET tonKho = tonKho + ?, phienBan = phienBan + 1 WHERE maSach = ?', [$item['soLuong'], $item['maSach']]); }
                $deducted = 0;
            }
            $this->query('UPDATE donhang SET trangThai = ?, daTruKho = ? WHERE maDH = ?', [$status, $deducted, $id]);
            $this->audit($actor, 'Đổi trạng thái đơn', 'Đơn #' . $id, $order['trangThai'] . ' → ' . $status . ($note !== '' ? '; ' . $note : ''));
        });
    }

    public function users(string $search, int $page): array
    {
        return $this->paginate('FROM nguoidung nd JOIN vaitro vt ON vt.maVT = nd.maVT WHERE (nd.tenND LIKE ? OR nd.email LIKE ?)',
            'nd.maND, nd.tenND, nd.email, nd.SDT, nd.biKhoa, vt.tenVT, (SELECT COUNT(*) FROM donhang WHERE maND = nd.maND) AS orders',
            ['%' . $search . '%', '%' . $search . '%'], 'nd.maND DESC', $page);
    }

    public function updateUser(int $id, string $role, int $blocked, int $actor): void
    {
        if (!in_array($role, ['Admin', 'User'], true) || !in_array($blocked, [0, 1], true)) { throw new DomainException('Quyền hoặc trạng thái không hợp lệ.'); }
        if ($id === $actor) { throw new DomainException('Không thể tự thay đổi quyền hoặc khóa tài khoản đang đăng nhập.'); }
        $this->transaction(function () use ($id, $role, $blocked, $actor): void {
            // Serialize role changes to preserve at least one active admin.
            $roles = $this->query('SELECT * FROM vaitro ORDER BY maVT FOR UPDATE')->fetchAll();
            $ids = array_column($roles, 'maVT', 'tenVT');
            $user = $this->query('SELECT * FROM nguoidung WHERE maND = ? FOR UPDATE', [$id])->fetch();
            if (!$user || !isset($ids[$role])) { throw new DomainException('Không tìm thấy tài khoản hoặc vai trò.'); }
            if ((int) $user['maVT'] === (int) ($ids['Admin'] ?? 0) && !$user['biKhoa'] && ($role !== 'Admin' || $blocked)) {
                $admins = $this->query('SELECT maND FROM nguoidung WHERE maVT = ? AND biKhoa = 0 FOR UPDATE', [$ids['Admin']])->fetchAll();
                if (count($admins) <= 1) { throw new DomainException('Cần giữ ít nhất một quản trị viên đang hoạt động.'); }
            }
            $this->query('UPDATE nguoidung SET maVT = ?, biKhoa = ? WHERE maND = ?', [$ids[$role], $blocked, $id]);
            $this->audit($actor, 'Cập nhật tài khoản', 'Tài khoản #' . $id, $user['email'] . '; ' . $role . '; ' . ($blocked ? 'Khóa' : 'Hoạt động'));
        });
    }

    public function logs(int $page): array
    {
        return $this->paginate('FROM nhatkyquantri n LEFT JOIN nguoidung nd ON nd.maND = n.maND', 'n.*, nd.tenND', [], 'n.id DESC', $page);
    }
}
