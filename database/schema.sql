-- Initial schema. Applied explicitly by database/setup.php.
CREATE TABLE IF NOT EXISTS vaitro (
                maVT INT AUTO_INCREMENT PRIMARY KEY,
                tenVT VARCHAR(50) NOT NULL UNIQUE,
                moTa VARCHAR(255)
            ) ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS nguoidung (
                maND INT AUTO_INCREMENT PRIMARY KEY,
                tenND VARCHAR(100) NOT NULL,
                matKhau VARCHAR(255) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                SDT VARCHAR(15),
                diaChi VARCHAR(255),
                maVT INT NOT NULL,

                CONSTRAINT FK_NguoiDung_VaiTro
                    FOREIGN KEY (maVT)
                    REFERENCES vaitro(maVT)
                    ON UPDATE CASCADE
                    ON DELETE RESTRICT

            ) ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS theloai (
                maTL INT AUTO_INCREMENT PRIMARY KEY,
                tenTL VARCHAR(100) NOT NULL UNIQUE
            ) ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tacgia (
                maTG INT AUTO_INCREMENT PRIMARY KEY,
                tenTG VARCHAR(100) NOT NULL
            ) ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS nhaxuatban (
                maNXB INT AUTO_INCREMENT PRIMARY KEY,
                tenNXB VARCHAR(150) NOT NULL,
                diaChi VARCHAR(255),
                SDT VARCHAR(15)
            ) ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS sach (
                maSach INT AUTO_INCREMENT PRIMARY KEY,
                tenSach VARCHAR(255) NOT NULL,
                moTa TEXT,
                giaTien DECIMAL(15,2) NOT NULL DEFAULT 0,
                tonKho INT NOT NULL DEFAULT 0,

                maTL INT NOT NULL,
                maTG INT NOT NULL,
                maNXB INT NOT NULL,

                CONSTRAINT CK_Sach_Gia
                    CHECK (giaTien >= 0),

                CONSTRAINT CK_Sach_TonKho
                    CHECK (tonKho >= 0),

                CONSTRAINT FK_Sach_TheLoai
                    FOREIGN KEY (maTL)
                    REFERENCES theloai(maTL)
                    ON UPDATE CASCADE
                    ON DELETE RESTRICT,

                CONSTRAINT FK_Sach_TacGia
                    FOREIGN KEY (maTG)
                    REFERENCES tacgia(maTG)
                    ON UPDATE CASCADE
                    ON DELETE RESTRICT,

                CONSTRAINT FK_Sach_NhaXuatBan
                    FOREIGN KEY (maNXB)
                    REFERENCES nhaxuatban(maNXB)
                    ON UPDATE CASCADE
                    ON DELETE RESTRICT

            ) ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS giohang (
                maGH INT AUTO_INCREMENT PRIMARY KEY,
                maND INT NOT NULL UNIQUE,

                CONSTRAINT FK_GioHang_NguoiDung
                    FOREIGN KEY (maND)
                    REFERENCES nguoidung(maND)
                    ON UPDATE CASCADE
                    ON DELETE CASCADE

            ) ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chitietgiohang (
                maSach INT NOT NULL,
                maGH INT NOT NULL,
                soLuong INT NOT NULL DEFAULT 1,
                thanhTien DECIMAL(15,2) NOT NULL DEFAULT 0,

                PRIMARY KEY (maSach, maGH),

                CONSTRAINT CK_ChiTietGioHang_SoLuong
                    CHECK (soLuong > 0),

                CONSTRAINT CK_ChiTietGioHang_ThanhTien
                    CHECK (thanhTien >= 0),

                CONSTRAINT FK_ChiTietGioHang_Sach
                    FOREIGN KEY (maSach)
                    REFERENCES sach(maSach)
                    ON UPDATE CASCADE
                    ON DELETE CASCADE,

                CONSTRAINT FK_ChiTietGioHang_GioHang
                    FOREIGN KEY (maGH)
                    REFERENCES giohang(maGH)
                    ON UPDATE CASCADE
                    ON DELETE CASCADE

            ) ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS donhang (
                maDH INT AUTO_INCREMENT PRIMARY KEY,
                tenDH VARCHAR(100),
                tongSL INT NOT NULL DEFAULT 0,
                trangThai VARCHAR(50) NOT NULL DEFAULT 'ChoXacNhan',
                ghiChu VARCHAR(500),
                maND INT NOT NULL,

                CONSTRAINT CK_DonHang_TongSL
                    CHECK (tongSL >= 0),

                CONSTRAINT FK_DonHang_NguoiDung
                    FOREIGN KEY (maND)
                    REFERENCES nguoidung(maND)
                    ON UPDATE CASCADE
                    ON DELETE RESTRICT

            ) ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS chitietdonhang (
                maDH INT NOT NULL,
                maSach INT NOT NULL,
                soLuong INT NOT NULL,
                tongTien DECIMAL(15,2) NOT NULL DEFAULT 0,

                PRIMARY KEY (maDH, maSach),

                CONSTRAINT CK_ChiTietDonHang_SoLuong
                    CHECK (soLuong > 0),

                CONSTRAINT CK_ChiTietDonHang_TongTien
                    CHECK (tongTien >= 0),

                CONSTRAINT FK_ChiTietDonHang_DonHang
                    FOREIGN KEY (maDH)
                    REFERENCES donhang(maDH)
                    ON UPDATE CASCADE
                    ON DELETE CASCADE,

                CONSTRAINT FK_ChiTietDonHang_Sach
                    FOREIGN KEY (maSach)
                    REFERENCES sach(maSach)
                    ON UPDATE CASCADE
                    ON DELETE RESTRICT

            ) ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci;
