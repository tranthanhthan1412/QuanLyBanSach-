<?php [$status, $tone] = order_status($order['trangThai']); ?>
<div class="container page-content">
    <?php $breadcrumbs = [['label' => 'Đơn hàng', 'url' => url('orders')], ['label' => '#' . $order['maDH']]]; require ROOT_PATH . '/view/partials/breadcrumb.php'; ?>
    <div class="page-heading heading-row"><div><h1>Đơn hàng #<?= e($order['maDH']) ?></h1><p><?= e($order['tenDH'] ?: 'Chi tiết đơn hàng') ?></p></div><span class="status status-<?= e($tone) ?>"><?= e($status) ?></span></div>
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
			
			<div class="summary-row">
			    <span>Tiền sách</span>
			    <strong><?= e(money($order['tienSach'])) ?></strong>
			</div>

			<div class="summary-row">
			    <span>Phí vận chuyển</span>
			    <strong><?= e(money($order['phiVanChuyen'])) ?></strong>
			</div>

			<div class="summary-row summary-total">
			    <span>Tổng thanh toán</span>
			    <strong><?= e(money($order['tongTien'])) ?></strong>
			</div>

        </section>
        <aside class="panel">
            <h2>Ghi chú</h2>
			
			<h2>Thông tin nhận hàng</h2>

			<p>
			    <strong>Người nhận:</strong>
			    <?= e($order['tenNguoiNhan'] ?: 'Chưa có thông tin') ?>
			</p>

			<p>
			    <strong>Số điện thoại:</strong>
			    <?= e($order['SDTNguoiNhan'] ?: 'Chưa có') ?>
			</p>

			<p>
			    <strong>Địa chỉ giao hàng:</strong>
			    <?= e($order['diaChiGiao'] ?: 'Chưa có') ?>
			</p>

			<p>
			    <strong>Phương thức thanh toán:</strong>
			    <?= e($order['phuongThucTT']) ?>
			</p>

            <p><?= e($order['ghiChu'] ?: 'Không có ghi chú.') ?></p>
            <a class="text-link back-link" href="<?= e(url('orders')) ?>">← Quay lại đơn hàng</a>
        </aside>
    </div>
</div>
