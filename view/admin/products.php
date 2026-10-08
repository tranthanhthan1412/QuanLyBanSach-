<p class="admin-intro">Quản lý danh mục sách, giá bán, ảnh bìa và số lượng tồn kho.</p>
<section class="admin-panel">
    <form class="admin-filters" method="get" action="<?= e(base_url() . '/index.php') ?>"><input type="hidden" name="route" value="admin-products">
        <div class="admin-search"><?= icon('search') ?><input name="q" type="search" aria-label="Tìm sách hoặc tác giả" placeholder="Tìm tên sách, tác giả…" value="<?= e(query('q')) ?>" maxlength="100"></div>
        <select name="category" aria-label="Thể loại"><option value="">Tất cả thể loại</option><?php foreach ($categories as $category): ?><option value="<?= e($category['id']) ?>" <?= query('category') === (string) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option><?php endforeach; ?></select>
        <select name="visibility" aria-label="Trạng thái sách"><?php foreach (['' => 'Tất cả trạng thái', 'visible' => 'Đang hiển thị', 'hidden' => 'Đã ẩn', 'low' => 'Sắp hết hàng'] as $value => $label): ?><option value="<?= e($value) ?>" <?= query('visibility') === $value ? 'selected' : '' ?>><?= e($label) ?></option><?php endforeach; ?></select>
        <button class="button button-outline">Lọc sách</button>
    </form>
    <div class="admin-table-wrap"><table class="admin-table"><thead><tr><th>Sản phẩm</th><th>Thể loại</th><th>Giá bán</th><th>Tồn kho</th><th>Hiển thị</th><th>Thao tác</th></tr></thead><tbody>
    <?php foreach ($listing['rows'] as $book): ?><tr>
        <td><div class="admin-product-cell"><img src="<?= e(asset(book_image($book['hinhAnh']))) ?>" alt="" loading="lazy"><div><a class="admin-name" href="<?= e(url('admin-product', ['id' => $book['maSach']])) ?>"><?= e($book['tenSach']) ?></a><small>#<?= e($book['maSach']) ?> · <?= e($book['tenTG']) ?></small></div></div></td>
        <td><?= e($book['tenTL']) ?></td><td class="admin-nowrap"><?= e(money($book['giaTien'])) ?></td><td><span class="admin-badge <?= $book['tonKho'] <= 5 ? 'amber' : 'neutral' ?>"><?= e($book['tonKho']) ?> cuốn</span></td><td><span class="admin-badge <?= $book['an'] ? 'neutral' : 'green' ?>"><?= $book['an'] ? 'Đã ẩn' : 'Đang bán' ?></span></td>
        <td><div class="admin-row-actions"><a class="admin-link" href="<?= e(url('admin-product', ['id' => $book['maSach']])) ?>">Sửa</a><form method="post" action="<?= e(url('admin-product-delete')) ?>" data-confirm="Xóa vĩnh viễn sách này? Sách có lịch sử đơn hàng sẽ được giữ lại."><?= csrf_field() ?><input type="hidden" name="id" value="<?= e($book['maSach']) ?>"><input type="hidden" name="version" value="<?= e($book['phienBan']) ?>"><button class="admin-danger" type="submit">Xóa</button></form></div></td>
    </tr><?php endforeach; ?>
    <?php if (!$listing['rows']): ?><tr><td colspan="6"><div class="admin-empty"><?= icon('book') ?><h3>Chưa tìm thấy sách</h3><p>Thử thay đổi bộ lọc hoặc thêm sách mới.</p></div></td></tr><?php endif; ?>
    </tbody></table></div>
    <?php require ROOT_PATH . '/view/admin/pagination.php'; ?>
</section>
