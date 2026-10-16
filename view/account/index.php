<div class="container page-content">
    <?php $breadcrumbs = [['label' => 'Tài khoản']]; require ROOT_PATH . '/view/partials/breadcrumb.php'; ?>
    <div class="page-heading">
        <h1>Tài khoản của bạn</h1>
        <p>Xin chào, <strong><?= e($user['name']) ?></strong>!</p>
    </div>
    <div class="account-grid">
        <?php require ROOT_PATH . '/view/partials/account-nav.php'; ?>
        <section class="panel account-main">
            <h2>Thông tin cá nhân</h2>
            <p class="muted">Cập nhật thông tin liên hệ của bạn. Số điện thoại và địa chỉ không bắt buộc.</p>
            <?php if ($error): ?><div class="notice notice-error" role="alert"><?= e($error) ?></div><?php endif; ?>
            <?php if ($success): ?><div class="notice" role="status"><?= e($success) ?></div><?php endif; ?>
            <form method="post" action="<?= e(url('account')) ?>">
                <?= csrf_field() ?>
                <div class="form-row">
                    <div class="field">
                        <label for="profile-name">Họ và tên <span>*</span></label>
                        <input id="profile-name" name="name" type="text" required minlength="2" maxlength="80" autocomplete="name" value="<?= e($profile['name']) ?>">
                    </div>
                    <div class="field">
                        <label for="profile-phone">Số điện thoại</label>
                        <input id="profile-phone" name="phone" type="tel" maxlength="30" autocomplete="tel" placeholder="0901234567" value="<?= e($profile['phone']) ?>">
                    </div>
                </div>
                <div class="field">
                    <label for="profile-email">Email đăng nhập</label>
                    <input id="profile-email" type="email" value="<?= e($user['email']) ?>" readonly aria-describedby="profile-email-help">
                    <small id="profile-email-help" class="muted">Email đăng nhập hiện chưa hỗ trợ thay đổi.</small>
                </div>
                <div class="form-row">
                    <div class="field">
                        <label for="profile-province">Tỉnh / Thành phố</label>
                        <input id="profile-province" name="province" type="text" minlength="2" maxlength="80" autocomplete="address-level1" placeholder="Nhập tỉnh hoặc thành phố" value="<?= e($profile['province'] ?? '') ?>">
                    </div>
                    <div class="field">
                        <label for="profile-ward">Phường / Xã</label>
                        <input id="profile-ward" name="ward" type="text" minlength="2" maxlength="80" autocomplete="address-level2" placeholder="Nhập phường hoặc xã" value="<?= e($profile['ward'] ?? '') ?>">
                    </div>
                </div>
                <div class="field">
                    <label for="profile-address">Địa chỉ cụ thể</label>
                    <textarea id="profile-address" name="address" rows="3" maxlength="255" autocomplete="street-address" placeholder="Số nhà, tên đường…"><?= e($profile['address']) ?></textarea>
                    <small class="muted">Địa chỉ đã lưu trước đây vẫn được giữ lại. Hãy bổ sung tỉnh/thành phố và phường/xã để tự điền khi đặt hàng.</small>
                </div>
                <div class="account-actions">
                    <button class="button" type="submit">Lưu thay đổi</button>
                    <a class="button button-outline" href="<?= e(url('account-password')) ?>">Đổi mật khẩu</a>
                </div>
            </form>
        </section>
    </div>
</div>
