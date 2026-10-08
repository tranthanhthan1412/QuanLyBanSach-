<div class="container page-content">
    <?php $breadcrumbs = [['label' => 'Giỏ hàng', 'url' => url('cart')], ['label' => 'Đặt hàng']]; require ROOT_PATH . '/view/partials/breadcrumb.php'; ?>
    <div class="page-heading"><h1>Thông tin đặt hàng</h1><p>Chỉ một bước nữa để hoàn tất trải nghiệm mua sách.</p></div>
	<?php if (!$loggedIn): ?>
	    <div class="panel" style="padding:18px;margin-bottom:20px">
	        Bạn cần <a href="<?= e(url('login')) ?>"><strong>đăng nhập</strong></a>
	        trước khi gửi đơn hàng COD.
	        Giỏ hàng vẫn được giữ trên trình duyệt.
	    </div>
	<?php endif; ?>
    <div id="checkout-empty" hidden></div>
    <div class="checkout-grid" id="checkout-content" hidden>
        <form class="panel checkout-form" id="checkout-form" data-demo-form="checkout" method="post" action="<?= e(url('checkout')) ?>" novalidate>
		<?= csrf_field() ?>
		<input type="hidden" name="checkout_nonce" value="<?= e($_SESSION['checkout_nonce']) ?>">
		<input type="hidden" name="items" id="checkout-items" value="">
		
            <h2>Thông tin người nhận</h2>
            <div class="form-row"><div class="field"><label for="recipient">Họ và tên <span>*</span></label><input id="recipient" name="name" required minlength="2" maxlength="80" autocomplete="off" placeholder="Nguyễn Văn Mẫu" aria-describedby="recipient-error"><span class="field-error" id="recipient-error"></span></div><div class="field"><label for="phone">Số điện thoại <span>*</span></label><input id="phone" name="phone" type="tel" inputmode="tel" required autocomplete="off" placeholder="0901234567" aria-describedby="phone-error"><span class="field-error" id="phone-error"></span></div></div>
            <div class="field"><label for="email">Email (không bắt buộc)</label><input id="email" name="email" type="email" autocomplete="off" placeholder="khachhang@example.com" aria-describedby="email-error"><span class="field-error" id="email-error"></span></div>
            <h2>Địa chỉ giao hàng</h2>
            <div class="form-row"><div class="field"><label for="province">Tỉnh / Thành phố <span>*</span></label><input id="province" name="province" required minlength="2" maxlength="80" autocomplete="off" placeholder="Nhập tỉnh hoặc thành phố" aria-describedby="province-error"><span class="field-error" id="province-error"></span></div><div class="field"><label for="ward">Phường / Xã <span>*</span></label><input id="ward" name="ward" required minlength="2" maxlength="80" autocomplete="off" placeholder="Nhập phường hoặc xã" aria-describedby="ward-error"><span class="field-error" id="ward-error"></span></div></div>
            <div class="field"><label for="address">Địa chỉ cụ thể <span>*</span></label><input id="address" name="address" required minlength="5" maxlength="200" autocomplete="off" placeholder="Số nhà, tên đường…" aria-describedby="address-error"><span class="field-error" id="address-error"></span></div>
            <div class="field"><label for="note">Ghi chú đơn hàng</label><textarea id="note" name="note" rows="3" maxlength="500" placeholder="Ghi chú cho người giao hàng (không bắt buộc)"></textarea></div>
            <h2>Phương thức thanh toán</h2><label class="payment-option"><input type="radio" name="payment" value="cod" checked> <?= icon('truck') ?><span><strong>Thanh toán khi nhận hàng (COD)</strong><small>Trả tiền mặt khi nhận sách, không cần thanh toán trực tuyến</small></span></label>
            <p class="form-result" role="status" tabindex="-1" hidden></p>
        </form>
        <aside id="checkout-summary" class="order-summary panel" aria-label="Tóm tắt đơn hàng"></aside>
    </div>
</div>
