<div class="container auth-page">

    <div class="auth-card panel">

        <a class="brand" href="<?= e(url()) ?>">
            <?= icon('book') ?>
            <span>BookStore</span>
        </a>


        <h1>
            <?= $isRegister
                ? 'Tạo tài khoản'
                : 'Chào mừng bạn trở lại'
            ?>
        </h1>


        <p class="muted">

            <?= $isRegister
                ? 'Bắt đầu hành trình khám phá những cuốn sách hay.'
                : 'Một thế giới sách đang chờ bạn khám phá.'
            ?>

        </p>


        <!-- ERROR -->

        <?php if (!empty($error)): ?>

        <div class="notice notice-error">
            <?= e($error) ?>
        </div>

        <?php endif; ?>


        <?php if (!empty($success)): ?><div class="notice"><?= e($success) ?></div><?php endif; ?>
        <!-- FORM -->

        <form method="post" action="<?= e(
                url(
                    $isRegister
                        ? 'register'
                        : 'login'
                )
            ) ?>" autocomplete="on">
            <?= csrf_field() ?>


            <!-- NAME -->

            <?php if ($isRegister): ?>

            <div class="field">

                <label for="fullname">
                    Họ và tên
                </label>

                <input id="fullname" name="name" type="text" required minlength="2" maxlength="80"
                    placeholder="Nhập họ và tên" value="<?= e(
                            post_string('name')
                        ) ?>">

            </div>

            <?php endif; ?>


            <!-- EMAIL -->

            <div class="field">

                <label for="auth-email">
                    Email
                </label>

                <input id="auth-email" name="email" type="email" required maxlength="100" autocomplete="email" placeholder="ban@example.com" value="<?= e(
                        post_string('email')
                    ) ?>">

            </div>


            <!-- PASSWORD -->

            <div class="field">

                <label for="password">
                    Mật khẩu
                </label>


                <div class="password-wrap">

                    <input id="password" name="password" type="password" required minlength="6" maxlength="72"
                        placeholder="Nhập mật khẩu" autocomplete="<?= $isRegister ? 'new-password' : 'current-password' ?>">


                    <button type="button" data-toggle-password="password" class="icon-button" aria-label="Hiện mật khẩu"
                        aria-pressed="false">
                        <?= icon('eye') ?>
                    </button>

                </div>

            </div>


            <!-- CONFIRM PASSWORD -->

            <?php if ($isRegister): ?>

            <div class="field">

                <label for="confirm-password">
                    Nhập lại mật khẩu
                </label>


                <div class="password-wrap">

                    <input id="confirm-password" name="confirm" type="password" required minlength="6" maxlength="72"
                        placeholder="Nhập lại mật khẩu" autocomplete="new-password">


                    <button type="button" data-toggle-password="confirm-password" class="icon-button"
                        aria-label="Hiện mật khẩu nhập lại" aria-pressed="false">
                        <?= icon('eye') ?>
                    </button>

                </div>

            </div>

            <?php endif; ?>


            <!-- SUBMIT -->

            <button class="button full-width" type="submit">

                <?= $isRegister
                    ? 'Đăng ký'
                    : 'Đăng nhập'
                ?>

                <?= icon('arrow') ?>

            </button>


        </form>


        <!-- SWITCH -->

        <p class="auth-switch">

            <?= $isRegister
                ? 'Đã có tài khoản?'
                : 'Chưa có tài khoản?'
            ?>


            <a class="text-link" href="<?= e(
                    url(
                        $isRegister
                            ? 'login'
                            : 'register'
                    )
                ) ?>">

                <?= $isRegister
                    ? 'Đăng nhập'
                    : 'Đăng ký'
                ?>

            </a>

        </p>




    </div>

</div>