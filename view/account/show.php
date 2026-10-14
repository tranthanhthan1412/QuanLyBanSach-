<?php [$status, $tone] = order_status($order['trangThai']); ?>
<div class="container page-content">
    <?php $breadcrumbs = [['label' => 'Đơn hàng', 'url' => url('orders')], ['label' => '#' . $order['maDH']]]; require ROOT_PATH . '/view/partials/breadcrumb.php'; ?>
    <div class="page-heading heading-row"><div><h1>Đơn hàng #<?= e($order['maDH']) ?></h1><p><?= e($order['tenDH'] ?: 'Chi tiết đơn hàng') ?></p></div><span class="status status-<?= e($tone) ?>"><?= e($status) ?></span></div>
    <?php if (!empty($cancelNotice)): ?>
    <div class="notice<?= empty($cancelNotice['success']) ? ' notice-error' : '' ?>"
         role="<?= empty($cancelNotice['success']) ? 'alert' : 'status' ?>">
        <?= e($cancelNotice['message']) ?>
    </div>
    <?php endif; ?>
    <div class="checkout-grid">
        <section class="panel">
            <h2>Sách trong đơn</h2>
            <?php if (!$order['items']): ?><p>Đơn hàng chưa có sách.</p><?php endif; ?>
            <?php foreach ($order['items'] as $line): ?>
            <div class="order-item">
                <div><a href="<?= e(url('product', ['id' => $line['maSach']])) ?>"><?= e($line['tenSach']) ?></a><small><?= e($line['tenTG']) ?> · Số lượng: <?= (int) $line['soLuong'] ?></small></div>
                <strong><?= money($line['tongTien']) ?></strong>
            </div>
            <?php endforeach; ?>
            <div class="summary-row"><span>Số lượng</span><span><?= (int) $order['tongSL'] ?> cuốn</span></div>
            <div class="summary-row summary-total"><span>Tổng tiền sách</span><strong><?= money($order['tongTien']) ?></strong></div>
            <div class="summary-row"><span>Phí vận chuyển</span><span><?= money($order['phiVanChuyen']) ?></span></div>
            <div class="summary-row summary-total"><span>Tổng thanh toán (COD)</span><strong><?= money($order['total']) ?></strong></div>
        </section>
        <aside class="panel">
            <?php if ($order['tenNguoiNhan']): ?>
            <h2>Thông tin giao hàng</h2>
            <p><?= e($order['tenNguoiNhan']) ?> · <?= e($order['sdtNhan']) ?></p>
            <p><?= e($order['diaChiNhan']) ?></p>
            <p>Email nhận đơn: <?= e($order['emailNhan']) ?></p>
            <p>Ngày đặt: <?= e($order['ngayTao']) ?></p>
            <?php endif; ?>
            <h2>Ghi chú</h2>
            <p><?= e($order['ghiChu'] ?: 'Không có ghi chú.') ?></p>
            <?php if ($order['trangThai'] === 'ChoXacNhan'): ?>
            <form method="post" action="<?= e(url('order-cancel')) ?>"
                  onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này?');">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $order['maDH'] ?>">
                <div class="field">
                    <label for="cancel-reason">Lý do hủy (không bắt buộc)</label>
                    <textarea id="cancel-reason" name="reason" maxlength="180" rows="2" placeholder="Ví dụ: Tôi muốn thay đổi đơn hàng"></textarea>
                </div>
                <button class="button button-outline" type="submit">Hủy đơn hàng</button>
                <p class="muted">Chỉ được hủy khi đơn còn Chờ xác nhận.</p>
            </form>
            <?php endif; ?>
            <a class="text-link back-link" href="<?= e(url('orders')) ?>">← Quay lại đơn hàng</a>
        </aside>
    </div>
</div>
