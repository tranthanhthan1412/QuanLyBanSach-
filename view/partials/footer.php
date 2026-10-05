<footer class="site-footer">
    <div class="container">
        <div class="footer-grid">
            <div>
                <a class="brand footer-brand" href="<?= e(url()) ?>"><?= icon('book') ?><span>BookStore</span></a>
                <p>Thế giới sách và truyện tranh dành cho bạn. Tìm cuốn sách yêu thích, mở ra những điều mới mẻ mỗi ngày.</p>
                <div class="footer-note"><?= icon('book') ?><span>Mỗi trang sách, một hành trình.</span></div>
            </div>
            <div>
                <h2>Khám phá BookStore</h2>
                <a href="<?= e(url('products')) ?>">Tất cả sách & truyện</a>
                <a href="<?= e(url('products', ['category' => 'truyen-tranh'])) ?>">Truyện tranh</a>
                <a href="<?= e(url('products', ['sort' => 'newest'])) ?>">Sách mới</a>
                <a href="<?= e(url('cart')) ?>">Giỏ hàng của bạn</a>
                <a href="<?= e(url('orders')) ?>">Xem đơn hàng mẫu</a>
            </div>
            <div>
                <h2>Chăm sóc khách hàng</h2>
                <p class="icon-line"><?= icon('mail') ?><span>hello@bookstore.example<br><small>Thông tin liên hệ minh họa</small></span></p>
                <p class="icon-line"><?= icon('pin') ?><span>Nhà sách trực tuyến BookStore<br>Đồ án Công nghệ phần mềm</span></p>
                <h3>Giờ hỗ trợ</h3>
                <p>Thứ 2 – Thứ 6: 09:00 – 18:00<br>Thứ 7: 09:00 – 15:00<br>Chủ nhật: Nghỉ</p>
            </div>
            <div>
                <h2>Bản tin sách</h2>
                <p>Cập nhật những cuốn sách mới và câu chuyện thú vị từ BookStore.</p>
                <form data-demo-form="newsletter" class="newsletter" novalidate method="post" action="<?= e(url()) ?>">
                    <label class="sr-only" for="newsletter-email">Email nhận bản tin</label>
                    <input id="newsletter-email" name="email" type="email" placeholder="Nhập email của bạn" required autocomplete="email" aria-describedby="newsletter-error">
                    <span class="field-error" id="newsletter-error"></span>
                    <button class="button button-small" type="submit">Đăng ký nhận tin</button>
                    <p class="form-result" role="status" tabindex="-1" hidden></p>
                </form>
                <small>Form minh họa. Email không được gửi hoặc lưu.</small>
            </div>
        </div>
        <div class="footer-bottom"><span>© <?= date('Y') ?> BookStore. Đồ án nhóm 3 sinh viên.</span><span class="payment-note">Phương thức thanh toán: <span class="tag">COD · Mô phỏng</span></span></div>
    </div>
</footer>
