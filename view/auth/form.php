<div class="container auth-page">
    <div class="auth-card panel">
        <a class="brand" href="<?= e(url()) ?>"><?= icon('book') ?><span>BookStore</span></a>
        <h1><?= $isRegister ? 'Tạo tài khoản' : 'Chào mừng bạn trở lại' ?></h1>
        <p class="muted"><?= $isRegister ? 'Bắt đầu hành trình khám phá những cuốn sách hay.' : 'Một thế giới sách đang chờ bạn khám phá.' ?></p>
        <?php $notice = 'Form chỉ kiểm tra dữ liệu trên trình duyệt. Chưa có xác thực hoặc tài khoản thật; mật khẩu và thông tin cá nhân không được gửi hay lưu.'; require ROOT_PATH . '/view/partials/notice.php'; ?>
        <form data-demo-form="<?= $isRegister ? 'register' : 'login' ?>" novalidate method="post" action="<?= e(url($isRegister ? 'register' : 'login')) ?>" autocomplete="off">
            <?php if ($isRegister): ?><div class="field"><label for="fullname">Họ và tên</label><input id="fullname" name="name" required minlength="2" maxlength="80" placeholder="Nhập họ và tên mẫu" aria-describedby="fullname-error"><span class="field-error" id="fullname-error"></span></div><?php endif; ?>
            <div class="field"><label for="auth-email">Email</label><input id="auth-email" name="email" type="email" required placeholder="ban@example.com" aria-describedby="auth-email-error"><span class="field-error" id="auth-email-error"></span></div>
            <div class="field"><label for="password">Mật khẩu</label><div class="password-wrap"><input id="password" name="password" type="password" required minlength="8" maxlength="128" placeholder="Ít nhất 8 ký tự (dùng mật khẩu mẫu)" autocomplete="new-password" aria-describedby="password-error"><button type="button" data-toggle-password="password" class="icon-button" aria-label="Hiện mật khẩu" aria-pressed="false"><?= icon('eye') ?></button></div><span class="field-error" id="password-error"></span></div>
            <?php if ($isRegister): ?><div class="field"><label for="confirm-password">Nhập lại mật khẩu</label><div class="password-wrap"><input id="confirm-password" name="confirm" type="password" required minlength="8" autocomplete="new-password" placeholder="Nhập lại mật khẩu mẫu" aria-describedby="confirm-password-error"><button type="button" data-toggle-password="confirm-password" class="icon-button" aria-label="Hiện mật khẩu nhập lại" aria-pressed="false"><?= icon('eye') ?></button></div><span class="field-error" id="confirm-password-error"></span></div><?php endif; ?>
            <button class="button full-width" type="submit"><?= $isRegister ? 'Thử đăng ký' : 'Thử đăng nhập' ?> <?= icon('arrow') ?></button>
            <p class="form-result" role="status" tabindex="-1" hidden></p>
        </form>
        <p class="auth-switch"><?= $isRegister ? 'Đã có tài khoản?' : 'Chưa có tài khoản?' ?> <a class="text-link" href="<?= e(url($isRegister ? 'login' : 'register')) ?>"><?= $isRegister ? 'Đăng nhập' : 'Đăng ký' ?></a></p>
        <a class="demo-account-link" href="<?= e(url('account')) ?>">Xem giao diện tài khoản mẫu <?= icon('arrow') ?></a>
    </div>
</div>
