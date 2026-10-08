<?php
$route = query('route');
$navigation = [
    ['admin', 'Tổng quan', 'chip', ''], ['admin-products', 'Sản phẩm', 'book', ''],
    ['admin-orders', 'Đơn hàng', 'box', ''], ['admin-users', 'Tài khoản', 'users', ''],
    ['admin-catalog', 'Thể loại', 'menu', 'categories'], ['admin-catalog', 'Tác giả', 'user', 'authors'],
    ['admin-catalog', 'Nhà xuất bản', 'briefcase', 'publishers'], ['admin-logs', 'Nhật ký', 'clock', ''],
];
?>
<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title) ?> | BookStore Admin</title>
    <link rel="icon" href="<?= e(asset('images/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body class="admin-body">
    <a class="skip-link" href="#admin-main">Đến nội dung chính</a>
    <aside class="admin-sidebar">
        <a class="admin-brand" href="<?= e(url('admin')) ?>"><span><?= icon('book') ?></span><div>BookStore<small>KHÔNG GIAN QUẢN TRỊ</small></div></a>
        <div class="admin-nav-label">QUẢN LÝ CỬA HÀNG</div>
        <nav class="admin-nav" aria-label="Quản trị">
            <?php foreach ($navigation as [$target, $label, $symbol, $catalogKind]):
                $active = $catalogKind ? $route === $target && query('kind', 'categories') === $catalogKind : ($route === $target || ($target === 'admin-products' && $route === 'admin-product') || ($target === 'admin-orders' && $route === 'admin-order'));
            ?>
            <a href="<?= e(url($target, $catalogKind ? ['kind' => $catalogKind] : [])) ?>" <?= $active ? 'aria-current="page"' : '' ?>><?= icon($symbol) ?><span><?= e($label) ?></span></a>
            <?php endforeach; ?>
        </nav>
        <div class="admin-sidebar-bottom"><p><span class="admin-dot"></span> Cửa hàng của bạn</p><a href="<?= e(url('home')) ?>"><?= icon('arrow') ?> Xem website bán hàng</a></div>
    </aside>
    <div class="admin-workspace">
        <header class="admin-topbar"><div><span class="admin-top-label">BookStore</span><span class="admin-divider">/</span><span><?= e($title) ?></span></div><div class="admin-top-actions"><span class="admin-avatar"><?= e(mb_strtoupper(mb_substr($user['name'], 0, 1))) ?></span><div><strong><?= e($user['name']) ?></strong><small>Quản trị viên</small></div><form method="post" action="<?= e(url('logout')) ?>"><?= csrf_field() ?><button class="admin-logout" type="submit">Đăng xuất</button></form></div></header>
        <main id="admin-main" class="admin-content">
            <div class="admin-heading"><div><div class="admin-eyebrow">QUẢN TRỊ / <?= e(mb_strtoupper($title)) ?></div><h1><?= e($title) ?></h1></div><a class="button" href="<?= e(url('admin-product')) ?>"><span aria-hidden="true">＋</span> Thêm sách mới</a></div>
            <?php if ($flash): ?><div class="admin-alert <?= $flash['type'] === 'error' ? 'is-error' : '' ?>" role="<?= $flash['type'] === 'error' ? 'alert' : 'status' ?>"><?= e($flash['message']) ?></div><?php endif; ?>
            <?php require ROOT_PATH . '/view/admin/' . $view . '.php'; ?>
        </main>
        <footer class="admin-footer">BookStore · Hệ thống quản lý bán sách <span>Dữ liệu được cập nhật từ cửa hàng</span></footer>
    </div>
    <script src="<?= e(asset('js/admin.js')) ?>" defer></script>
</body>
</html>
