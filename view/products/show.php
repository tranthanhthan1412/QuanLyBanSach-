<div class="container page-content">
    <?php $breadcrumbs = [['label' => 'Sách & truyện', 'url' => url('products')], ['label' => $product['title']]]; require ROOT_PATH . '/view/partials/breadcrumb.php'; ?>
    <div class="product-detail">
        <div class="detail-cover"><img src="<?= e(asset($product['image'])) ?>" alt="Ảnh minh họa bìa <?= e($product['title']) ?>" width="420" height="560"></div>
        <div class="detail-info">
            <a class="tag" href="<?= e(url('products', ['category' => $product['category']])) ?>"><?= e($product['category_name']) ?></a>
            <h1><?= e($product['title']) ?></h1><p class="detail-author">Tác giả: <strong><?= e($product['author']) ?></strong></p>
            <div class="rating"><span class="stars" aria-hidden="true">★★★★★</span><strong><?= e($product['rating']) ?>/5</strong><span>(<?= number_format($product['reviews'], 0, ',', '.') ?> đánh giá mẫu)</span></div>
            <div class="detail-price"><strong><?= money($product['price']) ?></strong><?php if ($product['old_price'] > $product['price']): ?><del><?= money($product['old_price']) ?></del><span class="badge badge-sale">−<?= (int) round((1 - $product['price'] / $product['old_price']) * 100) ?>%</span><?php endif; ?></div>
            <p class="detail-description"><?= e($product['description']) ?></p>
            <div class="stock-note"><?= icon('check') ?>Còn hàng <span class="muted">· Tối đa 20 cuốn / đầu sách trong demo</span></div>
            <label class="quantity-label" for="product-quantity">Số lượng</label>
            <div class="detail-buy"><div class="quantity-control"><button type="button" data-quantity-step="-1" aria-label="Giảm số lượng">−</button><input id="product-quantity" type="number" min="1" max="20" value="1" inputmode="numeric"><button type="button" data-quantity-step="1" aria-label="Tăng số lượng">+</button></div><button class="button" data-add-cart="<?= $product['id'] ?>" data-quantity-input="product-quantity"><?= icon('cart') ?>Thêm vào giỏ hàng</button></div>
            <div class="detail-perks"><span><?= icon('truck') ?>Miễn phí giao hàng từ 300.000 ₫</span><span><?= icon('shield') ?>Thanh toán khi nhận hàng (COD)</span></div>
            <p class="small muted">Giá, thông tin xuất bản và hình ảnh là dữ liệu minh họa cho đồ án.</p>
        </div>
    </div>
    <section class="book-information panel"><h2>Thông tin sách</h2><dl><div><dt>Tác giả</dt><dd><?= e($product['author']) ?></dd></div><div><dt>Nhà xuất bản (mẫu)</dt><dd><?= e($product['publisher']) ?></dd></div><div><dt>Năm xuất bản (mẫu)</dt><dd><?= $product['year'] ?></dd></div><div><dt>Số trang (mẫu)</dt><dd><?= $product['pages'] ?></dd></div><div><dt>Hình thức</dt><dd>Bìa mềm</dd></div><div><dt>Ngôn ngữ</dt><dd>Tiếng Việt</dd></div><div><dt>Hình ảnh</dt><dd>Ảnh minh họa từ thiết kế tham khảo, không phải bìa xuất bản chính thức.</dd></div></dl></section>
    <section class="related-section"><div class="section-heading"><h2>Có thể bạn cũng thích</h2><p>Tiếp nối hành trình đọc sách của bạn</p></div><div class="product-grid"><?php foreach ($related as $product) { require ROOT_PATH . '/view/partials/product-card.php'; } ?></div></section>
</div>
