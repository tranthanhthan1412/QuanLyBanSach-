<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

// Repeatable additive upgrade. Old order dates and stock movements remain unknown.
function migrate_admin(PDO $db): void
{
    $columns = [
        'sach' => ['an' => 'TINYINT(1) NOT NULL DEFAULT 0', 'phienBan' => 'INT NOT NULL DEFAULT 1'],
        'nguoidung' => ['biKhoa' => 'TINYINT(1) NOT NULL DEFAULT 0'],
        'donhang' => ['daTruKho' => 'TINYINT(1) NOT NULL DEFAULT 0', 'ngayTao' => 'DATETIME NULL DEFAULT NULL'],
    ];
    foreach ($columns as $table => $definitions) {
        foreach ($definitions as $name => $definition) {
            if (!$db->query("SHOW COLUMNS FROM `$table` LIKE '$name'")->fetch()) {
                $db->exec("ALTER TABLE `$table` ADD COLUMN `$name` $definition");
            }
        }
    }
    $db->exec('CREATE TABLE IF NOT EXISTS nhatkyquantri (
        id BIGINT AUTO_INCREMENT PRIMARY KEY, maND INT NULL,
        hanhDong VARCHAR(100) NOT NULL, doiTuong VARCHAR(100) NOT NULL, chiTiet TEXT NOT NULL,
        ngayTao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT FK_NhatKy_NguoiDung FOREIGN KEY (maND) REFERENCES nguoidung(maND) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
}
