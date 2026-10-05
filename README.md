# BookStore — Website bán sách

Dự án PHP thuần theo mô hình MVC. Hiện dùng dữ liệu mẫu, chưa kết nối MySQL; đăng nhập, đăng ký và đặt hàng đang mô phỏng.

## Chạy dự án

1. Đặt dự án trong `C:\xampp\htdocs\QuanLyBanSach`.
2. Bật **Apache** trong XAMPP (PHP 8.2+, extension `mbstring`).
3. Mở **http://localhost/QuanLyBanSach/**.

Không cần bật MySQL, cài npm hay Composer. Cấu hình URL, phí giao hàng và số sách mỗi trang nằm trong `config/app.php`.

## Cấu trúc

```text
admin/          dành cho phần quản trị sau này
config/         cấu hình, autoload và khai báo route
controller/     nhận request, gọi model và chọn view
libs/           Controller dùng chung, Router và helper
model/          xử lý sách, danh mục và đơn hàng
  data/         dữ liệu mẫu
view/           giao diện, layout và các phần dùng chung
public/
  assets/       CSS, JavaScript và ảnh
  index.php     hỗ trợ URL /public/ và PHP CLI
index.php       điểm vào chính
.htaccess       cấu hình truy cập Apache
README.md       hướng dẫn dự án
```

Luồng xử lý: `index.php` → `config/bootstrap.php` → `config/routes.php` → controller → model → view.

Các tệp `.htaccess` trong thư mục mã nguồn chặn truy cập trực tiếp qua Apache.

## Chức năng hiện có

- Xem sách, tìm kiếm không dấu, lọc danh mục, sắp xếp và phân trang.
- Giỏ hàng lưu trên trình duyệt; tính tiền và phí giao hàng.
- Form đăng nhập, đăng ký và đặt hàng COD mô phỏng.
- Xem tài khoản và đơn hàng mẫu; giao diện hỗ trợ điện thoại.

Ví dụ: `index.php?route=products`, `index.php?route=product&id=1`, `index.php?route=cart`.

Dữ liệu mẫu gồm 24 sách, 8 danh mục và 3 đơn hàng. Form không tạo tài khoản hay đơn thật và không lưu thông tin cá nhân. Khi làm backend, thay nguồn dữ liệu trong `model/` bằng MySQL.

Có thể chạy bằng PHP CLI: `C:\xampp\php\php.exe -S localhost:8000 -t public`, rồi mở `http://localhost:8000/`.

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
