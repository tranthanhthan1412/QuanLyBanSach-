<p class="admin-intro">Phiếu đã nhập kho; thông tin dưới đây là chứng từ lưu tại thời điểm xác nhận và không thể chỉnh sửa.</p>
<section class="admin-panel">
    <div class="form-row">
        <div><strong>Mã phiếu:</strong> #<?= e($receipt['maPN']) ?><p><strong>Ngày nhập:</strong> <?= e($receipt['ngayNhap']) ?></p><p><strong>Thời điểm ghi nhận:</strong> <?= e($receipt['ngayTao']) ?></p></div>
        <div><strong>Người lập:</strong> <?= e($receipt['tenND']) ?><p><strong>Nhà cung cấp:</strong> <?= e($receipt['nhaCungCap'] ?: '—') ?></p><p><span class="admin-badge green">Đã nhập kho</span></p></div>
    </div>
    <?php if ($receipt['ghiChu']): ?><p><strong>Ghi chú:</strong> <?= e($receipt['ghiChu']) ?></p><?php endif; ?>
    <div class="admin-table-wrap"><table class="admin-table">
        <thead><tr><th>Sách tại thời điểm nhập</th><th>Số lượng</th><th>Đơn giá nhập</th><th>Thành tiền</th><th>Tồn kho hiện tại</th></tr></thead>
        <tbody><?php foreach ($receipt['items'] as $item): ?><tr>
            <td><?= e($item['tenSachLucNhap']) ?> <small>#<?= e($item['maSach']) ?></small></td>
            <td><?= e($item['soLuong']) ?> cuốn</td>
            <td class="admin-nowrap"><?= e(money($item['donGiaNhap'])) ?></td>
            <td class="admin-nowrap"><?= e(money($item['thanhTien'])) ?></td>
            <td><?= e($item['tonKho']) ?> cuốn</td>
        </tr><?php endforeach; ?></tbody>
        <tfoot><tr><th colspan="3">Tổng giá trị nhập kho</th><th colspan="2"><?= e(money($receipt['tongTienNhap'])) ?></th></tr></tfoot>
    </table></div>
    <p class="muted">Tồn kho hiện tại có thể thay đổi do bán hàng hoặc các phiếu nhập tiếp theo; số lượng/giá nhập trên phiếu không đổi.</p>
</section>
