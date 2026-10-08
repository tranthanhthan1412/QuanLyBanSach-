<header class="site-header">

    <div class="container header-top">

        <!-- LOGO -->
        <a class="brand" href="<?= e(url()) ?>" aria-label="BookStore — Trang chủ">
            <?= icon('book') ?>
            <span>BookStore</span>
        </a>


        <!-- SEARCH -->
        <form class="header-search" action="<?= e(base_url() . '/index.php') ?>" method="get" role="search">

            <input type="hidden" name="route" value="products">

            <div class="search-input">

                <?= icon('search') ?>

                <label class="sr-only" for="header-search">
                    Tìm sách, tác giả hoặc thể loại
                </label>

                <input id="header-search" name="q" type="search" placeholder="Tìm sách, tác giả hoặc thể loại..."
                    value="<?= e(query('q')) ?>" maxlength="100">

            </div>

            <button class="button button-small" type="submit">
                Tìm
            </button>

        </form>


        <!-- HEADER ACTIONS -->
        <div class="header-actions">


            <?php if (!empty($_SESSION['user'])): ?>

            <?php if (is_admin()): ?>
            <a class="header-action" href="<?= e(url('admin')) ?>"><?= icon('shield') ?><span>Quản trị</span></a>
            <?php endif; ?>

            <!-- ========================= -->
            <!-- ĐÃ ĐĂNG NHẬP -->
            <!-- ========================= -->

            <a class="header-action" href="<?= e(url('account')) ?>" title="Tài khoản">

                <?= icon('user') ?>

                <span>
                    <?= e($_SESSION['user']['name']) ?>
                </span>

            </a>


            <form class="logout-form" method="post" action="<?= e(url('logout')) ?>"><?= csrf_field() ?><button class="header-action text-button" type="submit">Đăng xuất</button></form>


            <?php else: ?>

            <!-- ========================= -->
            <!-- CHƯA ĐĂNG NHẬP -->
            <!-- ========================= -->

            <a class="header-action" href="<?= e(url('login')) ?>">

                <?= icon('user') ?>

                <span>Tài khoản</span>

            </a>

            <?php endif; ?>


            <!-- CART -->
            <a class="header-action cart-link" href="<?= e(url('cart')) ?>">

                <?= icon('cart') ?>

                <span>Giỏ hàng</span>

                <span class="cart-badge" data-cart-badge hidden>
                    0
                </span>

            </a>


            <!-- MENU -->
            <button class="icon-button menu-toggle" id="menu-toggle" aria-label="Mở danh mục" aria-expanded="false"
                aria-controls="category-nav">
                <?= icon('menu') ?>
            </button>

        </div>

    </div>


    <!-- CATEGORY NAV -->
    <nav class="container category-nav" id="category-nav" aria-label="Danh mục sách">


        <!-- MOBILE SEARCH -->
        <form class="mobile-search" action="<?= e(base_url() . '/index.php') ?>" method="get" role="search">

            <input type="hidden" name="route" value="products">

            <div class="search-input">

                <?= icon('search') ?>

                <label class="sr-only" for="mobile-search">
                    Tìm sách trong danh mục
                </label>

                <input id="mobile-search" name="q" type="search" placeholder="Tìm sách..." value="<?= e(query('q')) ?>"
                    maxlength="100">

            </div>

            <button class="button button-small" type="submit">
                Tìm
            </button>

        </form>


        <!-- ALL PRODUCTS -->
        <a href="<?= e(url('products')) ?>" <?= query('route') === 'products'
                && query('category') === ''
                ? 'aria-current="page"'
                : '' ?>>
            Tất cả
        </a>


        <!-- CATEGORIES -->
        <?php foreach ($categories as $navCategory): ?>

        <a href="<?= e(
                    url(
                        'products',
                        [
                            'category' => $navCategory['slug']
                        ]
                    )
                ) ?>" <?= query('category') === $navCategory['slug']
                    ? 'aria-current="page"'
                    : '' ?>>
            <?= e($navCategory['name']) ?>
        </a>

        <?php endforeach; ?>


        <!-- MOBILE ACCOUNT -->
        <?php if (is_admin()): ?>
        <a class="mobile-account" href="<?= e(url('admin')) ?>"><?= icon('shield') ?>Quản trị</a>
        <?php endif; ?>
        <?php if (!empty($_SESSION['user'])): ?>

        <a class="mobile-account" href="<?= e(url('account')) ?>">

            <?= icon('user') ?>

            <?= e($_SESSION['user']['name']) ?>

        </a>


        <form class="mobile-account logout-form" method="post" action="<?= e(url('logout')) ?>"><?= csrf_field() ?><button class="text-button" type="submit">Đăng xuất</button></form>


        <?php else: ?>

        <a class="mobile-account" href="<?= e(url('login')) ?>">

            <?= icon('user') ?>

            Tài khoản của tôi

        </a>

        <?php endif; ?>


    </nav>

</header>
