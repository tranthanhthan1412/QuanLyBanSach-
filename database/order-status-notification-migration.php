<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require dirname(__DIR__) . '/model/database.php';

try {
    $db = (new \Database())->getConnection();
    // Repeatable, additive migration. Does not rewrite old orders.
    $db->exec('CREATE TABLE IF NOT EXISTS thongbaotrangthaidon (
        maTB BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
        maDH INT NOT NULL,
        trangThaiCu VARCHAR(50) NOT NULL,
        trangThaiMoi VARCHAR(50) NOT NULL,
        lyDo VARCHAR(500) NOT NULL DEFAULT \'\',
        emailNhan VARCHAR(100) NOT NULL,
        tenNguoiNhan VARCHAR(100) NOT NULL,
        trangThaiGui VARCHAR(12) NOT NULL DEFAULT \'pending\',
        soLanGui SMALLINT UNSIGNED NOT NULL DEFAULT 0,
        ngayThu DATETIME NULL DEFAULT NULL,
        ngayGui DATETIME NULL DEFAULT NULL,
        ngayTao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_TB_DonHang (maDH, maTB),
        CONSTRAINT FK_TB_DonHang FOREIGN KEY (maDH)
            REFERENCES donhang(maDH) ON DELETE RESTRICT ON UPDATE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    echo "Order status notification table ready.\n";
} catch (\Throwable $error) {
    fwrite(STDERR, 'Migration failed: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
