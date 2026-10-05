<header class="site-header">
    <div class="container header-top">
        <a class="brand" href="<?= e(url()) ?>" aria-label="BookStore — Trang chủ"><?= icon('book') ?><span>BookStore</span></a>
        <form class="header-search" action="<?= e(base_url() . '/index.php') ?>" method="get" role="search">
            <input type="hidden" name="route" value="products">
            <div class="search-input"><?= icon('search') ?><label class="sr-only" for="header-search">Tìm sách, tác giả hoặc thể loại</label><input id="header-search" name="q" type="search" placeholder="Tìm sách, tác giả hoặc thể loại..." value="<?= e(query('q')) ?>" maxlength="100"></div>
            <button class="button button-small" type="submit">Tìm</button>
        </form>
        <div class="header-actions">
            <a class="header-action" href="<?= e(url('login')) ?>"><?= icon('user') ?><span>Tài khoản</span></a>
            <a class="header-action cart-link" href="<?= e(url('cart')) ?>"><?= icon('cart') ?><span>Giỏ hàng</span><span class="cart-badge" data-cart-badge hidden>0</span></a>
            <button class="icon-button menu-toggle" id="menu-toggle" aria-label="Mở danh mục" aria-expanded="false" aria-controls="category-nav"><?= icon('menu') ?></button>
        </div>
    </div>
    <nav class="container category-nav" id="category-nav" aria-label="Danh mục sách">
        <form class="mobile-search" action="<?= e(base_url() . '/index.php') ?>" method="get" role="search">
            <input type="hidden" name="route" value="products">
            <div class="search-input"><?= icon('search') ?><label class="sr-only" for="mobile-search">Tìm sách trong danh mục</label><input id="mobile-search" name="q" type="search" placeholder="Tìm sách..." value="<?= e(query('q')) ?>" maxlength="100"></div>
            <button class="button button-small" type="submit">Tìm</button>
        </form>
        <a href="<?= e(url('products')) ?>" <?= query('route') === 'products' && query('category') === '' ? 'aria-current="page"' : '' ?>>Tất cả</a>
        <?php foreach ($categories as $navCategory): ?>
        <a href="<?= e(url('products', ['category' => $navCategory['slug']])) ?>" <?= query('category') === $navCategory['slug'] ? 'aria-current="page"' : '' ?>><?= e($navCategory['name']) ?></a>
        <?php endforeach; ?>
        <a class="mobile-account" href="<?= e(url('login')) ?>"><?= icon('user') ?>Tài khoản của tôi</a>
    </nav>
</header>
