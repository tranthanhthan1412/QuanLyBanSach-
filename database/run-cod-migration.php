<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once dirname(__DIR__) . '/model/database.php';
require_once __DIR__ . '/cod-migration.php';

try {
    $config = Database::settings();
    $databaseName = $config['name'];
    $confirmedName = $argv[1] ?? '';

    if ($confirmedName === '' || $confirmedName !== $databaseName) {
        fwrite(STDERR, "Database hien tai: {$databaseName}\n");
        fwrite(STDERR, "Can xac nhan dung ten database khi chay.\n");
        exit(1);
    }

    $db = Database::connect();

    migrate_cod($db);

    echo "Cap nhat COD thanh cong!\n";
    echo "Database: {$databaseName}\n";

} catch (Throwable $error) {
    fwrite(STDERR, "Loi: " . $error->getMessage() . "\n");
    exit(1);
}
