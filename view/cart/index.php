<div class="container page-content">
    <?php $breadcrumbs = [['label' => 'Giỏ hàng']]; require ROOT_PATH . '/view/partials/breadcrumb.php'; ?>
    <div class="page-heading"><h1>Giỏ hàng của bạn</h1><p>Những cuốn sách sẵn sàng cho hành trình mới.</p></div>
    <div id="cart-root"><p class="loading-message">Đang tải giỏ hàng…</p></div>
    <a class="text-link back-link" href="<?= e(url('products')) ?>">← Tiếp tục khám phá sách</a>
</div>
