<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require_once dirname(__DIR__) . '/model/database.php';

try {
    $db = \Database::connect();
    // giohang và chitietgiohang đã có trong database/schema.sql. Chỉ thêm bảng
    // đánh dấu từng lần gộp giỏ khách để POST retry không cộng số lượng hai lần.
    $db->exec('CREATE TABLE IF NOT EXISTS giohang_langop (
        maND INT NOT NULL,
        maLanGop CHAR(32) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
        ngayGop DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (maND, maLanGop),
        CONSTRAINT FK_GioHangLanGop_NguoiDung
            FOREIGN KEY (maND) REFERENCES nguoidung(maND) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
    echo "Cart sync migration ready.\n";
} catch (\Throwable $error) {
    fwrite(STDERR, 'Cart migration failed: ' . $error->getMessage() . PHP_EOL);
    exit(1);
}
