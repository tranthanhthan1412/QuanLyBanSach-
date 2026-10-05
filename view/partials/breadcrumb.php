<nav class="breadcrumb" aria-label="Đường dẫn">
    <a href="<?= e(url()) ?>">Trang chủ</a>
    <?php foreach (($breadcrumbs ?? []) as $crumb): ?>
        <?= icon('chevron') ?>
        <?php if (isset($crumb['url'])): ?><a href="<?= e($crumb['url']) ?>"><?= e($crumb['label']) ?></a><?php else: ?><span aria-current="page"><?= e($crumb['label']) ?></span><?php endif; ?>
    <?php endforeach; ?>
</nav>
