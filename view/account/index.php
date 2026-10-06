<div class="container page-content">

    <?php
    $user = $_SESSION['user'] ?? null;

    $breadcrumbs = [
        ['label' => 'Tài khoản']
    ];

    require ROOT_PATH . '/view/partials/breadcrumb.php';
    ?>


    <div class="page-heading">

        <h1>Tài khoản của bạn</h1>

        <p>
            Xin chào,
            <strong><?= e($user['name'] ?? 'Khách') ?></strong>!
        </p>

    </div>


    <?php require ROOT_PATH . '/view/partials/notice.php'; ?>


    <div class="account-grid">

        <?php require ROOT_PATH . '/view/partials/account-nav.php'; ?>


        <section class="panel account-main">

            <h2>Thông tin cá nhân</h2>

            <p class="muted">
                Thông tin tài khoản của bạn.
            </p>


            <dl class="profile-details">

                <!-- HỌ VÀ TÊN -->
                <div>

                    <dt>Họ và tên</dt>

                    <dd>
                        <?= e($user['name'] ?? 'Chưa cập nhật') ?>
                    </dd>

                </div>


                <!-- EMAIL -->
                <div>

                    <dt>Email</dt>

                    <dd>
                        <?= e($user['email'] ?? 'Chưa cập nhật') ?>
                    </dd>

                </div>


                <!-- VAI TRÒ -->
                <div>

                    <dt>Vai trò</dt>

                    <dd>
                        <?= e($user['role'] ?? 'User') ?>
                    </dd>

                </div>


            </dl>


            <div class="account-actions">

                <a class="button button-outline" href="<?= e(url('orders')) ?>">
                    Xem đơn hàng
                    <?= icon('arrow') ?>
                </a>


                <a class="button" href="<?= e(url('logout')) ?>">
                    Đăng xuất
                </a>

            </div>

        </section>

    </div>

</div>