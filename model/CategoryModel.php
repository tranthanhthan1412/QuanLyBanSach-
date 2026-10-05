<?php
declare(strict_types=1);

namespace App\Models;

final class CategoryModel
{
    public function all(): array
    {
        static $categories;
        return $categories ??= require __DIR__ . '/data/categories.php';
    }

    public function withProductCounts(): array
    {
        $counts = array_count_values(array_column((new ProductModel())->all(), 'category'));
        return array_map(fn (array $category): array => $category + [
            'product_count' => $counts[$category['slug']] ?? 0,
        ], $this->all());
    }
}
