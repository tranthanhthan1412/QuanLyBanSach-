<p class="admin-intro">Lịch sử thay đổi sản phẩm, danh mục, đơn hàng và quyền tài khoản.</p>
<section class="admin-panel"><div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Thời gian (máy chủ)</th><th>Người thực hiện</th><th>Thao tác</th><th>Chi tiết</th></tr></thead><tbody>
<?php foreach ($listing['rows'] as $row): ?><tr><td class="admin-nowrap"><?= e($row['ngayTao']) ?></td><td><?= e($row['tenND'] ?: 'Tài khoản đã xóa') ?></td><td><strong><?= e($row['hanhDong']) ?></strong><small><?= e($row['doiTuong']) ?></small></td><td class="admin-log-detail"><?= e($row['chiTiet']) ?></td></tr><?php endforeach; ?>
<?php if (!$listing['rows']): ?><tr><td colspan="4"><div class="admin-empty"><?= icon('clock') ?><h3>Chưa có hoạt động</h3><p>Các thao tác quản trị sẽ được ghi nhận tại đây.</p></div></td></tr><?php endif; ?>
</tbody></table></div><?php require ROOT_PATH . '/view/admin/pagination.php'; ?></section>
