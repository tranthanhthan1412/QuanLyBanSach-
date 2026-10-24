<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

function migrate_cod_reconciliation(PDO $db): void
{
    $columns = [
        'donhang' => [
            'trangThaiCOD' => "VARCHAR(30) NOT NULL DEFAULT 'ChuaThu'",
            'ngayDoiSoatCOD' => "DATETIME NULL DEFAULT NULL",
            'ghiChuCOD' => "VARCHAR(500) NULL DEFAULT NULL",
            'nguoiDoiSoatCOD' => "INT NULL DEFAULT NULL",
        ],
    ];

    foreach ($columns as $table => $definitions) {
        foreach ($definitions as $name => $definition) {
            if (!$db->query("SHOW COLUMNS FROM `$table` LIKE '$name'")->fetch()) {
                $db->exec("ALTER TABLE `$table` ADD COLUMN `$name` $definition");
            }
        }
    }

    $db->exec('CREATE TABLE IF NOT EXISTS yeucaudoitra (
        maYC INT AUTO_INCREMENT PRIMARY KEY,
        maDH INT NOT NULL,
        maND INT NOT NULL,
        loaiYeuCau VARCHAR(30) NOT NULL DEFAULT \'HoanTien\',
        lyDo VARCHAR(100) NOT NULL,
        chiTiet TEXT NULL,
        soTienHoan DECIMAL(15,2) NOT NULL DEFAULT 0,
        trangThai VARCHAR(30) NOT NULL DEFAULT \'ChoXuLy\',
        phanHoiAdmin TEXT NULL,
        hoanKho TINYINT(1) NOT NULL DEFAULT 0,
        ngayTao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        ngayXuLy DATETIME NULL DEFAULT NULL,
        INDEX idx_yeucaudoitra_dh (maDH),
        INDEX idx_yeucaudoitra_nd (maND),
        CONSTRAINT FK_YeuCauDoiTra_DonHang
            FOREIGN KEY (maDH)
            REFERENCES donhang(maDH)
            ON DELETE CASCADE,
        CONSTRAINT FK_YeuCauDoiTra_NguoiDung
            FOREIGN KEY (maND)
            REFERENCES nguoidung(maND)
            ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
}
