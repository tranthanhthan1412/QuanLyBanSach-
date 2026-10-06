<?php

declare(strict_types=1);

namespace App\Models;

final class CategoryModel
{
    private \PDO $db;

    public function __construct()
    {
        $database = new \Database();
        $this->db = $database->getConnection();
    }

    public function all(): array
    {
        $sql = "
            SELECT
                maTL AS id,
                maTL AS slug,
                tenTL AS name
            FROM theloai
            ORDER BY maTL ASC
        ";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function withProductCounts(): array
    {
        $sql = "
            SELECT
                tl.maTL AS id,
                tl.maTL AS slug,
                tl.tenTL AS name,
                COUNT(s.maSach) AS product_count
            FROM theloai tl
            LEFT JOIN sach s ON tl.maTL = s.maTL
            GROUP BY tl.maTL, tl.tenTL
            ORDER BY tl.maTL ASC
        ";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}