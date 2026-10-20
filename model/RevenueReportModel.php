<?php
declare(strict_types=1);

namespace App\Models;

use DateTimeImmutable;
use DomainException;
use PDO;

final class RevenueReportModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = (new \Database())->getConnection();
    }

    // Thống kê đơn hàng và doanh thu theo khoảng ngày
    public function summary(string $from, string $to): array
    {
        $start = DateTimeImmutable::createFromFormat('!Y-m-d', $from);
        $end = DateTimeImmutable::createFromFormat('!Y-m-d', $to);

        if (!$start || !$end
            || $start->format('Y-m-d') !== $from
            || $end->format('Y-m-d') !== $to
            || $start > $end) {
            throw new DomainException('Khoảng thời gian không hợp lệ.');
        }

        $sql = "
            SELECT
                COUNT(*) AS total_orders,

                COALESCE(SUM(CASE
                    WHEN dh.trangThai IN (
                        'DaGiao', 'HoanThanh',
                        'Đã giao', 'Hoàn thành'
                    )
                    THEN 1 ELSE 0
                END), 0) AS completed_orders,

                COALESCE(SUM(CASE
                    WHEN dh.trangThai IN (
                        'DaHuy', 'Đã hủy', 'Hủy'
                    )
                    THEN 1 ELSE 0
                END), 0) AS cancelled_orders,

                COALESCE(SUM(CASE
                    WHEN dh.trangThai IN (
                        'DaGiao', 'HoanThanh',
                        'Đã giao', 'Hoàn thành'
                    )
                    THEN (
                        SELECT COALESCE(SUM(ct.tongTien), 0)
                        FROM chitietdonhang ct
                        WHERE ct.maDH = dh.maDH
                    )
                    ELSE 0
                END), 0) AS revenue

            FROM donhang dh

            WHERE dh.ngayTao >= :from_date
              AND dh.ngayTao < :until_date
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'from_date' => $from . ' 00:00:00',
            'until_date' => $end->modify('+1 day')
                ->format('Y-m-d 00:00:00'),
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
	
	// Thống kê 10 cuốn sách bán chạy nhất trong khoảng thời gian
	public function bestSellers(string $from, string $to): array
	{
	    $start = DateTimeImmutable::createFromFormat('!Y-m-d', $from);
	    $end = DateTimeImmutable::createFromFormat('!Y-m-d', $to);

	    if (!$start || !$end
	        || $start->format('Y-m-d') !== $from
	        || $end->format('Y-m-d') !== $to
	        || $start > $end) {
	        throw new DomainException('Khoảng thời gian không hợp lệ.');
	    }

	    $sql = "
	        SELECT
	            ct.maSach,
	            s.tenSach,
	            SUM(ct.soLuong) AS soLuongBan,
	            COALESCE(SUM(ct.tongTien), 0) AS doanhThuSach

	        FROM chitietdonhang ct

	        INNER JOIN donhang dh
	            ON dh.maDH = ct.maDH

	        INNER JOIN sach s
	            ON s.maSach = ct.maSach

	        WHERE dh.trangThai IN (
	            'DaGiao', 'HoanThanh',
	            'Đã giao', 'Hoàn thành'
	        )

	        AND dh.ngayTao >= :from_date
	        AND dh.ngayTao < :until_date

	        GROUP BY ct.maSach, s.tenSach

	        ORDER BY
	            soLuongBan DESC,
	            doanhThuSach DESC,
	            ct.maSach ASC

	        LIMIT 10
	    ";

	    $stmt = $this->db->prepare($sql);

	    $stmt->execute([
	        'from_date' => $from . ' 00:00:00',
	        'until_date' => $end->modify('+1 day')
	            ->format('Y-m-d 00:00:00'),
	    ]);

	    return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
}
