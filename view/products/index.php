<div class="container page-content">
    <?php $breadcrumbs = [['label' => 'Sách & truyện']]; require ROOT_PATH . '/view/partials/breadcrumb.php'; ?>
    <div class="page-heading"><h1>Tất cả sách & truyện</h1><p>Một cuốn sách mới, một thế giới mới. Tìm câu chuyện tiếp theo của bạn.</p></div>
    <form class="catalog-filters panel" method="get" action="<?= e(base_url() . '/index.php') ?>">
        <input type="hidden" name="route" value="products">
        <div class="field filter-search"><label for="catalog-search">Tìm kiếm</label><input id="catalog-search" type="search" name="q" value="<?= e($search) ?>" placeholder="Tên sách, tác giả, thể loại..." maxlength="100"></div>
        <div class="field"><label for="category">Danh mục</label><select id="category" name="category" data-auto-submit><option value="">Tất cả danh mục</option><?php foreach ($categories as $cat): ?><option value="<?= e($cat['slug']) ?>" <?= $category === $cat['slug'] ? 'selected' : '' ?>><?= e($cat['name']) ?></option><?php endforeach; ?></select></div>
        <div class="field"><label for="sort">Sắp xếp theo</label><select id="sort" name="sort" data-auto-submit><?php foreach (['featured' => 'Nổi bật', 'newest' => 'Mới nhất', 'price-asc' => 'Giá: thấp đến cao', 'price-desc' => 'Giá: cao đến thấp', 'name' => 'Tên sách: A – Z'] as $key => $label): ?><option value="<?= e($key) ?>" <?= $sort === $key ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select></div>
        <button class="button" type="submit"><?= icon('search') ?>Tìm sách</button>
    </form>
    <div class="results-bar"><p><strong><?= $total ?></strong> kết quả<?= $search !== '' ? ' cho “' . e($search) . '”' : '' ?></p><?php if ($search !== '' || $category !== ''): ?><a class="text-link" href="<?= e(url('products')) ?>">Xóa bộ lọc</a><?php endif; ?></div>
    <?php if (!$products): ?>
    <div class="empty-state panel"><?= icon('search') ?><h2>Chưa tìm thấy cuốn sách này</h2><p>Thử một từ khóa khác hoặc khám phá tất cả danh mục nhé.</p><a class="button" href="<?= e(url('products')) ?>">Xem tất cả sách</a></div>
    <?php else: ?>
    <div class="product-grid"><?php foreach ($products as $product) { require ROOT_PATH . '/view/partials/product-card.php'; } ?></div>
    <?php if ($pages > 1): ?><nav class="pagination" aria-label="Phân trang"><?php for ($i = 1; $i <= $pages; $i++): ?><a href="<?= e(url('products', ['q' => $search, 'category' => $category, 'sort' => $sort, 'page' => $i])) ?>" <?= $i === $page ? 'aria-current="page"' : '' ?> aria-label="Trang <?= $i ?>"><?= $i ?></a><?php endfor; ?></nav><?php endif; ?>
    <?php endif; ?>
</div>
