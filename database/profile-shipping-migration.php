<?php
declare(strict_types=1);

// Run once on each local database, or rerun safely after pulling updates:
// php database/profile-shipping-migration.php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

require_once dirname(__DIR__) . '/model/database.php';

try {
    $db = Database::connect();
    $columns = [
        'tinhThanhPho' => 'VARCHAR(80) NULL DEFAULT NULL',
        'phuongXa' => 'VARCHAR(80) NULL DEFAULT NULL',
    ];
    foreach ($columns as $column => $definition) {
        $statement = $db->prepare(
            'SELECT COUNT(*) FROM information_schema.COLUMNS '
            . 'WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
        );
        $statement->execute(['nguoidung', $column]);
        if ((int) $statement->fetchColumn() === 0) {
            $db->exec("ALTER TABLE `nguoidung` ADD COLUMN `$column` $definition");
        }
    }
    echo "Address columns ready.\n";
} catch (Throwable $error) {
    fwrite(STDERR, 'Address migration failed: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
