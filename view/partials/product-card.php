<article class="product-card">
    <a class="product-image" href="<?= e(url('product', ['id' => $product['id']])) ?>" tabindex="-1" aria-hidden="true">
        <img src="<?= e(asset($product['image'])) ?>" alt="" loading="lazy" width="360" height="256">
        <span class="product-badges">
            <?php if ($product['badge']): ?><span class="badge <?= $product['badge'] === 'Mới' ? 'badge-new' : 'badge-best' ?>"><?= e($product['badge']) ?></span><?php endif; ?>
            <?php if ($product['old_price'] > $product['price']): ?><span class="badge badge-sale">−<?= (int) round((1 - $product['price'] / $product['old_price']) * 100) ?>%</span><?php endif; ?>
        </span>
    </a>
    <div class="product-body">
        <a class="tag" href="<?= e(url('products', ['category' => $product['category']])) ?>"><?= e($product['category_name']) ?></a>
        <h3><a href="<?= e(url('product', ['id' => $product['id']])) ?>"><?= e($product['title']) ?></a></h3>
        <p class="product-author"><?= e($product['author']) ?></p>
        <div class="rating" aria-label="Đánh giá mẫu <?= e($product['rating']) ?> trên 5"><span class="stars" aria-hidden="true">★★★★<span>★</span></span><span><?= e($product['rating']) ?> (<?= number_format($product['reviews'], 0, ',', '.') ?>)</span></div>
        <div class="price"><strong><?= money($product['price']) ?></strong><?php if ($product['old_price'] > $product['price']): ?><del><?= money($product['old_price']) ?></del><?php endif; ?></div>
        <button class="button add-to-cart" data-add-cart="<?= $product['id'] ?>" aria-label="Thêm <?= e($product['title']) ?> vào giỏ"><?= icon('cart') ?><span>Thêm vào giỏ</span></button>
    </div>
</article>
