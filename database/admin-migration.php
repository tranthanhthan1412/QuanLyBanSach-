<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

// Repeatable additive upgrade. Old order dates and stock movements remain unknown.

function migrate_admin(PDO $db): void
{
    $columns = [
        'sach' => [
            'an' => 'TINYINT(1) NOT NULL DEFAULT 0',
            'phienBan' => 'INT NOT NULL DEFAULT 1'
        ],
        'nguoidung' => [
            'biKhoa' => 'TINYINT(1) NOT NULL DEFAULT 0'
        ],
        'donhang' => [
            'daTruKho' => 'TINYINT(1) NOT NULL DEFAULT 0',
            'ngayTao' => 'DATETIME NULL DEFAULT NULL'
        ],
    ];

    foreach ($columns as $table => $definitions) {
        foreach ($definitions as $name => $definition) {
            if (!$db->query("SHOW COLUMNS FROM `$table` LIKE '$name'")->fetch()) {
                $db->exec("ALTER TABLE `$table` ADD COLUMN `$name` $definition");
            }
        }
    }

    $db->exec('CREATE TABLE IF NOT EXISTS nhatkyquantri (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        maND INT NULL,
        hanhDong VARCHAR(100) NOT NULL,
        doiTuong VARCHAR(100) NOT NULL,
        chiTiet TEXT NOT NULL,
        ngayTao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT FK_NhatKy_NguoiDung
            FOREIGN KEY (maND)
            REFERENCES nguoidung(maND)
            ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

    $uniquePublisher = $db->query("
        SHOW INDEX
        FROM nhaxuatban
        WHERE Key_name = 'uk_nhaxuatban_ten'
    ")->fetch();

    if (!$uniquePublisher) {
        $db->exec("
            ALTER TABLE nhaxuatban
            ADD CONSTRAINT uk_nhaxuatban_ten
            UNIQUE (tenNXB)
        ");
    }

    // Ghi nhận phiếu nhập đã xác nhận. Không suy diễn lịch sử từ tồn kho cũ.
    // Dùng requestToken UNIQUE để chống tạo phiếu 2 lần khi gửi lại yêu cầu.
    $db->exec('CREATE TABLE IF NOT EXISTS phieunhap (
        maPN BIGINT AUTO_INCREMENT PRIMARY KEY,
        maND INT NOT NULL,
        ngayNhap DATE NOT NULL,
        ngayTao DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        nhaCungCap VARCHAR(150) NULL,
        ghiChu VARCHAR(500) NULL,
        tongTienNhap DECIMAL(18,2) NOT NULL DEFAULT 0,
        trangThai VARCHAR(20) NOT NULL DEFAULT \'DaNhap\',
        requestToken CHAR(64) NOT NULL UNIQUE,
        INDEX idx_phieunhap_ngay (ngayNhap, maPN),
        CONSTRAINT FK_PhieuNhap_NguoiDung FOREIGN KEY (maND)
            REFERENCES nguoidung(maND) ON UPDATE CASCADE ON DELETE RESTRICT,
        CONSTRAINT CK_PhieuNhap_TongTien CHECK (tongTienNhap >= 0)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');

    $db->exec('CREATE TABLE IF NOT EXISTS chitietphieunhap (
        maPN BIGINT NOT NULL,
        maSach INT NOT NULL,
        tenSachLucNhap VARCHAR(255) NOT NULL,
        soLuong INT NOT NULL,
        donGiaNhap DECIMAL(12,2) NOT NULL,
        thanhTien DECIMAL(18,2) NOT NULL,
        PRIMARY KEY (maPN, maSach),
        CONSTRAINT FK_ChiTietPhieuNhap_PhieuNhap FOREIGN KEY (maPN)
            REFERENCES phieunhap(maPN) ON UPDATE CASCADE ON DELETE RESTRICT,
        CONSTRAINT FK_ChiTietPhieuNhap_Sach FOREIGN KEY (maSach)
            REFERENCES sach(maSach) ON UPDATE CASCADE ON DELETE RESTRICT,
        CONSTRAINT CK_ChiTietPhieuNhap_SoLuong CHECK (soLuong > 0),
        CONSTRAINT CK_ChiTietPhieuNhap_Gia CHECK (donGiaNhap >= 0),
        CONSTRAINT CK_ChiTietPhieuNhap_ThanhTien CHECK (thanhTien >= 0)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci');
}