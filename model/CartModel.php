<?php
declare(strict_types=1);
namespace App\Models;

use DomainException;
use PDO;
use Throwable;

/** Giỏ MySQL chỉ thuộc về chủ tài khoản; giá bán luôn lấy từ bảng sách. */
final class CartModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = (new \Database())->getConnection();
    }

    private function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /** Trả về đúng các đầu sách còn bán, có tồn kho; không tin giá gửi từ browser. */
    public function items(int $userId): array
    {
        $rows = $this->query(
            'SELECT ct.maSach AS id, LEAST(ct.soLuong, s.tonKho, 9999999) AS qty
             FROM giohang gh
             JOIN chitietgiohang ct ON ct.maGH = gh.maGH
             JOIN sach s ON s.maSach = ct.maSach
             WHERE gh.maND = ? AND s.an = 0 AND s.tonKho > 0
             ORDER BY ct.maSach',
            [$userId]
        )->fetchAll();

        return array_map(static fn (array $row): array => [
            'id' => (int) $row['id'], 'qty' => (int) $row['qty'],
        ], $rows);
    }

    private function cartIdForUpdate(int $userId): int
    {
        // Khoá hàng giohang để các thao tác cùng tài khoản chạy tuần tự.
        $this->query(
            'INSERT INTO giohang (maND) VALUES (?)
             ON DUPLICATE KEY UPDATE maGH = LAST_INSERT_ID(maGH)',
            [$userId]
        );
        return (int) $this->db->lastInsertId();
    }

    private function transaction(int $userId, callable $work): void
    {
        $this->db->beginTransaction();
        try {
            $work($this->cartIdForUpdate($userId));
            $this->db->commit();
        } catch (Throwable $error) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $error;
        }
    }

    /** Giá chỉ được tính bằng SQL từ sach.giaTien, không đọc giá phía client. */
    private function putLine(int $cartId, int $bookId, int $qty): void
    {
        $this->query(
            'INSERT INTO chitietgiohang (maGH, maSach, soLuong, thanhTien)
             SELECT ?, maSach, ?, ROUND(giaTien * ?, 2) FROM sach WHERE maSach = ?
             ON DUPLICATE KEY UPDATE
                 soLuong = VALUES(soLuong), thanhTien = VALUES(thanhTien)',
            [$cartId, $qty, $qty, $bookId]
        );
    }

    /** Đặt số lượng tuyệt đối: có thể gửi lại nếu mất mạng mà không cộng hai lần. */
    public function setQuantity(int $userId, int $bookId, int $quantity): array
    {
        if ($bookId < 1 || $quantity < 0 || $quantity > 9999999) {
            throw new DomainException('ID sách hoặc số lượng không hợp lệ.');
        }

        $this->transaction($userId, function (int $cartId) use ($bookId, $quantity): void {
            if ($quantity === 0) {
                $this->query('DELETE FROM chitietgiohang WHERE maGH = ? AND maSach = ?', [$cartId, $bookId]);
                return;
            }
            $book = $this->query(
                'SELECT tonKho FROM sach WHERE maSach = ? AND an = 0 FOR UPDATE', [$bookId]
            )->fetch();
            if (!$book || (int) $book['tonKho'] < 1) {
                throw new DomainException('Sách không còn bán hoặc đã hết hàng.');
            }
            $existing = $this->query(
                'SELECT 1 FROM chitietgiohang WHERE maGH = ? AND maSach = ?', [$cartId, $bookId]
            )->fetchColumn();
            if (!$existing && (int) $this->query(
                'SELECT COUNT(*) FROM chitietgiohang WHERE maGH = ?', [$cartId]
            )->fetchColumn() >= 200) {
                throw new DomainException('Giỏ hàng chỉ hỗ trợ tối đa 200 đầu sách.');
            }
            $this->putLine($cartId, $bookId, min($quantity, (int) $book['tonKho']));
        });

        return $this->items($userId);
    }

    /** Gộp cộng số lượng đúng 1 lần cho mỗi mã chuyển giỏ, kể cả retry/cùng nhiều tab. */
    public function mergeGuest(int $userId, string $transferId, array $items): array
    {
        if (!preg_match('/^[a-f0-9]{32}$/D', $transferId) || count($items) > 200) {
            throw new DomainException('Dữ liệu chuyển giỏ không hợp lệ.');
        }
        $guest = [];
        foreach ($items as $line) {
            if (!is_array($line)
                || !isset($line['id'], $line['qty'])
                || !is_int($line['id']) || !is_int($line['qty'])
                || $line['id'] < 1 || $line['qty'] < 1 || $line['qty'] > 9999999) {
                throw new DomainException('Sản phẩm hoặc số lượng trong giỏ không hợp lệ.');
            }
            $guest[$line['id']] = min(9999999, ($guest[$line['id']] ?? 0) + $line['qty']);
        }
        ksort($guest, SORT_NUMERIC);

        $this->transaction($userId, function (int $cartId) use ($userId, $transferId, $guest): void {
            $insert = $this->query(
                'INSERT IGNORE INTO giohang_langop (maND, maLanGop) VALUES (?, ?)',
                [$userId, $transferId]
            );
            if ($insert->rowCount() === 0) return; // Retry đã được xử lý.

            foreach ($guest as $bookId => $qty) {
                $book = $this->query(
                    'SELECT tonKho FROM sach WHERE maSach = ? AND an = 0 FOR UPDATE', [$bookId]
                )->fetch();
                if (!$book || (int) $book['tonKho'] <= 0) continue;
                $old = $this->query(
                    'SELECT soLuong FROM chitietgiohang WHERE maGH = ? AND maSach = ?',
                    [$cartId, $bookId]
                )->fetchColumn();
                $newQty = min(9999999, (int) $book['tonKho'], $qty + (int) ($old ?: 0));
                $this->putLine($cartId, (int) $bookId, $newQty);
            }
        });

        return $this->items($userId);
    }

    /** Xoá phía server; không đụng giỏ khách lưu trên trình duyệt. */
    public function clear(int $userId): array
    {
        $this->transaction($userId, function (int $cartId): void {
            $this->query('DELETE FROM chitietgiohang WHERE maGH = ?', [$cartId]);
        });
        return [];
    }
}
