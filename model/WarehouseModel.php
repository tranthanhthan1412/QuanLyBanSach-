<?php
declare(strict_types=1);
namespace App\Models;

use DateTimeImmutable;
use DomainException;
use PDO;
use PDOException;
use Throwable;

/** Phiếu nhập kho là chứng từ bất biến: ghi phiếu và cộng tồn kho trong một transaction. */
final class WarehouseModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = (new \Database())->getConnection();
    }

    private function query(string $sql, array $params = []): \PDOStatement
    {
        $statement = $this->db->prepare($sql);
        $statement->execute($params);
        return $statement;
    }

    public function books(): array
    {
        // Có thể nhập thêm cả sách đang ẩn, không tự đổi trạng thái hiển thị.
        return $this->query('SELECT maSach, tenSach, tonKho, an FROM sach ORDER BY tenSach, maSach')->fetchAll();
    }

    public function receipts(int $page): array
    {
        $total = (int) $this->query('SELECT COUNT(*) FROM phieunhap')->fetchColumn();
        $pages = max(1, (int) ceil($total / 12));
        $page = max(1, min($pages, $page));
        $offset = ($page - 1) * 12;
        $rows = $this->query('SELECT p.*, nd.tenND,
            (SELECT COUNT(*) FROM chitietphieunhap ct WHERE ct.maPN = p.maPN) AS soDauSach,
            (SELECT COALESCE(SUM(ct.soLuong), 0) FROM chitietphieunhap ct WHERE ct.maPN = p.maPN) AS tongSoLuong
            FROM phieunhap p JOIN nguoidung nd ON nd.maND = p.maND
            ORDER BY p.maPN DESC LIMIT 12 OFFSET ' . $offset)->fetchAll();
        return compact('rows', 'total', 'page', 'pages');
    }

    public function receipt(int $id): ?array
    {
        $receipt = $this->query('SELECT p.*, nd.tenND FROM phieunhap p
            JOIN nguoidung nd ON nd.maND = p.maND WHERE p.maPN = ?', [$id])->fetch();
        if (!$receipt) { return null; }
        $receipt['items'] = $this->query('SELECT ct.*, s.tonKho, s.an
            FROM chitietphieunhap ct JOIN sach s ON s.maSach = ct.maSach
            WHERE ct.maPN = ? ORDER BY ct.maSach', [$id])->fetchAll();
        return $receipt;
    }

    public function byToken(string $token): ?int
    {
        if (!preg_match('/^[a-f0-9]{64}$/D', $token)) { return null; }
        $id = $this->query('SELECT maPN FROM phieunhap WHERE requestToken = ?', [$token])->fetchColumn();
        return $id === false ? null : (int) $id;
    }

    private static function priceCents(string $value): int
    {
        if (!preg_match('/^\d{1,8}(?:\.\d{1,2})?$/D', $value)) {
            throw new DomainException('Đơn giá nhập phải từ 0 đến 99.999.999,99 và tối đa 2 chữ số thập phân.');
        }
        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '');
        return (int) $whole * 100 + (int) str_pad($fraction, 2, '0');
    }

    private static function decimal(int $cents): string
    {
        return intdiv($cents, 100) . '.' . str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }

    public function create(array $form, string $token, int $actor): int
    {
        if (!preg_match('/^[a-f0-9]{64}$/D', $token)) {
            throw new DomainException('Phiên nhập kho không hợp lệ.');
        }
        // Có thể nhận lại request sau timeout; tuyệt đối không cộng tồn kho lần thứ hai.
        $existing = $this->byToken($token);
        if ($existing !== null) { return $existing; }

        $day = $form['date'] ?? '';
        if (!is_string($day) || !preg_match('/^\d{4}-\d{2}-\d{2}$/D', $day)) {
            throw new DomainException('Ngày nhập không hợp lệ.');
        }
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $day);
        if (!$date || $date->format('Y-m-d') !== $day || $day > date('Y-m-d') || $day < '2000-01-01') {
            throw new DomainException('Ngày nhập không hợp lệ hoặc ở tương lai.');
        }
        $supplier = trim((string) ($form['supplier'] ?? ''));
        $note = trim((string) ($form['note'] ?? ''));
        if (mb_strlen($supplier) > 150 || mb_strlen($note) > 500) {
            throw new DomainException('Nhà cung cấp tối đa 150 ký tự, ghi chú tối đa 500 ký tự.');
        }
        $rawItems = $form['items'] ?? [];
        if (!is_array($rawItems) || count($rawItems) > 20) {
            throw new DomainException('Một phiếu nhập tối đa 20 đầu sách.');
        }
        $items = [];
        foreach ($rawItems as $raw) {
            if (!is_array($raw)) { throw new DomainException('Dữ liệu sách nhập không hợp lệ.'); }
            $bookId = trim((string) ($raw['book_id'] ?? ''));
            $quantity = trim((string) ($raw['quantity'] ?? ''));
            $price = trim((string) ($raw['unit_price'] ?? ''));
            if ($bookId === '' && $quantity === '' && $price === '') { continue; }
            if (!ctype_digit($bookId) || (int) $bookId <= 0) {
                throw new DomainException('Vui lòng chọn sách hợp lệ cho từng dòng nhập.');
            }
            if (!preg_match('/^[1-9][0-9]{0,5}$/D', $quantity) || (int) $quantity > 100000) {
                throw new DomainException('Số lượng nhập mỗi dòng phải từ 1 đến 100.000.');
            }
            $id = (int) $bookId;
            if (isset($items[$id])) { throw new DomainException('Không được nhập trùng sách trong cùng một phiếu.'); }
            $qty = (int) $quantity;
            $cents = self::priceCents($price);
            $items[$id] = ['quantity' => $qty, 'unit_price' => self::decimal($cents), 'lineCents' => $qty * $cents];
        }
        if (!$items) { throw new DomainException('Vui lòng chọn ít nhất một sách để nhập kho.'); }

        ksort($items, SORT_NUMERIC);
        $totalCents = array_sum(array_column($items, 'lineCents'));
        $this->db->beginTransaction();
        try {
            $existing = $this->byToken($token);
            if ($existing !== null) {
                $this->db->commit();
                return $existing;
            }
            // Khóa theo mã sách tăng dần để giảm nguy cơ deadlock khi nhập đồng thời.
            $ids = array_keys($items);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $locked = $this->query("SELECT maSach, tenSach, tonKho FROM sach
                WHERE maSach IN ($placeholders) ORDER BY maSach FOR UPDATE", $ids)->fetchAll();
            if (count($locked) !== count($items)) { throw new DomainException('Một số sách không còn tồn tại.'); }
            foreach ($locked as $book) {
                $id = (int) $book['maSach'];
                if ((int) $book['tonKho'] + $items[$id]['quantity'] > 9999999) {
                    throw new DomainException('Tồn kho sách "' . $book['tenSach'] . '" sẽ vượt giới hạn 9.999.999.');
                }
            }

            $this->query('INSERT INTO phieunhap (maND, ngayNhap, nhaCungCap, ghiChu, tongTienNhap, trangThai, requestToken)
                VALUES (?, ?, ?, ?, ?, ?, ?)', [
                $actor, $day, $supplier !== '' ? $supplier : null,
                $note !== '' ? $note : null, self::decimal($totalCents), 'DaNhap', $token,
            ]);
            $receiptId = (int) $this->db->lastInsertId();
            foreach ($locked as $book) {
                $id = (int) $book['maSach'];
                $item = $items[$id];
                $this->query('INSERT INTO chitietphieunhap
                    (maPN, maSach, tenSachLucNhap, soLuong, donGiaNhap, thanhTien)
                    VALUES (?, ?, ?, ?, ?, ?)', [
                    $receiptId, $id, $book['tenSach'], $item['quantity'],
                    $item['unit_price'], self::decimal($item['lineCents']),
                ]);
                $changed = $this->query('UPDATE sach SET tonKho = tonKho + ?, phienBan = phienBan + 1
                    WHERE maSach = ? AND tonKho <= ?', [$item['quantity'], $id, 9999999 - $item['quantity']]);
                if ($changed->rowCount() !== 1) { throw new DomainException('Tồn kho đã thay đổi, vui lòng lập lại phiếu nhập.'); }
            }
            $this->query('INSERT INTO nhatkyquantri (maND, hanhDong, doiTuong, chiTiet)
                VALUES (?, ?, ?, ?)', [
                $actor, 'Nhập kho', 'Phiếu nhập #' . $receiptId,
                count($items) . ' đầu sách; tổng số lượng: ' . array_sum(array_column($items, 'quantity')) .
                '; tổng giá nhập: ' . self::decimal($totalCents),
            ]);
            $this->db->commit();
            return $receiptId;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) { $this->db->rollBack(); }
            if ($exception instanceof PDOException && (int) ($exception->errorInfo[1] ?? 0) === 1062) {
                $previous = $this->byToken($token);
                if ($previous !== null) { return $previous; }
            }
            throw $exception;
        }
    }
}
