# BookStore – Website bán sách

Đồ án cuối kỳ xây dựng website bán sách bằng **PHP thuần theo mô hình MVC**, **MySQL** và **JavaScript**. Dự án không cần Composer hay Node.js; PHPMailer được đặt sẵn trong `libs/`.

## Chạy nhanh trên XAMPP

Yêu cầu PHP 8.0 trở lên, PDO MySQL, MySQL/MariaDB và JavaScript bật trên trình duyệt.

1. Đặt dự án tại `C:\xampp\htdocs\QuanLyBanSach`.
2. Mở XAMPP Control Panel, bật **Apache** và **MySQL**.
3. Mở PowerShell và chạy:

   ```powershell
   Set-Location C:\xampp\htdocs\QuanLyBanSach
   & C:\xampp\php\php.exe database\setup.php
   ```

   Lệnh tạo database `QuanLyBanSach`, tạo/cập nhật cấu trúc bảng và dữ liệu nền. Nếu các bảng sách, thể loại, tác giả và nhà xuất bản đều trống, script thêm 8 thể loại và 24 sách minh họa. Lệnh không tạo tài khoản hay đơn hàng; danh mục đã có sẽ được giữ nguyên.

4. Mở <http://localhost/QuanLyBanSach/>. Đăng ký tài khoản khách hàng trên website rồi đăng nhập.
5. Thử xem/tìm/lọc sách, thêm sách vào giỏ, đặt đơn COD. Khi đặt đơn, điền tên người nhận, số điện thoại, tỉnh/thành phố, phường/xã và địa chỉ cụ thể.
6. Mở **Tài khoản → Đơn hàng** để xem đơn vừa tạo hoặc hủy khi đơn còn chờ xác nhận.

Nếu XAMPP được cài ở nơi khác, đổi đường dẫn `C:\xampp\php\php.exe` trong các lệnh cho phù hợp.

### Đường dẫn nhanh

| Trang | Đường dẫn |
| --- | --- |
| Danh sách sách | `http://localhost/QuanLyBanSach/index.php?route=products` |
| Đăng ký | `http://localhost/QuanLyBanSach/index.php?route=register` |
| Giỏ hàng | `http://localhost/QuanLyBanSach/index.php?route=cart` |
| Đơn hàng của tôi | `http://localhost/QuanLyBanSach/index.php?route=orders` |
| Trang quản trị | `http://localhost/QuanLyBanSach/index.php?route=admin` |
| Quản lý đơn hàng | `http://localhost/QuanLyBanSach/index.php?route=admin-orders` |

Các trang tài khoản và quản trị yêu cầu đăng nhập; trang quản trị yêu cầu vai trò `Admin`.

## Tạo tài khoản quản trị để chấm thử

Đăng ký một tài khoản thứ hai trên website, sau đó dùng email đó để cấp vai trò `Admin` từ PowerShell:

```powershell
Set-Location C:\xampp\htdocs\QuanLyBanSach
$email = (Read-Host "Nhập email đã đăng ký trên website").Trim()
& "C:\xampp\php\php.exe" "database\set-role.php" "$email" "Admin"
```

Trước đó cần đăng ký tài khoản này trên website. Chạy hai dòng lệnh trong cùng cửa sổ PowerShell; khi được hỏi, nhập đúng email đã dùng để đăng ký. Thành công sẽ hiện `Role updated: <email> -> Admin`. Nếu hiện `Usage`, kiểm tra lại email và chạy lệnh ngay trong cửa sổ vừa nhập email. Nếu hiện `Account not found`, tài khoản chưa được đăng ký trong database đang cấu hình. Đăng xuất rồi đăng nhập lại tài khoản này để mở trang quản trị. Tài khoản mới đăng ký mặc định có vai trò khách hàng; dự án không có tài khoản/mật khẩu mẫu được tạo sẵn.

Để trả tài khoản về vai trò khách hàng, chạy lệnh tương tự với `User`:

```powershell
$email = Read-Host "Nhập email tài khoản cần đổi về User"
& "C:\xampp\php\php.exe" "database\set-role.php" "$email" "User"
```

## Checklist test nhanh

Nên tạo một tài khoản khách hàng và một tài khoản quản trị. Để kiểm tra luồng đơn hàng, đăng nhập tài khoản khách hàng và đặt đơn COD trước, sau đó đăng nhập tài khoản quản trị.

**Khách hàng**

- Xem danh sách sách; tìm kiếm, lọc thể loại, sắp xếp và phân trang.
- Thêm sách vào giỏ, cập nhật giỏ và đặt đơn COD.
- Xem thông tin đơn hàng; hủy đơn đang chờ xác nhận.
- Sau khi quản trị viên đánh dấu đơn đã giao, gửi yêu cầu đổi sách hoặc trả hàng/hoàn tiền.
- Cập nhật hồ sơ cá nhân và đổi mật khẩu.

**Quản trị viên**

- Trang tổng quan và báo cáo doanh thu.
- Thêm/sửa/ẩn/xóa sách; quản lý thể loại, tác giả và nhà xuất bản.
- Tạo phiếu nhập kho và kiểm tra tồn kho.
- Mở **Đơn hàng**, chọn đơn khách vừa đặt và chuyển tuần tự **Chờ xác nhận → Đã xác nhận → Đang giao → Đã giao**. Có thể hủy đơn; khi xác nhận đơn, tồn kho được trừ.
- Trong chi tiết đơn, cập nhật đối soát COD; xử lý yêu cầu đổi trả do khách gửi.
- Kiểm tra quản lý tài khoản và nhật ký thao tác.

## Cấu hình

Thông tin kết nối database nằm ở `config/database.php`. Mặc định là host `127.0.0.1`, cổng `3306`, database `QuanLyBanSach`, user `root`, mật khẩu rỗng. Có thể ghi đè bằng các biến môi trường `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`.

Phí giao hàng, mức miễn phí vận chuyển và số sách mỗi trang nằm ở `config/app.php`.

Email xác nhận/đổi trạng thái đơn hàng là tùy chọn. Nếu cần kiểm tra gửi mail, sao chép `config/mail.local.example.php` thành `config/mail.local.php` rồi điền thông tin SMTP (Gmail cần Mật khẩu ứng dụng). Không cấu hình SMTP thì đơn hàng vẫn hoạt động và được lưu; website chỉ không gửi email.

## Cấu trúc dự án

```text
index.php       Điểm vào website
config/         Cấu hình, routes, bootstrap
controller/     Nhận request, gọi model và chọn view
model/          Nghiệp vụ và truy vấn MySQL
view/           Giao diện các trang
libs/           Router, helper và PHPMailer
public/assets/  CSS, JavaScript, hình ảnh
database/       Schema, dữ liệu mẫu, setup, migration, công cụ cấp vai trò
```

Luồng xử lý chính: `index.php` → `config/routes.php` → `controller/` → `model/` → `view/`.

## Khi không chạy được

- Kiểm tra Apache và MySQL đang bật trong XAMPP.
- Nếu trang báo lỗi kết nối, kiểm tra user/mật khẩu MySQL trong `config/database.php` và đảm bảo PHP CLI có PDO MySQL.
- Nếu vào **Nhập kho** mà gặp trang “BookStore tạm thời chưa sẵn sàng”, hãy bật MySQL rồi chạy lại migration từ thư mục dự án:

  ```powershell
  Set-Location C:\xampp\htdocs\QuanLyBanSach
  & C:\xampp\php\php.exe database\setup.php
  ```

  Nếu setup báo lỗi hoặc trang vẫn chưa hoạt động, xem log Apache tại `C:\xampp\apache\logs\error.log`; trang 503 không hiển thị nguyên nhân chi tiết.
- Nếu giỏ hàng hoặc đặt hàng không hoạt động, bật JavaScript trên trình duyệt.
- Chạy lại `database/setup.php` nếu vừa tạo database hoặc cần áp dụng các migration mới. Script giữ lại danh mục hiện có.

