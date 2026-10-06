<?php

class Database
{
    private $host = "localhost";
    private $db_name = "QuanLyBanSach";
    private $username = "root";
    private $password = "";

    public $conn;

    // Kết nối CSDL
    public function getConnection()
    {
        $this->conn = null;

        try {
            // Kết nối MySQL Server
            $pdo = new PDO(
                "mysql:host=" . $this->host . ";charset=utf8mb4",
                $this->username,
                $this->password
            );

            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Tự tạo database nếu chưa tồn tại
            $pdo->exec(
                "CREATE DATABASE IF NOT EXISTS `" . $this->db_name . "`
                CHARACTER SET utf8mb4
                COLLATE utf8mb4_unicode_ci"
            );

            // Kết nối vào database
            $this->conn = new PDO(
                "mysql:host=" . $this->host .
                ";dbname=" . $this->db_name .
                ";charset=utf8mb4",
                $this->username,
                $this->password
            );

            $this->conn->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );

            $this->conn->setAttribute(
                PDO::ATTR_DEFAULT_FETCH_MODE,
                PDO::FETCH_ASSOC
            );

            // Tạo các bảng
            $this->initializeAllTables();

        } catch (PDOException $e) {
            die("Lỗi kết nối CSDL: " . $e->getMessage());
        }

        return $this->conn;
    }


    // =====================================================
    // TẠO CÁC BẢNG
    // =====================================================

    private function initializeAllTables()
    {
        $queries = [

            // =============================================
            // 1. VAI TRÒ
            // =============================================
            "CREATE TABLE IF NOT EXISTS VaiTro (
                maVT INT AUTO_INCREMENT PRIMARY KEY,
                tenVT VARCHAR(50) NOT NULL UNIQUE,
                moTa VARCHAR(255)
            ) ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci",


            // =============================================
            // 2. NGƯỜI DÙNG
            // =============================================
            "CREATE TABLE IF NOT EXISTS NguoiDung (
                maND INT AUTO_INCREMENT PRIMARY KEY,
                tenND VARCHAR(100) NOT NULL,
                matKhau VARCHAR(255) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                SDT VARCHAR(15),
                diaChi VARCHAR(255),
                maVT INT NOT NULL,

                CONSTRAINT FK_NguoiDung_VaiTro
                    FOREIGN KEY (maVT)
                    REFERENCES VaiTro(maVT)
                    ON UPDATE CASCADE
                    ON DELETE RESTRICT

            ) ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci",


            // =============================================
            // 3. THỂ LOẠI
            // =============================================
            "CREATE TABLE IF NOT EXISTS TheLoai (
                maTL INT AUTO_INCREMENT PRIMARY KEY,
                tenTL VARCHAR(100) NOT NULL UNIQUE
            ) ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci",


            // =============================================
            // 4. TÁC GIẢ
            // =============================================
            "CREATE TABLE IF NOT EXISTS TacGia (
                maTG INT AUTO_INCREMENT PRIMARY KEY,
                tenTG VARCHAR(100) NOT NULL
            ) ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci",


            // =============================================
            // 5. NHÀ XUẤT BẢN
            // =============================================
            "CREATE TABLE IF NOT EXISTS NhaXuatBan (
                maNXB INT AUTO_INCREMENT PRIMARY KEY,
                tenNXB VARCHAR(150) NOT NULL,
                diaChi VARCHAR(255),
                SDT VARCHAR(15)
            ) ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci",


            // =============================================
            // 6. SÁCH
            // =============================================
            "CREATE TABLE IF NOT EXISTS Sach (
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
                    REFERENCES TheLoai(maTL)
                    ON UPDATE CASCADE
                    ON DELETE RESTRICT,

                CONSTRAINT FK_Sach_TacGia
                    FOREIGN KEY (maTG)
                    REFERENCES TacGia(maTG)
                    ON UPDATE CASCADE
                    ON DELETE RESTRICT,

                CONSTRAINT FK_Sach_NhaXuatBan
                    FOREIGN KEY (maNXB)
                    REFERENCES NhaXuatBan(maNXB)
                    ON UPDATE CASCADE
                    ON DELETE RESTRICT

            ) ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci",


            // =============================================
            // 7. GIỎ HÀNG
            // =============================================
            "CREATE TABLE IF NOT EXISTS GioHang (
                maGH INT AUTO_INCREMENT PRIMARY KEY,
                maND INT NOT NULL UNIQUE,

                CONSTRAINT FK_GioHang_NguoiDung
                    FOREIGN KEY (maND)
                    REFERENCES NguoiDung(maND)
                    ON UPDATE CASCADE
                    ON DELETE CASCADE

            ) ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci",


            // =============================================
            // 8. CHI TIẾT GIỎ HÀNG
            // =============================================
            "CREATE TABLE IF NOT EXISTS ChiTietGioHang (
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
                    REFERENCES Sach(maSach)
                    ON UPDATE CASCADE
                    ON DELETE CASCADE,

                CONSTRAINT FK_ChiTietGioHang_GioHang
                    FOREIGN KEY (maGH)
                    REFERENCES GioHang(maGH)
                    ON UPDATE CASCADE
                    ON DELETE CASCADE

            ) ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci",


            // =============================================
            // 9. ĐƠN HÀNG
            // =============================================
            "CREATE TABLE IF NOT EXISTS DonHang (
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
                    REFERENCES NguoiDung(maND)
                    ON UPDATE CASCADE
                    ON DELETE RESTRICT

            ) ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci",


            // =============================================
            // 10. CHI TIẾT ĐƠN HÀNG
            // =============================================
            "CREATE TABLE IF NOT EXISTS ChiTietDonHang (
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
                    REFERENCES DonHang(maDH)
                    ON UPDATE CASCADE
                    ON DELETE CASCADE,

                CONSTRAINT FK_ChiTietDonHang_Sach
                    FOREIGN KEY (maSach)
                    REFERENCES Sach(maSach)
                    ON UPDATE CASCADE
                    ON DELETE RESTRICT

            ) ENGINE=InnoDB
            DEFAULT CHARSET=utf8mb4
            COLLATE=utf8mb4_unicode_ci"
        ];


        // Chạy từng câu SQL
        foreach ($queries as $query) {
            $this->conn->exec($query);
        }
    }
}
?>