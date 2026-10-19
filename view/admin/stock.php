<p class="admin-intro">Ghi nhận chứng từ nhập sách, giá vốn và người thực hiện. Mỗi phiếu đã lập là chứng từ chỉ đọc.</p>
<section class="admin-panel">
    <div class="admin-table-wrap">
        <table class="admin-table">
            <thead><tr><th>Phiếu nhập</th><th>Ngày nhập</th><th>Nhà cung cấp</th><th>Người lập</th><th>Số lượng</th><th>Tổng tiền nhập</th><th>Thao tác</th></tr></thead>
            <tbody>
            <?php foreach ($listing['rows'] as $row): ?>
            <tr>
                <td><strong>#<?= e($row['maPN']) ?></strong> <span class="admin-badge green">Đã nhập</span></td>
                <td><?= e($row['ngayNhap']) ?></td>
                <td><?= e($row['nhaCungCap'] ?: '—') ?></td>
                <td><?= e($row['tenND']) ?></td>
                <td><?= e($row['tongSoLuong']) ?> cuốn (<?= e($row['soDauSach']) ?> đầu sách)</td>
                <td class="admin-nowrap"><?= e(money($row['tongTienNhap'])) ?></td>
                <td><a class="admin-link" href="<?= e(url('admin-stock-detail', ['id' => $row['maPN']])) ?>">Xem phiếu</a></td>
            </tr>
            <?php endforeach; ?>
            <?php if (!$listing['rows']): ?>
            <tr><td colspan="7"><div class="admin-empty"><h3>Chưa có phiếu nhập kho</h3><p>Chọn “Lập phiếu nhập” để nhập sách lần đầu.</p></div></td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php if ($listing['pages'] > 1): ?>
    <nav class="admin-filters" aria-label="Phân trang phiếu nhập">
        <?php if ($listing['page'] > 1): ?><a class="button button-outline" href="<?= e(url('admin-stock', ['page' => $listing['page'] - 1])) ?>">← Trang trước</a><?php endif; ?>
        <span>Trang <?= e($listing['page']) ?> / <?= e($listing['pages']) ?> · <?= e($listing['total']) ?> phiếu</span>
        <?php if ($listing['page'] < $listing['pages']): ?><a class="button button-outline" href="<?= e(url('admin-stock', ['page' => $listing['page'] + 1])) ?>">Trang sau →</a><?php endif; ?>
    </nav>
    <?php endif; ?>
</section>
