<article class="product-card">

    <a class="product-image" href="<?= e(url('product', ['id' => $product['id']])) ?>" tabindex="-1" aria-hidden="true">
        <img src="<?= e(asset($product['image'])) ?>" alt="" width="300" height="400" loading="lazy">
    </a>

    <div class="product-body">

        <a class="tag" href="<?= e(url('products', ['category' => $product['category']])) ?>">
            <?= e($product['category_name']) ?>
        </a>

        <h3>
            <a href="<?= e(url('product', ['id' => $product['id']])) ?>">
                <?= e($product['title']) ?>
            </a>
        </h3>

        <p class="product-author">
            <?= e($product['author']) ?>
        </p>

        <div class="price">
            <strong><?= money($product['price']) ?></strong>
        </div>

        <p class="product-stock">
            <?php if ((int) $product['stock'] > 0): ?>
            Còn <?= (int) $product['stock'] ?> cuốn
            <?php else: ?>
            Hết hàng
            <?php endif; ?>
        </p>

        <button class="button add-to-cart" data-add-cart="<?= e($product['id']) ?>"
            aria-label="Thêm <?= e($product['title']) ?> vào giỏ" <?= (int) $product['stock'] <= 0 ? 'disabled' : '' ?>>
            <?= icon('cart') ?>
            <span>Thêm vào giỏ</span>
        </button>

    </div>

</article>