<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\CategoryModel;
use App\Models\ProductModel;

final class HomeController extends Controller
{
    public function index(): void
    {
        $this->render('home/index', [
            'title' => 'Khám phá thế giới qua từng trang sách',
            'featured' => (new ProductModel())->featured(),
            'homeCategories' => (new CategoryModel())->withProductCounts(),
        ]);
    }
}
