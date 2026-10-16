<?php
declare(strict_types=1);

namespace App\Models;

use DomainException;
use PDO;

final class AccountModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = (new \Database())->getConnection();
    }

    public function profile(int $userId): array
    {
        $stmt = $this->db->prepare("SELECT tenND AS name, COALESCE(SDT, '') AS phone, COALESCE(diaChi, '') AS address, COALESCE(tinhThanhPho, '') AS province, COALESCE(phuongXa, '') AS ward FROM nguoidung WHERE maND = ?");
        $stmt->execute([$userId]);
        $profile = $stmt->fetch();
        if (!$profile) {
            throw new DomainException('Không tìm thấy tài khoản.');
        }
        return $profile;
    }

    public function updateProfile(int $userId, array $profile): void
    {
        if (mb_strlen($profile['name']) < 2 || mb_strlen($profile['name']) > 80) {
            throw new DomainException('Họ tên cần từ 2 đến 80 ký tự.');
        }
        $phone = preg_replace('/[\s.-]/', '', $profile['phone']);
        if ($phone !== '' && !preg_match('/^(0|\+84)[0-9]{9}$/D', $phone)) {
            throw new DomainException('Số điện thoại không hợp lệ. Ví dụ: 0901234567 hoặc +84901234567.');
        }
        if (mb_strlen($profile['address']) > 255) {
            throw new DomainException('Địa chỉ không được dài quá 255 ký tự.');
        }
        $province = trim($profile['province'] ?? '');
        $ward = trim($profile['ward'] ?? '');
        foreach (['Tỉnh / Thành phố' => $province, 'Phường / Xã' => $ward] as $label => $value) {
            if ($value !== '' && (mb_strlen($value) < 2 || mb_strlen($value) > 80)) {
                throw new DomainException($label . ' cần từ 2 đến 80 ký tự.');
            }
        }
        // Only these fields can be changed; identity comes from the authenticated session.
        $stmt = $this->db->prepare('UPDATE nguoidung SET tenND = ?, SDT = ?, diaChi = ?, tinhThanhPho = ?, phuongXa = ? WHERE maND = ?');
        $stmt->execute([
            $profile['name'],
            $phone !== '' ? $phone : null,
            $profile['address'] !== '' ? $profile['address'] : null,
            $province !== '' ? $province : null,
            $ward !== '' ? $ward : null,
            $userId,
        ]);
    }

    public function changePassword(int $userId, string $current, string $password, string $confirm): void
    {
        if ($current === '' || strlen($current) > 72) {
            throw new DomainException('Vui lòng nhập mật khẩu hiện tại hợp lệ.');
        }
        if (strlen($password) < 8 || strlen($password) > 72 || str_contains($password, "\0")) {
            throw new DomainException('Mật khẩu mới cần từ 8 đến 72 byte và không chứa ký tự không hợp lệ. Ký tự có dấu có thể chiếm nhiều byte.');
        }
        if ($password !== $confirm) {
            throw new DomainException('Mật khẩu nhập lại không khớp.');
        }
        $stmt = $this->db->prepare('SELECT matKhau FROM nguoidung WHERE maND = ?');
        $stmt->execute([$userId]);
        $hash = $stmt->fetchColumn();
        if (!$hash || !password_verify($current, $hash)) {
            throw new DomainException('Mật khẩu hiện tại không chính xác.');
        }
        if (password_verify($password, $hash)) {
            throw new DomainException('Mật khẩu mới phải khác mật khẩu hiện tại.');
        }
        // Compare-and-swap prevents a concurrent password change from being overwritten.
        $stmt = $this->db->prepare('UPDATE nguoidung SET matKhau = ? WHERE maND = ? AND matKhau = ?');
        $stmt->execute([password_hash($password, PASSWORD_DEFAULT), $userId, $hash]);
        if ($stmt->rowCount() !== 1) {
            throw new DomainException('Mật khẩu đã thay đổi ở phiên khác. Vui lòng đăng nhập lại.');
        }
    }
}
