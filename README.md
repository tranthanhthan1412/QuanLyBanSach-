# BookStore — Website bán sách

Dự án PHP thuần theo MVC, chạy trên PHP 8.2+ và MySQL/MariaDB. Danh mục, sách, tài khoản và lịch sử đơn hàng đọc từ database. Đăng ký/đăng nhập đã hoạt động; giỏ hàng vẫn lưu trong trình duyệt và đặt hàng COD vẫn là mô phỏng, chưa tạo đơn hay trừ tồn kho.

## Chạy lần đầu với XAMPP

1. Đặt dự án tại `C:\xampp\htdocs\QuanLyBanSach`.
2. Bật **Apache** và **MySQL** trong XAMPP. PHP cần các extension `pdo_mysql` và `mbstring`.
3. Mở PowerShell trong thư mục dự án và chạy:

   ```powershell
   & C:\xampp\php\php.exe database\setup.php
   ```

4. Mở **http://localhost/QuanLyBanSach/**.
5. Vào **Đăng ký** để tạo tài khoản, sau đó đăng nhập. Không có tài khoản hay mật khẩu mặc định.

Không cần Composer, npm hoặc Node.js để chạy website. Node.js chỉ cần khi chạy kiểm thử tự động.

## Cấu hình database

`config/database.php` có mặc định XAMPP: host `127.0.0.1`, port `3306`, database `QuanLyBanSach`, user `root`, mật khẩu rỗng. Có thể chỉnh cấu hình cục bộ hoặc dùng các biến môi trường `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`. Không commit mật khẩu thật.

Ví dụ chạy CLI với database khác:

```powershell
$env:DB_NAME = 'BookStoreLocal'
$env:DB_USER = 'root'
& C:\xampp\php\php.exe database\setup.php
& C:\xampp\php\php.exe -S localhost:8000 -t public
```

Mở **http://localhost:8000/**. Biến môi trường đặt trong PowerShell chỉ áp dụng cho các tiến trình khởi chạy từ cửa sổ đó; Apache của XAMPP cần cấu hình tương ứng riêng. Cấu hình URL, phí giao hàng và số sách mỗi trang nằm trong `config/app.php`.

## Khởi tạo và nâng cấp dữ liệu

Lệnh `database/setup.php` chỉ chạy bằng CLI. Lệnh này:

- Tạo database và 10 bảng nếu chưa tồn tại, dựa trên `database/schema.sql`.
- Bổ sung cột ảnh `sach.hinhAnh` nếu đang dùng cấu trúc từ bản pull trước.
- Thêm vai trò `Admin` và `User` nếu thiếu. Đăng ký tìm vai trò theo tên, không phụ thuộc mã vai trò bằng 2.
- Thêm **8 thể loại, 24 sách minh họa**, tác giả và nhà xuất bản khi cả bốn bảng danh mục này còn trống. Ảnh, giá và thông tin sách mẫu chỉ phục vụ đồ án.
- Không tạo tài khoản hoặc đơn hàng mẫu. Tài khoản mới sẽ có danh sách đơn hàng trống.

Có thể chạy lại lệnh sau khi pull code. Dữ liệu sách, tài khoản, đơn hàng đã có không bị xóa hay ghi đè; nếu danh mục đã có dữ liệu, bước nạp sách mẫu được bỏ qua. Việc nạp dữ liệu mẫu dùng transaction; các lệnh tạo/sửa bảng của MySQL không nằm trong transaction đó. Tài khoản database chạy setup cần quyền tạo database, tạo/sửa bảng và ghi dữ liệu.

Website chỉ mở kết nối dùng chung cho mỗi request; không tự tạo database hoặc chạy lệnh tạo bảng khi người dùng tải trang.

## Chức năng hiện có và giới hạn

- Xem sách, tìm theo tên/tác giả/thể loại, lọc danh mục, sắp xếp và phân trang bằng MySQL. Database mới dùng `utf8mb4_unicode_ci`, hỗ trợ tìm không dấu.
- Ảnh sách lấy từ tài nguyên trong dự án; sách chưa có ảnh dùng ảnh thay thế.
- Giỏ hàng lưu ID và số lượng trên trình duyệt; giới hạn theo tồn kho, tính giá và phí giao hàng. Chưa đồng bộ giỏ giữa tài khoản/thiết bị, chưa giữ chỗ tồn kho.
- Đăng ký, đăng nhập bằng mật khẩu đã hash; biểu mẫu có CSRF token, session được đổi ID khi đăng nhập/đăng xuất. Đăng xuất dùng POST.
- Trang tài khoản hiển thị dữ liệu người dùng đang đăng nhập. Danh sách và chi tiết đơn hàng chỉ đọc đơn của người dùng đó; dùng số tiền đã lưu trong chi tiết đơn.
- **Đặt hàng COD và nhận bản tin vẫn là demo.** Cần JavaScript; thông tin người nhận/email nhận tin không được lưu. Hoàn tất đặt hàng thử sẽ xóa giỏ và tạo xác nhận tạm trong phiên trình duyệt; không tạo bản ghi đơn hàng trong MySQL.
- Phân quyền `Admin` (quản trị viên) và `User` (khách hàng) ở phía server. Admin có trang tổng quan số đầu sách, tồn kho, khách hàng và đơn hàng; khách hàng truy cập trang này nhận HTTP 403.
- Chưa có thêm/sửa/xóa sách trên trang quản trị, chỉnh sửa hồ sơ, đặt hàng thật, cập nhật trạng thái đơn, thanh toán online hay báo cáo doanh thu.

Ví dụ URL: `index.php?route=products`, `index.php?route=product&id=1`, `index.php?route=cart`. ID sách thực tế tùy dữ liệu trong database; nên mở sách từ danh sách.

## Phân quyền admin và khách hàng

Đăng ký trên website luôn tạo khách hàng (`User`), kể cả khi request cố gửi vai trò `Admin`. Không có tài khoản admin hay mật khẩu mặc định. Sau khi đăng nhập, admin được chuyển đến `index.php?route=admin`, khách hàng đến trang tài khoản. Liên kết **Quản trị** chỉ hiển thị với admin trên máy tính, điện thoại và menu tài khoản.

Để cấp quyền admin cho một tài khoản đã đăng ký, chạy trong thư mục dự án (thay email ví dụ bằng tài khoản của bạn):

```powershell
& C:\xampp\php\php.exe database\set-role.php 'admin@example.com' Admin
```

Để chuyển tài khoản đó về khách hàng:

```powershell
& C:\xampp\php\php.exe database\set-role.php 'admin@example.com' User
```

Lệnh chỉ chạy bằng CLI, dùng cấu hình database hiện tại, không tạo tài khoản mới. Quyền được đọc lại từ database mỗi request; thay đổi quyền có hiệu lực ở request tiếp theo, không cần đăng xuất. Tài khoản bị xóa hoặc có vai trò không hỗ trợ sẽ mất phiên xác thực.

Trang tài khoản và đơn hàng yêu cầu đăng nhập; cả hai vai trò chỉ xem được đơn của chính mình ở các trang này. Danh mục, giỏ hàng và luồng đặt hàng mô phỏng vẫn truy cập công khai. Trang quản trị hiện cung cấp tổng quan, chưa có chức năng sửa dữ liệu.

Khi thêm route quản trị, khai báo danh sách quyền ở cả GET và POST, ví dụ `$router->get('admin', [AdminController::class, 'index'], ['Admin']);`. Route có danh sách quyền rỗng là công khai. Router kiểm tra quyền trước khi chạy controller; POST vẫn cần CSRF token hợp lệ. Không dùng việc ẩn menu làm kiểm soát truy cập.

## Kiểm thử

Kiểm tra cú pháp PHP và các trường hợp giỏ hàng, tính tiền, tồn kho:

```powershell
Get-ChildItem -Recurse -Filter *.php | ForEach-Object { & C:\xampp\php\php.exe -l $_.FullName }
node --test tests\cart.test.mjs
```

Kiểm thử tích hợp (cần Node.js 22+, MySQL đang chạy, PHP CLI và quyền tạo/xóa database test):

```powershell
node tests\smoke.mjs
```

Bài test tự tạo database riêng có tên `bookstore_test_<pid>_<timestamp>`, chạy setup hai lần, kiểm tra giữ nguyên dữ liệu, danh mục/tìm kiếm/ảnh, đăng ký/đăng nhập/đăng xuất, CSRF và quyền xem đơn hàng. Test cũng kiểm tra chặn khách hàng vào trang admin, điều hướng theo vai trò, bỏ qua vai trò giả mạo khi đăng ký, cấp/thu hồi quyền ngay trong phiên hiện tại và vô hiệu hóa phiên của tài khoản bị xóa hoặc có vai trò lạ. Test mở PHP server trên cổng trống, dùng session tạm trong `tests/.tmp-*/`, rồi dừng server và xóa database test. Không ghi vào database đang dùng cho website. Có thể đặt `PHP_BIN` nếu PHP nằm ở đường dẫn khác.

## Xử lý lỗi chạy dự án

- **Trang báo tạm thời chưa sẵn sàng:** kiểm tra MySQL, cấu hình kết nối, rồi chạy `database/setup.php`. Chi tiết kỹ thuật được ghi vào log PHP/Apache, không hiển thị trên trang.
- **Thiếu database/bảng/cột hinhAnh hoặc vai trò User:** chạy lại setup; việc pull code không tự cập nhật database.
- **Session không lưu được:** kiểm tra quyền ghi thư mục `session.save_path` trong PHP đang chạy. Với XAMPP mặc định thường là `C:\xampp\tmp`.
- **403 khi gửi form:** tải lại trang để lấy CSRF token mới, nhất là sau khi đăng nhập/đăng xuất ở tab khác.
- **405 khi mở logout bằng URL:** dùng nút Đăng xuất trên giao diện. Route này chỉ nhận POST.

## Cấu trúc

Luồng MVC: `index.php` → `config/routes.php` → `controller/` → `model/` → `view/`. Dữ liệu khởi tạo nằm trong `database/`; `model/` chỉ chứa xử lý dữ liệu khi ứng dụng chạy.

```text
config/         cấu hình ứng dụng, database, danh mục, autoload và routes
controller/     nhận request, gọi model và chọn view
model/          kết nối database và truy vấn dữ liệu
view/           giao diện, layout và partials
libs/           lớp MVC dùng chung: Router, Controller và helper
public/         điểm vào CLI server, CSS, JavaScript và ảnh
database/       setup.php, schema.sql và seed.php để khởi tạo dữ liệu
tests/          kiểm thử giỏ hàng và tích hợp HTTP
index.php       điểm vào Apache
```

Các thư mục mã nguồn, database và tests có `.htaccess` chặn truy cập trực tiếp qua Apache. PHP CLI server nên chạy với `-t public` như hướng dẫn.

## Nguồn ảnh

Thiết kế tham khảo: https://fig-icy-54010016.figma.site/. Ảnh Unsplash được lưu trong `public/assets/images/`, dùng để minh họa:

| Tệp | Nguồn |
| --- | --- |
| `library.jpg` | https://images.unsplash.com/photo-1756037020659-6f9d3418f6b6 |
| `books-stack.jpg` | https://images.unsplash.com/photo-1596522681657-8e9057309a7e |
| `notebook.jpg` | https://images.unsplash.com/photo-1574583943644-ee72a4990535 |
| `shelves.jpg` | https://images.unsplash.com/photo-1554357395-dbdc356ca5da |
| `children.jpg` | https://images.unsplash.com/photo-1631426964394-06606872d836 |
| `classics.jpg` | https://images.unsplash.com/photo-1692742593570-ca989f1febd9 |
