<section class="hero">
    <div class="container hero-grid">
        <div class="hero-copy">
            <h1>Khám phá thế giới<br>qua từng trang sách</h1>
            <p>Tìm cuốn sách yêu thích từ hàng ngàn câu chuyện.<br class="desktop-break"> Từ tiểu thuyết bán chạy đến truyện tranh, tri thức và cảm hứng đều ở đây.</p>
            <div class="hero-buttons"><a class="button" href="<?= e(url('products')) ?>">Khám phá ngay <?= icon('arrow') ?></a><a class="button button-outline" href="#categories">Xem danh mục</a></div>
            <div class="hero-benefits">
                <div><span class="benefit-icon"><?= icon('book') ?></span><span><strong>10.000+ đầu sách</strong><small>Thế giới tri thức đa dạng</small></span></div>
                <div><span class="benefit-icon"><?= icon('users') ?></span><span><strong>50.000+ bạn đọc</strong><small>Kết nối người yêu sách</small></span></div>
                <div><span class="benefit-icon"><?= icon('truck') ?></span><span><strong>Miễn phí giao hàng</strong><small>Đơn từ 300.000 ₫</small></span></div>
            </div>
        </div>
        <div class="hero-visual">
            <img src="<?= e(asset('images/library.jpg')) ?>" alt="Không gian thư viện với những kệ sách và ánh sáng tự nhiên" width="728" height="500" fetchpriority="high">
            <div class="hero-promo"><div><strong>Cuốn sách hay, ngày thêm ý nghĩa</strong><span>Khám phá những tựa sách được yêu thích</span></div><a class="button button-small" href="#featured">Xem ngay</a></div>
        </div>
    </div>
</section>
<section class="category-section section" id="categories">
    <div class="container">
        <div class="section-heading centered"><h2>Khám phá danh mục</h2><p>Tìm cuốn sách dành cho bạn theo chủ đề yêu thích. Từ văn học đến truyện tranh,<br class="desktop-break"> một thế giới câu chuyện đang chờ bạn khám phá.</p></div>
        <div class="category-grid">
            <?php foreach ($homeCategories as $category): ?>
            <a class="category-card" href="<?= e(url('products', ['category' => $category['slug']])) ?>">
                <span class="category-icon" style="--category-color: <?= e($category['color']) ?>"><?= icon($category['icon']) ?></span>
                <h3><?= e($category['name']) ?></h3><p><?= e($category['description']) ?></p><small><?= $category['product_count'] ?> tựa sách</small>
            </a>
            <?php endforeach; ?>
        </div>
        <div class="section-action"><a class="button" href="<?= e(url('products')) ?>">Xem tất cả danh mục</a></div>
    </div>
</section>
<section class="section featured-section" id="featured">
    <div class="container">
        <div class="section-heading heading-row"><div><h2>Sách được yêu thích</h2><p>Những cuốn sách hay được chọn riêng cho bạn</p></div><a class="button button-outline button-small" href="<?= e(url('products')) ?>">Xem tất cả <?= icon('arrow') ?></a></div>
        <div class="product-grid"><?php foreach ($featured as $product) { require ROOT_PATH . '/view/partials/product-card.php'; } ?></div>
    </div>
</section>
