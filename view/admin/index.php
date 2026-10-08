<p class="admin-intro">Chào <?= e($user['name']) ?>, cùng theo dõi hoạt động và chăm sóc cửa hàng hôm nay.</p>
<div class="admin-stats">
    <?php foreach ([['Doanh thu đơn đã giao', money($stats['revenue']), 'briefcase', 'Tổng tiền sách · toàn thời gian'], ['Đơn hàng', $stats['orders'], 'box', $stats['pending'] . ' đơn đang chờ xác nhận'], ['Đầu sách', $stats['books'], 'book', $stats['stock'] . ' cuốn trong kho đang bán'], ['Khách hàng', $stats['customers'], 'users', 'Tài khoản khách hàng đã đăng ký']] as [$label, $value, $symbol, $hint]): ?>
    <article class="admin-stat"><div class="admin-stat-top"><span><?= e($label) ?></span><span class="admin-stat-icon"><?= icon($symbol) ?></span></div><strong><?= e($value) ?></strong><small><?= e($hint) ?></small></article>
    <?php endforeach; ?>
</div>
<div class="admin-priority"><div class="admin-priority-icon"><?= icon('clock') ?></div><div><h2><?= e($stats['pending']) ?> đơn hàng cần xác nhận</h2><p>Kiểm tra đơn hàng và tồn kho trước khi chuyển sang giao hàng.</p></div><a class="button button-outline" href="<?= e(url('admin-orders', ['status' => 'ChoXacNhan'])) ?>">Xử lý đơn hàng <?= icon('arrow') ?></a></div>
<div class="admin-dashboard-grid">
    <section class="admin-panel"><div class="admin-panel-heading"><div><h2>Đơn hàng gần đây</h2><p>Các đơn mới nhất trong cửa hàng</p></div><a class="admin-link" href="<?= e(url('admin-orders')) ?>">Xem tất cả →</a></div><?php $rows = array_slice($recent, 0, 6); require ROOT_PATH . '/view/admin/orders-table.php'; ?></section>
    <section class="admin-panel"><div class="admin-panel-heading"><div><h2>Cần bổ sung tồn kho</h2><p><?= e($stats['low_stock']) ?> đầu sách còn tối đa 5 cuốn</p></div><?= icon('box') ?></div><div class="admin-stock-list">
        <?php foreach ($lowStock as $book): ?><a href="<?= e(url('admin-product', ['id' => $book['maSach']])) ?>"><img src="<?= e(asset(book_image($book['hinhAnh']))) ?>" alt=""><div><strong><?= e($book['tenSach']) ?></strong><small>Mã sách #<?= e($book['maSach']) ?></small></div><span class="admin-badge amber"><?= e($book['tonKho']) ?> cuốn</span></a><?php endforeach; ?>
        <?php if (!$lowStock): ?><div class="admin-empty compact"><?= icon('check') ?><h3>Tồn kho đang ổn</h3><p>Chưa có sách nào sắp hết hàng.</p></div><?php endif; ?>
    </div><a class="admin-panel-bottom" href="<?= e(url('admin-products', ['visibility' => 'low'])) ?>">Kiểm tra tồn kho →</a></section>
</div>
