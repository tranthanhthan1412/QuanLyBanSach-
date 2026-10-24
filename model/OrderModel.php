<?php

declare(strict_types=1);

namespace App\Models;

use PDO;
use DomainException;

final class OrderModel
{
    private PDO $db;

    public function __construct()
    {
        $database = new \Database();
        $this->db = $database->getConnection();
    }

    /**
     * Lấy danh sách đơn hàng của người dùng
     */
    public function getByUser(int $userId): array
    {
        $sql = "
            SELECT
                dh.maDH,
                dh.tenDH,
                dh.tongSL,
                dh.trangThai,
                dh.ghiChu,

				COALESCE(SUM(ctdh.tongTien), 0)
				+ COALESCE(MAX(dh.phiVanChuyen), 0) AS tongTien,

                COUNT(ctdh.maSach) AS soLoaiSach,
                MAX(dh.trangThaiCOD) AS trangThaiCOD,
                (
                    SELECT yc.trangThai
                    FROM yeucaudoitra yc
                    WHERE yc.maDH = dh.maDH
                    ORDER BY yc.maYC DESC
                    LIMIT 1
                ) AS trangThaiDoiTra

            FROM donhang dh

            LEFT JOIN chitietdonhang ctdh
                ON dh.maDH = ctdh.maDH

            WHERE dh.maND = ?

            GROUP BY
                dh.maDH,
                dh.tenDH,
                dh.tongSL,
                dh.trangThai,
                dh.ghiChu

            ORDER BY dh.maDH DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Lấy chi tiết một đơn hàng
     * Chỉ cho phép user xem đơn hàng của chính mình
     */
    public function getById(int $orderId, int $userId): ?array
    {
        $sql = "
            SELECT
                dh.maDH,
                dh.tenDH,
                dh.tongSL,
                dh.trangThai,
                dh.ghiChu,
                dh.maND,

				MAX(dh.tenNguoiNhan) AS tenNguoiNhan,
				MAX(dh.SDTNguoiNhan) AS SDTNguoiNhan,
				MAX(dh.emailNguoiNhan) AS emailNguoiNhan,
				MAX(dh.diaChiGiao) AS diaChiGiao,
				MAX(dh.phuongThucTT) AS phuongThucTT,

				COALESCE(MAX(dh.phiVanChuyen), 0) AS phiVanChuyen,

				COALESCE(SUM(ctdh.tongTien), 0) AS tienSach,

				COALESCE(SUM(ctdh.tongTien), 0)
				    + COALESCE(MAX(dh.phiVanChuyen), 0) AS tongTien

            FROM donhang dh

            LEFT JOIN chitietdonhang ctdh
                ON dh.maDH = ctdh.maDH

            WHERE dh.maDH = ?
              AND dh.maND = ?

            GROUP BY
                dh.maDH,
                dh.tenDH,
                dh.tongSL,
                dh.trangThai,
                dh.ghiChu,
                dh.maND

            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $orderId,
            $userId
        ]);

        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            return null;
        }

        $order['items'] = $this->getItems($orderId);

        return $order;
    }


    /**
     * Khách chỉ được hủy đơn của chính mình khi đang Chờ xác nhận.
     * Khóa bản ghi để không đụng độ khi Admin đang xác nhận và trừ kho.
     * Lý do hủy được lưu kèm ghi chú hiện có, không đổi schema MySQL.
     */
    public function cancelPending(int $orderId, int $userId, string $reason = ''): void
    {
        if ($orderId <= 0 || $userId <= 0) {
            throw new DomainException('Mã đơn hàng không hợp lệ.');
        }
        $reason = trim($reason);
        $length = static fn (string $text): int => function_exists('mb_strlen')
            ? mb_strlen($text, 'UTF-8') : strlen($text);
        if ($length($reason) > 180) {
            throw new DomainException('Lý do hủy không được vượt quá 180 ký tự.');
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare(
                'SELECT trangThai, daTruKho, ghiChu
                 FROM donhang WHERE maDH = ? AND maND = ? FOR UPDATE'
            );
            $stmt->execute([$orderId, $userId]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$order || $order['trangThai'] !== 'ChoXacNhan' || (int) $order['daTruKho'] !== 0) {
                throw new DomainException(
                    'Không thể hủy đơn này. Đơn có thể đã được xác nhận, đã hủy hoặc không thuộc tài khoản của bạn.'
                );
            }

            $note = trim((string) ($order['ghiChu'] ?? ''));
            if ($reason !== '') {
                $note .= ($note === '' ? '' : ' | ') . 'Lý do khách hủy: ' . $reason;
                if ($length($note) > 500) {
                    throw new DomainException('Ghi chú đơn hàng đã dài. Hãy rút ngắn hoặc bỏ trống lý do hủy.');
                }
            }

            $update = $this->db->prepare(
                "UPDATE donhang
                 SET trangThai = 'DaHuy', ghiChu = ?
                 WHERE maDH = ? AND maND = ?
                   AND trangThai = 'ChoXacNhan' AND daTruKho = 0"
            );
            $update->execute([$note, $orderId, $userId]);
            if ($update->rowCount() !== 1) {
                throw new DomainException('Trạng thái đơn vừa thay đổi. Vui lòng tải lại trang.');
            }
            // Chưa được xác nhận nên chưa trừ tồn kho; không cộng kho khi hủy.
            $this->db->commit();
        } catch (\Throwable $error) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $error;
        }
    }

    /**
     * Lấy sách trong đơn hàng
     */
    public function getItems(int $orderId): array
    {
        $sql = "
            SELECT
                ctdh.maDH,
                ctdh.maSach,
                ctdh.soLuong,
                ctdh.tongTien,

                s.tenSach,
                s.giaTien,
                s.moTa,
                s.tonKho,

                tg.tenTG,
                tl.tenTL,
                nxb.tenNXB

            FROM chitietdonhang ctdh

            INNER JOIN sach s
                ON ctdh.maSach = s.maSach

            LEFT JOIN tacgia tg
                ON s.maTG = tg.maTG

            LEFT JOIN theloai tl
                ON s.maTL = tl.maTL

            LEFT JOIN nhaxuatban nxb
                ON s.maNXB = nxb.maNXB

            WHERE ctdh.maDH = ?

            ORDER BY ctdh.maSach
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$orderId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}