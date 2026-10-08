<?php
declare(strict_types=1);

namespace App\Models;

use DomainException;
use PDO;
use RuntimeException;
use Throwable;

/** Creates an order from server-side book prices; never trusts client totals. */
final class CodOrderModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = (new \Database())->getConnection();
    }

    private static function cents(string $price): int
    {
        if (!preg_match('/^(\d{1,13})(?:\.(\d{1,2}))?$/D', $price, $matches)) {
            throw new RuntimeException('Giá sách trong cơ sở dữ liệu không hợp lệ.');
        }
        return ((int) $matches[1]) * 100 + (int) str_pad($matches[2] ?? '0', 2, '0');
    }

    private static function amount(int $cents): string
    {
        return sprintf('%d.%02d', intdiv($cents, 100), $cents % 100);
    }

    /** @param array<int, array{id:int,qty:int}> $items */
    public function create(int $userId, array $items, array $delivery): int
    {
        if (!$items || count($items) > 50) {
            throw new DomainException('Giỏ hàng phải có từ 1 đến 50 đầu sách.');
        }

        $requested = [];
        foreach ($items as $item) {
            if (!is_array($item) || !isset($item['id'], $item['qty'])
                || !is_int($item['id']) || !is_int($item['qty'])
                || $item['id'] <= 0 || $item['qty'] < 1 || $item['qty'] > 100) {
                throw new DomainException('Giỏ hàng chứa sách hoặc số lượng không hợp lệ.');
            }
            $requested[$item['id']] = ($requested[$item['id']] ?? 0) + $item['qty'];
            if ($requested[$item['id']] > 100) {
                throw new DomainException('Mỗi đầu sách được đặt tối đa 100 cuốn.');
            }
        }
        ksort($requested, SORT_NUMERIC);

        $this->db->beginTransaction();
        try {
            $marks = implode(',', array_fill(0, count($requested), '?'));
            // A consistent lock order also cooperates with the existing admin stock workflow.
            $query = $this->db->prepare("SELECT maSach, tenSach, giaTien, tonKho, an
                FROM sach WHERE maSach IN ($marks) ORDER BY maSach FOR UPDATE");
            $query->execute(array_keys($requested));
            $books = $query->fetchAll(PDO::FETCH_ASSOC);
            if (count($books) !== count($requested)) {
                throw new DomainException('Một số sách trong giỏ không còn tồn tại. Hãy tải lại trang.');
            }

            $count = 0;
            $subtotal = 0;
            $lines = [];
            foreach ($books as $book) {
                $id = (int) $book['maSach'];
                $qty = $requested[$id];
                if ((int) $book['an'] !== 0 || (int) $book['tonKho'] < $qty) {
                    throw new DomainException('Sách “' . $book['tenSach'] . '” không đủ hàng hoặc đã ngừng bán.');
                }
                $line = self::cents((string) $book['giaTien']) * $qty;
                $subtotal += $line;
                $count += $qty;
                $lines[] = [$id, $qty, self::amount($line)];
            }
            $freeFrom = ((int) config('free_shipping_from')) * 100;
            $shipping = $subtotal >= $freeFrom ? 0 : ((int) config('shipping_fee')) * 100;
            $total = $subtotal + $shipping;
            if ($total > 999999999999999) { // Bound by DECIMAL(15,2).
                throw new DomainException('Giá trị đơn hàng vượt giới hạn hệ thống.');
            }
            $stmt = $this->db->prepare('INSERT INTO donhang
                (tenDH, tongSL, trangThai, ghiChu, maND, ngayTao, daTruKho,
                 tenNguoiNhan, SDTNguoiNhan, emailNguoiNhan, diaChiGiao,
                 phuongThucTT, phiVanChuyen, tongThanhToan)
                 VALUES (?, ?, ?, ?, ?, NOW(), 0, ?, ?, ?, ?, ?, ?, ?)');
            $stmt->execute([
                'Đơn hàng COD', $count, 'ChoXacNhan', $delivery['note'], $userId,
                $delivery['name'], $delivery['phone'], $delivery['email'] ?: null,
                $delivery['address'], 'COD', self::amount($shipping), self::amount($total),
            ]);
            $orderId = (int) $this->db->lastInsertId();
            $insertLine = $this->db->prepare('INSERT INTO chitietdonhang
                (maDH, maSach, soLuong, tongTien) VALUES (?, ?, ?, ?)');
            foreach ($lines as [$id, $qty, $lineTotal]) {
                $insertLine->execute([$orderId, $id, $qty, $lineTotal]);
            }
            // Stock is deducted only when Admin confirms, in AdminModel::updateOrder().
            $this->db->commit();
            return $orderId;
        } catch (Throwable $exception) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $exception;
        }
    }
}
