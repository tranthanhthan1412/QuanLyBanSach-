<div class="container page-content">
    <?php $breadcrumbs = [['label' => 'Đơn hàng mẫu', 'url' => url('orders')], ['label' => $order['id']]]; require ROOT_PATH . '/view/partials/breadcrumb.php'; ?>
    <div class="page-heading heading-row"><div><h1>Đơn hàng #<?= e($order['id']) ?></h1><p>Ngày đặt: <?= e($order['date']) ?> · Thanh toán COD · Dữ liệu mô phỏng</p></div><span class="status status-<?= e($order['tone']) ?>"><?= e($order['status']) ?></span></div>
    <?php require ROOT_PATH . '/view/partials/notice.php'; ?>
    <div class="checkout-grid">
        <section class="panel">
            <h2>Sách trong đơn</h2>
            <?php foreach ($order['items'] as $line): $book = $line['product']; ?>
            <div class="order-item">
                <img src="<?= e(asset($book['image'])) ?>" alt="" width="56" height="76">
                <div><a href="<?= e(url('product', ['id' => $book['id']])) ?>"><?= e($book['title']) ?></a><small><?= e($book['author']) ?> · Số lượng: <?= $line['qty'] ?></small></div>
                <strong><?= money($line['total']) ?></strong>
            </div>
            <?php endforeach; ?>
            <div class="summary-row"><span>Tạm tính</span><span><?= money($order['subtotal']) ?></span></div>
            <div class="summary-row"><span>Phí vận chuyển</span><span><?= $order['shipping'] ? money($order['shipping']) : 'Miễn phí' ?></span></div>
            <div class="summary-row summary-total"><span>Tổng cộng</span><strong><?= money($order['total']) ?></strong></div>
        </section>
        <aside class="panel">
            <h2>Thông tin nhận hàng mẫu</h2>
            <p><strong>Khách hàng mẫu</strong></p>
            <p class="muted">Địa chỉ giao hàng mẫu, Việt Nam<br>Không chứa thông tin người dùng thật.</p>
            <div class="payment-option"><?= icon('truck') ?><span>Thanh toán khi nhận hàng (COD)</span></div>
            <a class="text-link back-link" href="<?= e(url('orders')) ?>">← Quay lại đơn hàng mẫu</a>
        </aside>
    </div>
</div>
