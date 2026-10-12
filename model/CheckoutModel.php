<?php
declare(strict_types=1);
namespace App\Models;

use DomainException;
use PDO;

final class CheckoutModel
{
    private PDO $db;
    public function __construct() { $this->db = (new \Database())->getConnection(); }

    public function find(int $id, int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM donhang WHERE maDH = ? AND maND = ?');
        $stmt->execute([$id, $userId]);
        $order = $stmt->fetch();
        if (!$order) { return null; }
        $stmt = $this->db->prepare('SELECT ct.*, COALESCE(ct.tenSachLucMua, s.tenSach) AS tenSach, tg.tenTG
            FROM chitietdonhang ct JOIN sach s ON s.maSach = ct.maSach
            JOIN tacgia tg ON tg.maTG = s.maTG WHERE ct.maDH = ? ORDER BY ct.maSach');
        $stmt->execute([$id]);
        $order['items'] = $stmt->fetchAll();
        $order['subtotal'] = array_sum(array_column($order['items'], 'tongTien'));
        $order['tongTien'] = $order['subtotal'];
        $order['total'] = $order['subtotal'] + (float) $order['phiVanChuyen'];
        return $order;
    }

    public function byToken(string $token, int $userId): ?array
    {
        $stmt = $this->db->prepare('SELECT maDH FROM donhang WHERE checkoutToken = ? AND maND = ?');
        $stmt->execute([$token, $userId]);
        $id = $stmt->fetchColumn();
        return $id ? $this->find((int) $id, $userId) : null;
    }

    public function create(array $user, array $data, array $items, string $token): array
    {
        if (!$items || count($items) > 100 || !array_is_list($items)) {
            throw new DomainException('Giỏ hàng cần từ 1 đến 100 đầu sách.');
        }
        $quantities = [];
        foreach ($items as $item) {
            if (!is_array($item) || !is_int($item['id'] ?? null) || $item['id'] < 1
                || !is_int($item['qty'] ?? null) || $item['qty'] < 1 || $item['qty'] > 99999
                || isset($quantities[$item['id']])) {
                throw new DomainException('Sách hoặc số lượng trong giỏ không hợp lệ.');
            }
            $quantities[$item['id']] = $item['qty'];
        }
        ksort($quantities);
        $this->db->beginTransaction();
        try {
            $bookQuery = $this->db->prepare('SELECT maSach, tenSach, giaTien, tonKho, an FROM sach WHERE maSach = ? FOR UPDATE');
            $lines = [];
            $subtotalCents = 0;
            foreach ($quantities as $id => $qty) {
                $bookQuery->execute([$id]);
                $book = $bookQuery->fetch();
                if (!$book || (int) $book['an'] || (int) $book['tonKho'] < $qty) {
                    throw new DomainException('Sách #' . $id . ' đã ẩn, hết hàng hoặc không đủ số lượng. Vui lòng cập nhật giỏ.');
                }
                $lineCents = (int) round((float) $book['giaTien'] * 100) * $qty;
                $subtotalCents += $lineCents;
                if ($subtotalCents > 9999999999999) { throw new DomainException('Giá trị đơn hàng vượt giới hạn.'); }
                $lines[] = [$id, $qty, number_format($lineCents / 100, 2, '.', ''), $book['tenSach']];
            }
            $shipping = $subtotalCents === 0 || $subtotalCents >= (int) round(config('free_shipping_from') * 100)
                ? 0 : config('shipping_fee');
            $stmt = $this->db->prepare("INSERT INTO donhang
                (tenDH, tongSL, trangThai, ghiChu, maND, ngayTao, tenNguoiNhan, emailNhan, sdtNhan, diaChiNhan, phiVanChuyen, checkoutToken, trangThaiEmail)
                VALUES (?, ?, 'ChoXacNhan', ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([
                'Đơn sách của ' . $data['name'], array_sum($quantities), $data['note'], $user['id'],
                (new \DateTimeImmutable('now', new \DateTimeZone('Asia/Ho_Chi_Minh')))->format('Y-m-d H:i:s'),
                $data['name'], $user['email'], $data['phone'],
                $data['address'] . ', ' . $data['ward'] . ', ' . $data['province'], $shipping, $token,
            ]);
            $id = (int) $this->db->lastInsertId();
            $stmt = $this->db->prepare('INSERT INTO chitietdonhang (maDH, maSach, soLuong, tongTien, tenSachLucMua) VALUES (?, ?, ?, ?, ?)');
            foreach ($lines as $line) { $stmt->execute([$id, ...$line]); }
            // Inventory is deducted by the existing admin confirmation transaction.
            $this->db->commit();
            return $this->find($id, $user['id']);
        } catch (\Throwable $error) {
            if ($this->db->inTransaction()) { $this->db->rollBack(); }
            throw $error;
        }
    }

    public function recordMailStatus(int $id, string $status): void
    {
        $stmt = $this->db->prepare('UPDATE donhang SET trangThaiEmail = ? WHERE maDH = ?');
        $stmt->execute([$status, $id]);
    }
}
