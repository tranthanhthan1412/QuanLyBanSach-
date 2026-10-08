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

    private function decorate(array $category): array
    {
        static $styles;
        $styles ??= array_column(require dirname(__DIR__) . '/config/categories.php', null, 'name');
        $style = $styles[$category['name']] ?? [];
        $category['id'] = (int) $category['id'];
        $category['slug'] = (string) $category['slug'];
        return $category + [
            'icon' => $style['icon'] ?? 'book',
            'color' => $style['color'] ?? '#6558ff',
            'description' => $style['description'] ?? 'Khám phá sách trong danh mục này',
        ];
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

        return array_map([$this, 'decorate'], $stmt->fetchAll(\PDO::FETCH_ASSOC));
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
            LEFT JOIN sach s ON tl.maTL = s.maTL AND s.an = 0
            GROUP BY tl.maTL, tl.tenTL
            ORDER BY tl.maTL ASC
        ";

        $stmt = $this->db->query($sql);

        return array_map([$this, 'decorate'], $stmt->fetchAll(\PDO::FETCH_ASSOC));
    }
}
