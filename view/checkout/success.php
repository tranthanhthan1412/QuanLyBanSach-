
<div class="container page-content narrow-page">
    <section class="panel success-panel" id="success-root">
        <div class="success-icon"><?= icon($order ? 'check' : 'cart') ?></div>
        <h1><?= $order ? 'Đặt hàng thành công!' : 'Chưa có thông tin đơn hàng' ?></h1>
        <?php if ($order): ?>
        <p>Cảm ơn bạn đã mua sách tại BookStore. Đơn hàng #<?= e($order['maDH']) ?> đang <?= e(mb_strtolower(order_status($order['trangThai'])[0])) ?>.</p>
        <p class="notice"><?php if ($order['trangThaiEmail'] === 'sent'): ?>Thông tin đơn hàng đã được gửi đến <?= e($order['emailNhan']) ?>. Bạn có thể kiểm tra thêm thư mục Spam.
        <?php else: ?>Đơn hàng đã được lưu. Email xác nhận chưa gửi được; bạn có thể xem đầy đủ thông tin trong mục Đơn hàng của bạn.<?php endif; ?></p>
        <div class="success-facts">
            <div class="summary-row"><span>Ngày đặt</span><span><?= e($order['ngayTao']) ?></span></div>
            <div class="summary-row"><span>Người nhận</span><span><?= e($order['tenNguoiNhan']) ?></span></div>
            <div class="summary-row"><span>Điện thoại</span><span><?= e($order['sdtNhan']) ?></span></div>
            <div class="summary-row"><span>Giao đến</span><span><?= e($order['diaChiNhan']) ?></span></div>
            <?php foreach ($order['items'] as $item): ?><div class="summary-row"><span><?= e($item['tenSach']) ?> × <?= (int) $item['soLuong'] ?></span><strong><?= money($item['tongTien']) ?></strong></div><?php endforeach; ?>
            <div class="summary-row"><span>Phí vận chuyển</span><span><?= money($order['phiVanChuyen']) ?></span></div>
            <div class="summary-row summary-total"><span>Tổng thanh toán (COD)</span><strong><?= money($order['total']) ?></strong></div>
        </div>
        <?php else: ?><p>Hãy chọn sách và hoàn tất đặt hàng để xem xác nhận tại đây.</p><?php endif; ?>
        <div class="success-actions"><a class="button" href="<?= e(url('products')) ?>">Tiếp tục khám phá <?= icon('arrow') ?></a><a class="button button-outline" href="<?= e(url('orders')) ?>">Đơn hàng của bạn</a></div>
    </section>
</div>
