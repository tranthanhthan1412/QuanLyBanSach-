<p class="admin-intro">Lập phiếu và xác nhận nhập ngay. Sau khi lưu, số lượng sách sẽ được cộng vào tồn kho và không thể sửa/xóa phiếu.</p>
<?php if ($error): ?><div class="admin-alert is-error" role="alert"><?= e($error) ?></div><?php endif; ?>
<section class="admin-panel">
    <form method="post" action="<?= e(url('admin-stock-create')) ?>" id="stock-entry-form">
        <?= csrf_field() ?>
        <input type="hidden" name="entry_token" value="<?= e($token) ?>">
        <div class="form-row">
            <div class="field"><label for="stock-date">Ngày nhập <span>*</span></label><input type="date" id="stock-date" name="date" required min="2000-01-01" max="<?= e(date('Y-m-d')) ?>" value="<?= e($form['date']) ?>"></div>
            <div class="field"><label for="stock-supplier">Nhà cung cấp (không bắt buộc)</label><input type="text" id="stock-supplier" name="supplier" maxlength="150" value="<?= e($form['supplier']) ?>" placeholder="Tên nhà cung cấp"></div>
        </div>
        <div class="field"><label for="stock-note">Ghi chú</label><textarea id="stock-note" name="note" rows="2" maxlength="500" placeholder="Số hóa đơn, ghi chú lô hàng…"><?= e($form['note']) ?></textarea></div>
        <h2>Sách nhập kho</h2>
        <p class="muted">Chọn tối đa 20 đầu sách, mỗi sách chỉ xuất hiện một lần. Giá nhập không làm thay đổi giá bán.</p>
        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead><tr><th>Sách</th><th>Số lượng nhập</th><th>Đơn giá nhập (đ)</th></tr></thead>
                <tbody id="stock-item-rows">
                <?php foreach ($form['items'] as $i => $row): ?>
                <tr>
                    <td><label class="sr-only" for="book-<?= e($i) ?>">Sách dòng <?= e($i + 1) ?></label><select name="book_id[]" id="book-<?= e($i) ?>" aria-label="Chọn sách dòng <?= e($i + 1) ?>"><option value="">-- Chọn sách --</option><?php foreach ($books as $book): ?><option value="<?= e($book['maSach']) ?>" <?= (string) $row['book_id'] === (string) $book['maSach'] ? 'selected' : '' ?>>#<?= e($book['maSach']) ?> · <?= e($book['tenSach']) ?> (tồn <?= e($book['tonKho']) ?><?= $book['an'] ? ', đang ẩn' : '' ?>)</option><?php endforeach; ?></select></td>
                    <td><input name="quantity[]" aria-label="Số lượng dòng <?= e($i + 1) ?>" type="number" min="1" max="100000" step="1" inputmode="numeric" placeholder="VD: 10" value="<?= e($row['quantity']) ?>"></td>
                    <td><input name="unit_price[]" aria-label="Đơn giá dòng <?= e($i + 1) ?>" type="number" min="0" max="99999999.99" step="0.01" inputmode="decimal" placeholder="VD: 50000" value="<?= e($row['unit_price']) ?>"></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="account-actions" style="margin-top:1rem;display:flex;gap:1rem;flex-wrap:wrap">
            <button type="button" class="button button-outline" id="stock-add-item">+ Thêm dòng sách</button>
            <button type="submit" class="button" id="stock-submit" data-confirm="Xác nhận nhập kho? Tồn kho sẽ tăng ngay và phiếu sẽ không thể sửa/xóa.">Xác nhận nhập kho</button>
        </div>
    </form>
</section>
<template id="stock-row-template">
    <tr>
        <td><select name="book_id[]" aria-label="Chọn sách"><option value="">-- Chọn sách --</option><?php foreach ($books as $book): ?><option value="<?= e($book['maSach']) ?>">#<?= e($book['maSach']) ?> · <?= e($book['tenSach']) ?> (tồn <?= e($book['tonKho']) ?><?= $book['an'] ? ', đang ẩn' : '' ?>)</option><?php endforeach; ?></select></td>
        <td><input type="number" name="quantity[]" aria-label="Số lượng nhập" min="1" max="100000" step="1" inputmode="numeric" placeholder="VD: 10"></td>
        <td><input type="number" name="unit_price[]" aria-label="Đơn giá nhập" min="0" max="99999999.99" step="0.01" inputmode="decimal" placeholder="VD: 50000"></td>
    </tr>
</template>
<script>
(() => {
    const body = document.getElementById('stock-item-rows');
    const button = document.getElementById('stock-add-item');
    const template = document.getElementById('stock-row-template');
    button.addEventListener('click', () => {
        if (body.rows.length >= 20) { button.disabled = true; return; }
        body.appendChild(template.content.cloneNode(true));
        if (body.rows.length >= 20) button.disabled = true;
    });
    if (body.rows.length >= 20) button.disabled = true;
})();
</script>
