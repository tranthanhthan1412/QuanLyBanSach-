
<div class="container page-content narrow-page">
    <section class="panel success-panel" id="success-root">

        <div class="success-icon">
            <?= icon('check') ?>
        </div>

        <span class="tag">ĐƠN HÀNG COD ĐÃ LƯU</span>

        <h1>Đặt hàng thành công!</h1>

        <p>
            Đơn hàng #<?= (int) $order['maDH'] ?>
            đã được lưu vào MySQL và đang chờ xác nhận.
            <br>
            Bạn sẽ thanh toán khi nhận hàng.
        </p>

        <div class="success-facts" id="success-details">

            <div class="summary-row">
                <span>Mã đơn hàng</span>
                <strong>#<?= (int) $order['maDH'] ?></strong>
            </div>

            <div class="summary-row">
                <span>Trạng thái</span>
                <strong><?= e(order_status($order['trangThai'])[0]) ?></strong>
            </div>

            <div class="summary-row">
                <span>Người nhận</span>
                <strong><?= e($order['tenNguoiNhan'] ?: $order['tenDH']) ?></strong>
            </div>

            <div class="summary-row">
                <span>Phương thức</span>
                <strong>COD</strong>
            </div>

            <div class="summary-row">
                <span>Số lượng</span>
                <strong><?= (int) $order['tongSL'] ?> cuốn</strong>
            </div>

            <div class="summary-row">
                <span>Tiền sách</span>
                <strong><?= e(money($order['tienSach'])) ?></strong>
            </div>

            <div class="summary-row">
                <span>Vận chuyển</span>
                <strong><?= e(money($order['phiVanChuyen'])) ?></strong>
            </div>

            <div class="summary-row summary-total">
                <span>Tổng thanh toán</span>
                <strong><?= e(money($order['tongTien'])) ?></strong>
            </div>
        </div>

        <div class="success-actions">
            <a class="button" href="<?= e(url('order', ['id' => $order['maDH']])) ?>">
                Xem chi tiết đơn <?= icon('arrow') ?>
            </a>

            <a class="button button-outline" href="<?= e(url('products')) ?>">
                Tiếp tục mua sắm
            </a>
        </div>

    </section>
</div>
