# BookStore – Website bán sách

Đồ án cuối kỳ: website bán sách viết bằng **PHP thuần (MVC)**, **MySQL** và **JavaScript**. Không dùng framework, Composer hay Node.js.

## Chức năng

**Khách hàng**
- Xem sách, tìm kiếm, lọc theo thể loại, sắp xếp, phân trang.
- Giỏ hàng (lưu trên trình duyệt), đặt hàng thanh toán khi nhận hàng (COD).
- Đăng ký, đăng nhập, sửa thông tin cá nhân, đổi mật khẩu.
- Xem đơn hàng, trạng thái thu tiền COD, gửi yêu cầu đổi trả sau khi đơn đã giao.
- Nhận email thông tin đơn hàng (PHPMailer).

**Quản trị viên**
- Quản lý sách (thêm, sửa, ẩn, xóa, ảnh bìa), thể loại, tác giả, nhà xuất bản.
- Xử lý đơn hàng: xác nhận (trừ kho), giao hàng, hủy (hoàn kho).
- Đối soát tiền COD, duyệt đổi trả và hoàn tiền (có thể nhập lại kho).
- Khóa tài khoản, phân quyền, xem nhật ký thao tác, thống kê tổng quan.

## Cài đặt (XAMPP)

1. Đặt dự án tại `C:\xampp\htdocs\QuanLyBanSach`.
2. Bật **Apache** và **MySQL**.
3. Tạo database và dữ liệu mẫu:

   ```powershell
   & C:\xampp\php\php.exe database\setup.php
   ```

4. Mở http://localhost/QuanLyBanSach/ và đăng ký tài khoản.
5. Cấp quyền admin cho tài khoản vừa đăng ký:

   ```powershell
   & C:\xampp\php\php.exe database\set-role.php email@example.com Admin
   ```

Cấu hình database nằm ở `config/database.php` (mặc định: `127.0.0.1`, database `QuanLyBanSach`, user `root`, mật khẩu rỗng). Phí giao hàng và số sách mỗi trang nằm ở `config/app.php`.

## Gửi email đơn hàng

1. Sao chép `config/mail.local.example.php` thành `config/mail.local.php`.
2. Điền tài khoản SMTP (với Gmail dùng **Mật khẩu ứng dụng**).

Khi chưa cấu hình, đơn vẫn được lưu bình thường, chỉ không gửi email.

## Cấu trúc

```text
index.php       điểm vào của website
config/         cấu hình, routes, autoload
controller/     nhận request, gọi model, chọn view
model/          truy vấn MySQL
view/           giao diện, layout, partials
libs/           Router, Controller, helper, PHPMailer
public/assets/  CSS, JavaScript, hình ảnh
database/       schema.sql, seed.php, setup.php, migration, set-role.php
```

Luồng xử lý: `index.php` → `config/routes.php` → `controller/` → `model/` → `view/`.

## Nguồn ảnh

Ảnh minh họa lấy từ Unsplash, lưu trong `public/assets/images/`.
