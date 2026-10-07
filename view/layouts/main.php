<!doctype html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="BookStore — khám phá sách và truyện tranh. Website đồ án Công nghệ phần mềm.">
    <meta name="theme-color" content="#060514">
    <title><?= e($title ?? 'Trang chủ') ?> | BookStore</title>
    <link rel="icon" href="<?= e(asset('images/favicon.svg')) ?>" type="image/svg+xml">
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
</head>
<body>
    <a class="skip-link" href="#main">Bỏ qua đến nội dung chính</a>
    <?php require ROOT_PATH . '/view/partials/header.php'; ?>
    <main id="main">
        <?php require ROOT_PATH . '/view/' . $view . '.php'; ?>
    </main>
    <?php require ROOT_PATH . '/view/partials/footer.php'; ?>
    <div class="toast-region" id="toast-region" role="status" aria-live="polite" aria-atomic="true"></div>
    <noscript><div class="noscript-notice">Vui lòng bật JavaScript để dùng giỏ hàng, đặt hàng thử và form nhận bản tin. Bạn vẫn có thể tìm kiếm và xem sách.</div></noscript>
    <script id="app-data" type="application/json"><?= json_encode($clientConfig, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?></script>
    <script type="module" src="<?= e(asset('js/app.js')) ?>"></script>
</body>
</html>
