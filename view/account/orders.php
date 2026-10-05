<div class="container page-content">
    <?php $breadcrumbs = [['label' => 'Tài khoản mẫu', 'url' => url('account')], ['label' => 'Đơn hàng mẫu']]; require ROOT_PATH . '/view/partials/breadcrumb.php'; ?>
    <div class="page-heading"><h1>Đơn hàng của bạn</h1><p>Theo dõi hành trình của những cuốn sách.</p></div>
    <?php $notice = 'Các đơn dưới đây là dữ liệu mẫu cố định, không phải lịch sử giao dịch hoặc các lần đặt thử trên trình duyệt.'; require ROOT_PATH . '/view/partials/notice.php'; ?>
    <div class="account-grid"><?php require ROOT_PATH . '/view/partials/account-nav.php'; ?><div class="orders-list">
    <?php foreach ($orders as $order): ?>
        <article class="panel order-card">
            <div class="order-card-top">
                <div><h2>#<?= e($order['id']) ?></h2><span class="muted small">Ngày đặt: <?= e($order['date']) ?></span></div>
                <span class="status status-<?= e($order['tone']) ?>"><?= e($order['status']) ?></span>
            </div>
            <?php foreach ($order['items'] as $line): $book = $line['product']; ?>
            <div class="order-item">
                <img src="<?= e(asset($book['image'])) ?>" alt="" width="48" height="64">
                <div><a href="<?= e(url('product', ['id' => $book['id']])) ?>"><?= e($book['title']) ?></a><small>Số lượng: <?= $line['qty'] ?></small></div>
                <strong><?= money($line['total']) ?></strong>
            </div>
            <?php endforeach; ?>
            <div class="order-card-bottom">
                <span>Tổng cộng: <strong><?= money($order['total']) ?></strong></span>
                <a class="button button-outline button-small" href="<?= e(url('order', ['id' => $order['id']])) ?>">Xem chi tiết <?= icon('arrow') ?></a>
            </div>
        </article>
    <?php endforeach; ?></div></div>
</div>
