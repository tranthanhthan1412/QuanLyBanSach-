<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\CategoryModel;
use App\Models\ProductModel;

class Controller
{
    public function render(string $view, array $data = []): void
    {
        $categories = (new CategoryModel())->all();

        $catalog = (new ProductModel())->all();

        $clientProducts = array_map(
            fn (array $item): array => [
                'id' => $item['id'],
                'title' => $item['title'],
                'author' => $item['author'],
                'price' => $item['price'],
                'href' => url('product', ['id' => $item['id']]),
                'stock' => $item['stock'],
            ],
            $catalog
        );

        $clientConfig = [
            'products' => $clientProducts,
            'shippingFee' => config('shipping_fee'),
            'freeShippingFrom' => config('free_shipping_from'),
            'urls' => [
                'products' => url('products'),
                'checkout' => url('checkout'),
                'success' => url('order-success'),
            ],
        ];

        extract($data, EXTR_SKIP);

        require ROOT_PATH . '/view/layouts/main.php';
    }

    protected function notFound(): void
    {
        http_response_code(404);

        $this->render(
            'errors/404',
            ['title' => 'Không tìm thấy trang']
        );
    }
}