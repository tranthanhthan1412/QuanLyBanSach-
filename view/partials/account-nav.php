<aside class="account-nav panel">
    <div class="account-avatar"><?= icon('user') ?></div>
    <strong><?= e($user['name']) ?></strong>
    <span class="tag"><?= e(role_label($user['role'])) ?></span>
    <nav aria-label="Tài khoản">
        <?php if (is_admin()): ?>
        <a href="<?= e(url('admin')) ?>" <?= query('route') === 'admin' ? 'aria-current="page"' : '' ?>><?= icon('shield') ?>Quản trị cửa hàng</a>
        <?php endif; ?>
        <a href="<?= e(url('account')) ?>" <?= query('route') === 'account' ? 'aria-current="page"' : '' ?>><?= icon('user') ?>Thông tin cá nhân</a>
        <a href="<?= e(url('orders')) ?>" <?= in_array(query('route'), ['orders', 'order'], true) ? 'aria-current="page"' : '' ?>><?= icon('box') ?>Đơn hàng của bạn</a>
        <a href="<?= e(url('products')) ?>"><?= icon('book') ?>Tiếp tục mua sắm</a>
    </nav>
</aside>
