<?php

declare(strict_types=1);

namespace App\Models;

use PDO;

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

                COALESCE(SUM(ctdh.tongTien), 0) AS tongTien,

                COUNT(ctdh.maSach) AS soLoaiSach

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

                COALESCE(SUM(ctdh.tongTien), 0) AS tongTien

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