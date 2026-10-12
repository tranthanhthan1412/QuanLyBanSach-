<div class="container page-content">
    <?php $breadcrumbs = [['label' => 'Giỏ hàng', 'url' => url('cart')], ['label' => 'Đặt hàng']]; require ROOT_PATH . '/view/partials/breadcrumb.php'; ?>
    <div class="page-heading"><h1>Thông tin đặt hàng</h1><p>Chỉ một bước nữa để hoàn tất trải nghiệm mua sách.</p></div>
    <?php if (!$user): ?>
    <div class="panel empty-state"><h2>Đăng nhập để đặt hàng</h2><p>Thông tin đơn hàng sẽ được gửi đến email tài khoản của bạn.</p><a class="button" href="<?= e(url('login')) ?>">Đăng nhập</a></div>
    <?php else: ?>
    <?php if ($error): ?><p class="notice notice-error" role="alert"><?= e($error) ?></p><?php endif; ?>
    <p class="notice">Thông tin đơn hàng sẽ được gửi đến <?= e($user['email']) ?>. Bạn thanh toán khi nhận hàng.</p>
    <noscript><p class="notice notice-error">Vui lòng bật JavaScript để sử dụng giỏ hàng và đặt hàng.</p></noscript>
    <div id="checkout-empty" hidden></div>
    <div class="checkout-grid" id="checkout-content" hidden>
        <form class="panel checkout-form" id="checkout-form" data-checkout-form method="post" action="<?= e(url('checkout')) ?>" novalidate>
            <?= csrf_field() ?>
            <input type="hidden" name="checkout_token" value="<?= e($_SESSION['checkout_token']) ?>">
            <h2>Thông tin người nhận</h2>
            <div class="form-row"><div class="field"><label for="recipient">Họ và tên <span>*</span></label><input id="recipient" name="name" value="<?= e($old['name'] ?? $user['name']) ?>" required minlength="2" maxlength="80" autocomplete="off" placeholder="Nguyễn Văn Mẫu" aria-describedby="recipient-error"><span class="field-error" id="recipient-error"></span></div><div class="field"><label for="phone">Số điện thoại <span>*</span></label><input id="phone" name="phone" value="<?= e($old['phone'] ?? '') ?>" type="tel" inputmode="tel" required autocomplete="off" placeholder="0901234567" aria-describedby="phone-error"><span class="field-error" id="phone-error"></span></div></div>
            <div class="field"><label for="email">Email nhận thông tin đơn hàng</label><input id="email" type="email" value="<?= e($user['email']) ?>" readonly><small>Email của tài khoản đang đăng nhập.</small></div>
            <h2>Địa chỉ giao hàng</h2>
            <div class="form-row"><div class="field"><label for="province">Tỉnh / Thành phố <span>*</span></label><input id="province" name="province" value="<?= e($old['province'] ?? '') ?>" required minlength="2" maxlength="80" autocomplete="off" placeholder="Nhập tỉnh hoặc thành phố" aria-describedby="province-error"><span class="field-error" id="province-error"></span></div><div class="field"><label for="ward">Phường / Xã <span>*</span></label><input id="ward" name="ward" value="<?= e($old['ward'] ?? '') ?>" required minlength="2" maxlength="80" autocomplete="off" placeholder="Nhập phường hoặc xã" aria-describedby="ward-error"><span class="field-error" id="ward-error"></span></div></div>
            <div class="field"><label for="address">Địa chỉ cụ thể <span>*</span></label><input id="address" name="address" value="<?= e($old['address'] ?? '') ?>" required minlength="5" maxlength="200" autocomplete="off" placeholder="Số nhà, tên đường…" aria-describedby="address-error"><span class="field-error" id="address-error"></span></div>
            <div class="field"><label for="note">Ghi chú đơn hàng</label><textarea id="note" name="note" rows="3" maxlength="500" placeholder="Ghi chú cho đơn hàng (không bắt buộc)"><?= e($old['note'] ?? '') ?></textarea></div>
            <h2>Phương thức thanh toán</h2><label class="payment-option"><input type="radio" name="payment" value="cod" checked> <?= icon('truck') ?><span><strong>Thanh toán khi nhận hàng (COD)</strong><small>Đơn hàng sẽ được cửa hàng xác nhận trước khi giao.</small></span></label>
            <p class="form-result" role="status" tabindex="-1" hidden></p>
        </form>
        <aside id="checkout-summary" class="order-summary panel" aria-label="Tóm tắt đơn hàng"></aside>
    </div>
    <?php endif; ?>
</div>
