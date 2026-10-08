<div class="admin-pagination"><span><?= e($listing['total']) ?> kết quả · Trang <?= e($listing['page']) ?>/<?= e($listing['pages']) ?></span><nav aria-label="Phân trang">
<?php $filters = []; foreach (['q', 'kind', 'category', 'visibility', 'status'] as $key) { if (query($key) !== '') { $filters[$key] = query($key); } } ?>
<?php if ($listing['page'] > 1): ?><a class="button button-outline button-small" href="<?= e(url(query('route'), $filters + ['page' => $listing['page'] - 1])) ?>">← Trước</a><?php endif; ?>
<?php if ($listing['page'] < $listing['pages']): ?><a class="button button-outline button-small" href="<?= e(url(query('route'), $filters + ['page' => $listing['page'] + 1])) ?>">Sau →</a><?php endif; ?>
</nav></div>
