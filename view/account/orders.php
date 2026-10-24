<div class="container page-content">
    <?php
    $breadcrumbs = [
        ['label' => 'Tài khoản', 'url' => url('account')],
        ['label' => 'Đơn hàng']
    ];
    require ROOT_PATH . '/view/partials/breadcrumb.php';
    ?>

    <div class="page-heading">
        <h1>Đơn hàng của bạn</h1>
        <p>Theo dõi các đơn hàng và trạng thái đổi trả của bạn.</p>
    </div>

    <div class="account-grid">
        <?php require ROOT_PATH . '/view/partials/account-nav.php'; ?>

        <section class="panel account-main">
            <h2>Danh sách đơn hàng</h2>

            <?php if (empty($orders)): ?>
            <div class="empty-state">
                <p>Bạn chưa có đơn hàng nào.</p>
                <a class="button" href="<?= e(url('products')) ?>">Tiếp tục mua sắm</a>
            </div>
            <?php else: ?>
            <div class="orders-list">
                <?php foreach ($orders as $order):
                    [$status, $statusClass] = order_status($order['trangThai'] ?? 'ChoXacNhan');
                    [$codLabel, $codTone] = cod_status($order['trangThaiCOD'] ?? 'ChuaThu');
                ?>
                <article class="order-card">
                    <div class="order-card-header">
                        <div>
                            <h3>#<?= e($order['maDH']) ?></h3>
                            <p class="muted"><?= e($order['tenDH'] ?: 'Đơn hàng') ?></p>
                        </div>
                        <div style="display: flex; gap: 6px; flex-wrap: wrap; align-items: center;">
                            <span class="status status-<?= e($statusClass) ?>"><?= e($status) ?></span>
                            <span class="status status-<?= e($codTone) ?>"><?= e($codLabel) ?></span>
                            <?php if (!empty($order['trangThaiDoiTra'])): [$retText, $retTone] = return_status($order['trangThaiDoiTra']); ?>
                            <span class="status status-<?= e($retTone) ?>">Đổi trả: <?= e($retText) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="order-card-body">
                        <div>
                            <span class="muted">Số lượng</span>
                            <strong><?= e($order['tongSL'] ?? 0) ?></strong>
                        </div>
                        <div>
                            <span class="muted">Số loại sách</span>
                            <strong><?= e($order['soLoaiSach'] ?? 0) ?></strong>
                        </div>
                        <div>
                            <span class="muted">Tổng tiền</span>
                            <strong><?= money((float)($order['tongTien'] ?? 0)) ?></strong>
                        </div>
                    </div>

                    <div class="order-card-footer">
                        <?php if (!empty($order['ghiChu'])): ?>
                        <span class="muted"><?= e($order['ghiChu']) ?></span>
                        <?php endif; ?>

                        <a class="button button-outline" href="<?= e(url('order', ['id' => $order['maDH']])) ?>">
                            Xem chi tiết
                            <?= icon('arrow') ?>
                        </a>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </section>
    </div>
</div>