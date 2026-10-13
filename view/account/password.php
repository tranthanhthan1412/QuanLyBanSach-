<div class="container page-content">
    <?php $breadcrumbs = [['label' => 'Tài khoản', 'url' => url('account')], ['label' => 'Đổi mật khẩu']]; require ROOT_PATH . '/view/partials/breadcrumb.php'; ?>
    <div class="page-heading"><h1>Đổi mật khẩu</h1><p>Bảo vệ tài khoản của bạn bằng mật khẩu mới.</p></div>
    <div class="account-grid">
        <?php require ROOT_PATH . '/view/partials/account-nav.php'; ?>
        <section class="panel account-main">
            <h2>Mật khẩu tài khoản</h2>
            <p class="muted">Sau khi đổi mật khẩu, bạn cần đăng nhập lại trên các thiết bị đang sử dụng.</p>
            <?php if ($error): ?><div class="notice notice-error" role="alert"><?= e($error) ?></div><?php endif; ?>
            <form method="post" action="<?= e(url('account-password')) ?>">
                <?= csrf_field() ?>
                <input type="text" name="username" value="<?= e($user['email']) ?>" autocomplete="username" hidden>
                <?php foreach ([
                    'current_password' => ['Mật khẩu hiện tại', 'current-password'],
                    'password' => ['Mật khẩu mới', 'new-password'],
                    'confirm' => ['Nhập lại mật khẩu mới', 'new-password'],
                ] as $name => [$label, $autocomplete]): ?>
                <div class="field">
                    <label for="account-<?= e($name) ?>"><?= e($label) ?> <span>*</span></label>
                    <div class="password-wrap">
                        <input id="account-<?= e($name) ?>" name="<?= e($name) ?>" type="password" required maxlength="72" autocomplete="<?= e($autocomplete) ?>"<?= $name !== 'current_password' ? ' aria-describedby="password-help"' : '' ?>>
                        <button type="button" data-toggle-password="account-<?= e($name) ?>" class="icon-button" aria-label="Hiện <?= e(mb_strtolower($label)) ?>" aria-pressed="false"><?= icon('eye') ?></button>
                    </div>
                </div>
                <?php endforeach; ?>
                <p id="password-help" class="muted">Mật khẩu mới cần từ 8 đến 72 byte; ký tự có dấu có thể chiếm nhiều byte.</p>
                <div class="account-actions">
                    <button class="button" type="submit">Đổi mật khẩu</button>
                    <a class="button button-outline" href="<?= e(url('account')) ?>">Quay lại hồ sơ</a>
                </div>
            </form>
        </section>
    </div>
</div>
