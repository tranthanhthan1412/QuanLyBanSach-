<div class="container page-content">

    <?php
    $breadcrumbs = [
        ['label' => 'Sách & truyện', 'url' => url('products')],
        ['label' => $product['title']]
    ];
    require ROOT_PATH . '/view/partials/breadcrumb.php';
    ?>

    <div class="product-detail">

        <div class="detail-cover">
            <img src="<?= e(asset($product['image'])) ?>" alt="Ảnh minh họa <?= e($product['title']) ?>" width="300" height="400">
        </div>

        <div class="detail-info">

            <a class="tag" href="<?= e(url('products', ['category' => $product['category'] ?? ''])) ?>">
                <?= e($product['category_name'] ?? 'Chưa phân loại') ?>
            </a>

            <h1><?= e($product['title']) ?></h1>

            <p class="detail-author">
                Tác giả:
                <strong><?= e($product['author'] ?? 'Chưa cập nhật') ?></strong>
            </p>

            <div class="detail-price">
                <strong><?= money($product['price']) ?></strong>
            </div>

            <p class="detail-description">
                <?= e($product['description'] ?? 'Chưa có mô tả cho sách này.') ?>
            </p>

            <div class="stock-note">
                <?= icon('check') ?>

                <?php if (($product['stock'] ?? 0) > 0): ?>
                Còn <?= (int) $product['stock'] ?> cuốn
                <?php else: ?>
                Hết hàng
                <?php endif; ?>
            </div>

            <?php if (($product['stock'] ?? 0) > 0): ?>

            <label class="quantity-label" for="product-quantity">
                Số lượng
            </label>

            <div class="detail-buy">

                <div class="quantity-control">

                    <button type="button" data-quantity-step="-1" aria-label="Giảm số lượng">
                        −
                    </button>

                    <input id="product-quantity" type="number" min="1" max="<?= (int) $product['stock'] ?>" value="1"
                        inputmode="numeric">

                    <button type="button" data-quantity-step="1" aria-label="Tăng số lượng">
                        +
                    </button>

                </div>

                <button class="button" data-add-cart="<?= e($product['id']) ?>" data-quantity-input="product-quantity">
                    <?= icon('cart') ?>
                    Thêm vào giỏ hàng
                </button>

            </div>

            <?php else: ?>

            <button class="button" type="button" disabled>
                Hết hàng
            </button>

            <?php endif; ?>

            <div class="detail-perks">
                <span>
                    <?= icon('truck') ?>
                    Miễn phí giao hàng từ 300.000 ₫
                </span>

                <span>
                    <?= icon('shield') ?>
                    Thanh toán khi nhận hàng (COD)
                </span>
            </div>

        </div>
    </div>

    <section class="book-information panel">

        <h2>Thông tin sách</h2>

        <dl>

            <div>
                <dt>Tên sách</dt>
                <dd><?= e($product['title']) ?></dd>
            </div>

            <div>
                <dt>Tác giả</dt>
                <dd><?= e($product['author'] ?? 'Chưa cập nhật') ?></dd>
            </div>

            <div>
                <dt>Thể loại</dt>
                <dd><?= e($product['category_name'] ?? 'Chưa cập nhật') ?></dd>
            </div>

            <div>
                <dt>Nhà xuất bản</dt>
                <dd><?= e($product['publisher'] ?? 'Chưa cập nhật') ?></dd>
            </div>

            <div>
                <dt>Giá bán</dt>
                <dd><?= money($product['price']) ?></dd>
            </div>

            <div>
                <dt>Tồn kho</dt>
                <dd><?= (int) ($product['stock'] ?? 0) ?> cuốn</dd>
            </div>

        </dl>

    </section>

    <?php if (!empty($related)): ?>

    <section class="related-section">

        <div class="section-heading">
            <h2>Có thể bạn cũng thích</h2>
            <p>Tiếp nối hành trình đọc sách của bạn</p>
        </div>

        <div class="product-grid">

            <?php foreach ($related as $product): ?>

            <?php
                    require ROOT_PATH . '/view/partials/product-card.php';
                    ?>

            <?php endforeach; ?>

        </div>

    </section>

    <?php endif; ?>

</div>