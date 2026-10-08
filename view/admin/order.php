<?php [$statusLabel, $statusColor] = order_status($order['trangThai']); $next = \App\Models\AdminModel::nextStatuses($order['trangThai']); ?>
<a class="admin-back" href="<?= e(url('admin-orders')) ?>">← Danh sách đơn hàng</a>
<div class="admin-editor"><div>
    <section class="admin-panel"><div class="admin-panel-heading"><div><h2><?= e($order['tenDH'] ?: 'Đơn #' . $order['maDH']) ?></h2><p><?= e($order['ngayTao'] ?: 'Chưa ghi nhận ngày tạo') ?></p></div><span class="admin-badge <?= e($statusColor) ?>"><?= e($statusLabel) ?></span></div>
        <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Sản phẩm</th><th>Số lượng</th><th>Thành tiền đã lưu</th></tr></thead><tbody><?php foreach ($order['items'] as $item): ?><tr><td><strong><?= e($item['tenSach']) ?></strong><small>Mã #<?= e($item['maSach']) ?> · Còn <?= e($item['tonKho']) ?> cuốn</small></td><td><?= e($item['soLuong']) ?></td><td><?= e(money($item['tongTien'])) ?></td></tr><?php endforeach; ?><?php if (!$order['items']): ?><tr><td colspan="3">Đơn chưa có sản phẩm.</td></tr><?php endif; ?></tbody></table></div><div class="admin-order-total"><span>Tổng tiền sách</span><strong><?= e(money($order['total'])) ?></strong></div>
			<?php if (!empty($order['tenNguoiNhan'])): ?>
			    <div class="admin-order-total">
			        <span>Phí vận chuyển</span>
			        <strong><?= e(money($order['phiVanChuyen'])) ?></strong>
			    </div>

			    <div class="admin-order-total">
			        <span>Tổng thu COD</span>
			        <strong><?= e(money($order['tongThanhToan'])) ?></strong>
			    </div>
			<?php endif; ?>
    </section><section class="admin-panel admin-form-panel admin-spaced"><h2>Ghi chú của đơn hàng</h2><p class="admin-preserve"><?= e($order['ghiChu'] ?: 'Không có ghi chú.') ?></p></section>
    </div><aside class="admin-editor-side"><section class="admin-panel admin-form-panel"><h2>Thông tin tài khoản khách hàng</h2><dl class="admin-details"><dt>Họ tên</dt><dd><?= e($order['tenND']) ?></dd><dt>Email</dt><dd><?= e($order['email']) ?></dd><dt>Điện thoại</dt><dd><?= e($order['SDT'] ?: 'Chưa cập nhật') ?></dd><dt>Địa chỉ hồ sơ</dt><dd><?= e($order['diaChi'] ?: 'Chưa cập nhật') ?></dd></dl></section>
	
	<?php if (!empty($order['tenNguoiNhan'])): ?>
	<section class="admin-panel admin-form-panel">
	    <h2>Thông tin giao hàng (COD)</h2>

	    <dl class="admin-details">
	        <dt>Người nhận</dt>
	        <dd><?= e($order['tenNguoiNhan']) ?></dd>

	        <dt>Số điện thoại</dt>
	        <dd><?= e($order['SDTNguoiNhan']) ?></dd>

	        <dt>Email</dt>
	        <dd><?= e($order['emailNguoiNhan'] ?: 'Không cung cấp') ?></dd>

	        <dt>Địa chỉ giao hàng</dt>
	        <dd><?= e($order['diaChiGiao']) ?></dd>

	        <dt>Phương thức thanh toán</dt>
	        <dd><?= e($order['phuongThucTT']) ?></dd>
	    </dl>
	</section>
	<?php endif; ?>

	<section class="admin-panel admin-form-panel"><h2>Xử lý đơn hàng</h2><?php if ($next): ?><form method="post" action="<?= e(url('admin-order', ['id' => $order['maDH']])) ?>" data-confirm="Xác nhận cập nhật trạng thái đơn hàng?"><?= csrf_field() ?><input type="hidden" name="expected" value="<?= e($order['trangThai']) ?>"><div class="field"><label for="order-status">Trạng thái tiếp theo</label><select id="order-status" name="status" required><?php foreach ($next as $value): ?><option value="<?= e($value) ?>"><?= e(\App\Models\AdminModel::STATUSES[$value]) ?></option><?php endforeach; ?></select></div><div class="field"><label for="order-note">Ghi chú nội bộ / lý do hủy</label><textarea id="order-note" name="note" rows="3" maxlength="500"></textarea></div><p class="admin-hint">Xác nhận đơn sẽ trừ kho. Hủy đơn hoàn lại số lượng đã trừ bởi hệ thống và cần ghi rõ lý do.</p><button class="button full-width" type="submit">Cập nhật trạng thái</button></form><?php else: ?><p>Đơn đã kết thúc hoặc có trạng thái cũ chưa hỗ trợ chuyển đổi.</p><?php endif; ?></section></aside>
</div>
