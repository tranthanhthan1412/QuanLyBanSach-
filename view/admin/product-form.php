<a class="admin-back" href="<?= e(url('admin-products')) ?>">← Quay lại danh sách sách</a>
<?php if ($error): ?><div class="admin-alert is-error" role="alert"><?= e($error) ?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" action="<?= e(url('admin-product', $product['maSach'] ? ['id' => $product['maSach']] : [])) ?>" class="admin-editor">
    <?= csrf_field() ?><input type="hidden" name="phienBan" value="<?= e($product['phienBan']) ?>">
    <section class="admin-panel admin-form-panel"><h2>Thông tin sách</h2><p>Thông tin này sẽ xuất hiện trên website bán hàng.</p>
        <div class="field"><label for="book-name">Tên sách *</label><input id="book-name" name="tenSach" required minlength="2" maxlength="255" value="<?= e($product['tenSach']) ?>" placeholder="Nhập tên sách"></div>
        <div class="field"><label for="book-description">Mô tả</label><textarea id="book-description" name="moTa" rows="8" maxlength="20000" placeholder="Giới thiệu nội dung cuốn sách…"><?= e($product['moTa']) ?></textarea></div>
        <div class="admin-form-grid"><div class="field"><label for="book-price">Giá bán (₫) *</label><input id="book-price" name="giaTien" type="number" min="0" max="9999999999" step="0.01" required value="<?= e($product['giaTien']) ?>"></div><div class="field"><label for="book-stock">Số cuốn tồn kho *</label><input id="book-stock" name="tonKho" type="number" min="0" max="9999999" step="1" required value="<?= e($product['tonKho']) ?>"></div></div>
        <div class="admin-form-grid">
            <?php foreach ([['maTL', 'Thể loại', $categories], ['maTG', 'Tác giả', $authors], ['maNXB', 'Nhà xuất bản', $publishers]] as [$key, $label, $options]): ?>
            <div class="field"><label for="book-<?= e($key) ?>"><?= e($label) ?> *</label><select id="book-<?= e($key) ?>" name="<?= e($key) ?>" required><option value="">Chọn <?= e(mb_strtolower($label)) ?></option><?php foreach ($options as $option): ?><option value="<?= e($option['id']) ?>" <?= (string) $product[$key] === (string) $option['id'] ? 'selected' : '' ?>><?= e($option['name']) ?></option><?php endforeach; ?></select></div>
            <?php endforeach; ?>
        </div><p class="admin-hint">Chưa có thể loại, tác giả hoặc nhà xuất bản phù hợp? Thêm tại mục tương ứng trong menu quản trị.</p>
    </section>
    <aside class="admin-editor-side"><section class="admin-panel admin-form-panel"><h2>Ảnh bìa</h2><div class="admin-cover-preview"><img id="cover-preview" src="<?= e(asset(book_image($product['hinhAnh']))) ?>" alt="Ảnh bìa hiện tại"></div><div class="field"><label for="book-image">Tải ảnh mới</label><input id="book-image" name="image" type="file" accept="image/jpeg,image/png,image/webp" data-preview="cover-preview"><p class="admin-hint">JPG, PNG, WebP · tối đa 2 MB, 6000 × 6000 px.</p></div><?php if ($product['hinhAnh']): ?><label class="admin-check"><input type="checkbox" name="remove_image" value="1"> Bỏ ảnh bìa hiện tại</label><?php endif; ?></section>
        <section class="admin-panel admin-form-panel"><h2>Hiển thị</h2><div class="field"><label for="book-visible">Trạng thái sản phẩm</label><select id="book-visible" name="an"><option value="0" <?= !$product['an'] ? 'selected' : '' ?>>Hiển thị trên cửa hàng</option><option value="1" <?= $product['an'] ? 'selected' : '' ?>>Ẩn khỏi cửa hàng</option></select></div><p class="admin-hint">Sách bị ẩn vẫn được giữ trong lịch sử đơn hàng.</p></section>
        <button class="button full-width" type="submit"><?= icon('check') ?> Lưu sản phẩm</button>
    </aside>
</form>
