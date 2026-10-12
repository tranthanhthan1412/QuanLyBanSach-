<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

function migrate_checkout(PDO $db): void
{
    $columns = [
        'donhang' => [
            'tenNguoiNhan' => 'VARCHAR(80) NULL',
            'emailNhan' => 'VARCHAR(100) NULL',
            'sdtNhan' => 'VARCHAR(15) NULL',
            'diaChiNhan' => 'VARCHAR(400) NULL',
            'phiVanChuyen' => 'DECIMAL(15,2) NOT NULL DEFAULT 0',
            'checkoutToken' => 'CHAR(64) NULL',
            'trangThaiEmail' => "VARCHAR(20) NOT NULL DEFAULT 'disabled'",
        ],
        'chitietdonhang' => ['tenSachLucMua' => 'VARCHAR(255) NULL'],
    ];
    foreach ($columns as $table => $definitions) {
        foreach ($definitions as $name => $definition) {
            if (!$db->query("SHOW COLUMNS FROM `$table` LIKE '$name'")->fetch()) {
                $db->exec("ALTER TABLE `$table` ADD COLUMN `$name` $definition");
            }
        }
    }
    if (!$db->query("SHOW INDEX FROM donhang WHERE Key_name = 'UQ_CheckoutToken'")->fetch()) {
        $db->exec('ALTER TABLE donhang ADD UNIQUE KEY UQ_CheckoutToken (checkoutToken)');
    }
}
