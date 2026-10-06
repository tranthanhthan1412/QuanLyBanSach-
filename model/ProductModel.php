<?php

declare(strict_types=1);

namespace App\Models;

final class ProductModel
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
                s.maSach AS id,
                s.tenSach AS title,
                s.moTa AS description,
                s.giaTien AS price,
                s.tonKho AS stock,
                s.maTL AS category,
                tl.tenTL AS category_name,
                s.maTG AS author_id,
                tg.tenTG AS author,
                s.maNXB AS publisher_id,
                nxb.tenNXB AS publisher
            FROM sach s
            INNER JOIN theloai tl ON s.maTL = tl.maTL
            INNER JOIN tacgia tg ON s.maTG = tg.maTG
            INNER JOIN nhaxuatban nxb ON s.maNXB = nxb.maNXB
            ORDER BY s.maSach ASC
        ";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function find(string $id): ?array
    {
        $sql = "
            SELECT
                s.maSach AS id,
                s.tenSach AS title,
                s.moTa AS description,
                s.giaTien AS price,
                s.tonKho AS stock,
                s.maTL AS category,
                tl.tenTL AS category_name,
                s.maTG AS author_id,
                tg.tenTG AS author,
                s.maNXB AS publisher_id,
                nxb.tenNXB AS publisher
            FROM sach s
            INNER JOIN theloai tl ON s.maTL = tl.maTL
            INNER JOIN tacgia tg ON s.maTG = tg.maTG
            INNER JOIN nhaxuatban nxb ON s.maNXB = nxb.maNXB
            WHERE s.maSach = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);

        $product = $stmt->fetch(\PDO::FETCH_ASSOC);

        return $product ?: null;
    }

    public function featured(int $limit = 8): array
    {
        $limit = max(1, $limit);

        $sql = "
            SELECT
                s.maSach AS id,
                s.tenSach AS title,
                s.moTa AS description,
                s.giaTien AS price,
                s.tonKho AS stock,
                s.maTL AS category,
                tl.tenTL AS category_name,
                s.maTG AS author_id,
                tg.tenTG AS author,
                s.maNXB AS publisher_id,
                nxb.tenNXB AS publisher
            FROM sach s
            INNER JOIN theloai tl ON s.maTL = tl.maTL
            INNER JOIN tacgia tg ON s.maTG = tg.maTG
            INNER JOIN nhaxuatban nxb ON s.maNXB = nxb.maNXB
            ORDER BY s.maSach ASC
            LIMIT {$limit}
        ";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function paginate(
        string $search,
        string $category,
        string $sort,
        int $page,
        int $pageSize
    ): array {
        if (!in_array(
            $sort,
            ['featured', 'price-asc', 'price-desc', 'name', 'newest'],
            true
        )) {
            $sort = 'featured';
        }

        $pageSize = max(1, $pageSize);
        $page = max(1, $page);

        $where = [];
        $params = [];

        // Tìm kiếm
        if ($search !== '') {
            $where[] = "
                (
                    s.tenSach LIKE :search
                    OR tg.tenTG LIKE :search
                    OR tl.tenTL LIKE :search
                )
            ";

            $params['search'] = '%' . $search . '%';
        }

        // Lọc thể loại
        if ($category !== '') {
            $where[] = 's.maTL = :category';
            $params['category'] = $category;
        }

        $whereSql = '';

        if (!empty($where)) {
            $whereSql = 'WHERE ' . implode(' AND ', $where);
        }

        // Sắp xếp
        $orderSql = match ($sort) {
            'price-asc' => 's.giaTien ASC',
            'price-desc' => 's.giaTien DESC',
            'name' => 's.tenSach ASC',
            'newest' => 's.maSach DESC',
            default => 's.maSach ASC',
        };

        // Đếm tổng số sản phẩm
        $countSql = "
            SELECT COUNT(*)
            FROM sach s
            INNER JOIN theloai tl ON s.maTL = tl.maTL
            INNER JOIN tacgia tg ON s.maTG = tg.maTG
            INNER JOIN nhaxuatban nxb ON s.maNXB = nxb.maNXB
            {$whereSql}
        ";

        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);

        $total = (int) $countStmt->fetchColumn();

        $pages = max(1, (int) ceil($total / $pageSize));

        $page = min($pages, $page);

        $offset = ($page - 1) * $pageSize;

        // Lấy danh sách sản phẩm
        $sql = "
            SELECT
                s.maSach AS id,
                s.tenSach AS title,
                s.moTa AS description,
                s.giaTien AS price,
                s.tonKho AS stock,
                s.maTL AS category,
                tl.tenTL AS category_name,
                s.maTG AS author_id,
                tg.tenTG AS author,
                s.maNXB AS publisher_id,
                nxb.tenNXB AS publisher
            FROM sach s
            INNER JOIN theloai tl ON s.maTL = tl.maTL
            INNER JOIN tacgia tg ON s.maTG = tg.maTG
            INNER JOIN nhaxuatban nxb ON s.maNXB = nxb.maNXB
            {$whereSql}
            ORDER BY {$orderSql}
            LIMIT {$pageSize} OFFSET {$offset}
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $products = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return compact(
            'products',
            'sort',
            'total',
            'pages',
            'page'
        );
    }

    public function related(array $product, int $limit = 4): array
    {
        $limit = max(1, $limit);

        $sql = "
            SELECT
                s.maSach AS id,
                s.tenSach AS title,
                s.moTa AS description,
                s.giaTien AS price,
                s.tonKho AS stock,
                s.maTL AS category,
                tl.tenTL AS category_name,
                s.maTG AS author_id,
                tg.tenTG AS author,
                s.maNXB AS publisher_id,
                nxb.tenNXB AS publisher
            FROM sach s
            INNER JOIN theloai tl ON s.maTL = tl.maTL
            INNER JOIN tacgia tg ON s.maTG = tg.maTG
            INNER JOIN nhaxuatban nxb ON s.maNXB = nxb.maNXB
            WHERE s.maTL = :category
            AND s.maSach != :id
            ORDER BY s.maSach ASC
            LIMIT {$limit}
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'category' => $product['category'],
            'id' => $product['id']
        ]);

        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}