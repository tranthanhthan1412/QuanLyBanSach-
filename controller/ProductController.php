<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\ProductModel;
use App\Models\CategoryModel;

final class ProductController extends Controller
{
    public function index(): void
    {
        $search = mb_substr(query('q'), 0, 100);
        $category = query('category');

        $result = (new ProductModel())->paginate(
            $search,
            $category,
            query('sort', 'featured'),
            (int) query('page', '1'),
            (int) config('page_size')
        );

        $categories = (new CategoryModel())->all();

        $this->render('products/index', $result + [
            'title' => 'Tất cả sách & truyện',
            'search' => $search,
            'category' => $category,
            'categories' => $categories,
        ]);
    }

    public function show(): void
    {
        $model = new ProductModel();

        $product = $model->find(query('id'));

        if ($product === null) {
            $this->notFound();
            return;
        }

        $this->render('products/show', [
            'title' => $product['title'],
            'product' => $product,
            'related' => $model->related($product),
        ]);
    }
}