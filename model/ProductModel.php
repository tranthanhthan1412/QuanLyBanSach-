<?php
declare(strict_types=1);

namespace App\Models;

final class ProductModel
{
    public function all(): array
    {
        static $products;
        return $products ??= require __DIR__ . '/data/products.php';
    }

    public function find(string $id): ?array
    {
        foreach ($this->all() as $product) {
            if ((string) $product['id'] === $id) {
                return $product;
            }
        }
        return null;
    }

    public function featured(int $limit = 8): array
    {
        return array_slice($this->all(), 0, $limit);
    }

    public function paginate(string $search, string $category, string $sort, int $page, int $pageSize): array
    {
        if (!in_array($sort, ['featured', 'price-asc', 'price-desc', 'name', 'newest'], true)) {
            $sort = 'featured';
        }

        $normalizedSearch = normalize_search($search);
        $items = array_values(array_filter($this->all(), function (array $item) use ($normalizedSearch, $category): bool {
            return ($category === '' || $item['category'] === $category)
                && ($normalizedSearch === '' || str_contains(normalize_search($item['title'] . ' ' . $item['author'] . ' ' . $item['category_name']), $normalizedSearch));
        }));

        if ($sort !== 'featured') {
            usort($items, fn (array $a, array $b): int => match ($sort) {
                'price-asc' => $a['price'] <=> $b['price'],
                'price-desc' => $b['price'] <=> $a['price'],
                'name' => strcmp(normalize_search($a['title']), normalize_search($b['title'])),
                'newest' => $b['id'] <=> $a['id'],
            });
        }

        $pageSize = max(1, $pageSize);
        $total = count($items);
        $pages = max(1, (int) ceil($total / $pageSize));
        $page = min($pages, max(1, $page));
        $products = array_slice($items, ($page - 1) * $pageSize, $pageSize);

        return compact('products', 'sort', 'total', 'pages', 'page');
    }

    public function related(array $product, int $limit = 4): array
    {
        $others = array_values(array_filter($this->all(), fn (array $item): bool => $item['id'] !== $product['id']));
        usort($others, fn (array $a, array $b): int => ($b['category'] === $product['category']) <=> ($a['category'] === $product['category']));
        return array_slice($others, 0, $limit);
    }
}
