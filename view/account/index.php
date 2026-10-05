<div class="container page-content">
    <?php $breadcrumbs = [['label' => 'Tài khoản mẫu']]; require ROOT_PATH . '/view/partials/breadcrumb.php'; ?>
    <div class="page-heading"><h1>Tài khoản của bạn</h1><p>Góc nhỏ dành cho người yêu sách.</p></div>
    <?php require ROOT_PATH . '/view/partials/notice.php'; ?>
    <div class="account-grid"><?php require ROOT_PATH . '/view/partials/account-nav.php'; ?><section class="panel account-main"><h2>Thông tin cá nhân mẫu</h2><p class="muted">Thông tin cố định để minh họa giao diện. Trang này không yêu cầu đăng nhập và không đại diện cho người dùng thật.</p><dl class="profile-details"><div><dt>Họ và tên</dt><dd>Khách hàng mẫu</dd></div><div><dt>Email</dt><dd>khachhang@example.com</dd></div><div><dt>Số điện thoại</dt><dd>Chưa cung cấp</dd></div><div><dt>Địa chỉ</dt><dd>Địa chỉ giao hàng mẫu, Việt Nam</dd></div></dl><a class="button button-outline" href="<?= e(url('orders')) ?>">Xem đơn hàng mẫu <?= icon('arrow') ?></a></section></div>
</div>
