<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

/** Add only columns required for persisted COD orders; leave existing records intact. */
function migrate_cod(PDO $db): void
{
    $columns = [
        'tenNguoiNhan' => 'VARCHAR(80) NULL',
        'SDTNguoiNhan' => 'VARCHAR(20) NULL',
        'emailNguoiNhan' => 'VARCHAR(100) NULL',
        'diaChiGiao' => 'VARCHAR(400) NULL',
        'phuongThucTT' => "VARCHAR(20) NOT NULL DEFAULT 'COD'",
        'phiVanChuyen' => 'DECIMAL(15,2) NOT NULL DEFAULT 0',
        'tongThanhToan' => 'DECIMAL(15,2) NOT NULL DEFAULT 0',
    ];
    foreach ($columns as $name => $definition) {
        if (!$db->query("SHOW COLUMNS FROM donhang LIKE '$name'")->fetch()) {
            $db->exec("ALTER TABLE donhang ADD COLUMN `$name` $definition");
        }
    }
}
