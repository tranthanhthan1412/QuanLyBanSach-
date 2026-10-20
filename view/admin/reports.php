<p class="admin-intro">
    Xem doanh thu, tình hình đơn hàng và các sách bán chạy
    trong khoảng thời gian bạn lựa chọn.
</p>

<!-- Bộ lọc khoảng thời gian -->
<section class="admin-panel">
    <div class="admin-panel-heading">
        <div>
            <h2>Lọc báo cáo theo thời gian</h2>
            <p>Chọn ngày bắt đầu và ngày kết thúc.</p>
        </div>
    </div>

    <form class="admin-filters"
          method="get"
          action="<?= e(base_url() . '/index.php') ?>">

        <input type="hidden"
               name="route"
               value="admin-reports">

        <label>
            Từ ngày
            <input type="date"
                   name="from"
                   value="<?= e($from) ?>"
                   required>
        </label>

        <label>
            Đến ngày
            <input type="date"
                   name="to"
                   value="<?= e($to) ?>"
                   required>
        </label>

        <button type="submit" class="button">
            Xem báo cáo
        </button>
    </form>
</section>

<?php if ($error !== ''): ?>
    <div class="admin-alert is-error" role="alert">
        <?= e($error) ?>
    </div>
<?php elseif ($summary !== null): ?>

    <!-- Các ô thống kê -->
    <div class="admin-stats">

        <article class="admin-stat">
            <div class="admin-stat-top">
                <span>Doanh thu đã giao</span>
                <span class="admin-stat-icon">
                    <?= icon('briefcase') ?>
                </span>
            </div>

            <strong>
                <?= e(money($summary['revenue'] ?? 0)) ?>
            </strong>

            <small>Tổng tiền sách của đơn đã giao</small>
        </article>

        <article class="admin-stat">
            <div class="admin-stat-top">
                <span>Tổng đơn hàng</span>
                <span class="admin-stat-icon">
                    <?= icon('box') ?>
                </span>
            </div>

            <strong>
                <?= e($summary['total_orders'] ?? 0) ?>
            </strong>

            <small>Đơn được tạo trong khoảng ngày</small>
        </article>

        <article class="admin-stat">
            <div class="admin-stat-top">
                <span>Đơn đã giao</span>
                <span class="admin-stat-icon">
                    <?= icon('check') ?>
                </span>
            </div>

            <strong>
                <?= e($summary['completed_orders'] ?? 0) ?>
            </strong>

            <small>Đơn đã giao thành công</small>
        </article>

        <article class="admin-stat">
            <div class="admin-stat-top">
                <span>Đơn đã hủy</span>
                <span class="admin-stat-icon">
                    <?= icon('clock') ?>
                </span>
            </div>

            <strong>
                <?= e($summary['cancelled_orders'] ?? 0) ?>
            </strong>

            <small>Đơn có trạng thái đã hủy</small>
        </article>

    </div>

    <!-- Bảng sách bán chạy -->
    <section class="admin-panel">

        <div class="admin-panel-heading">
            <div>
                <h2>Top 10 sách bán chạy</h2>
                <p>
                    Thống kê số lượng bán từ các đơn
                    đã giao trong khoảng thời gian chọn.
                </p>
            </div>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>STT</th>
                        <th>Tên sách</th>
                        <th>Số lượng bán</th>
                        <th>Doanh thu sách</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($bestSellers as $index => $book): ?>
                        <tr>
                            <td><?= e($index + 1) ?></td>

                            <td>
                                <strong>
                                    <?= e($book['tenSach']) ?>
                                </strong>
                            </td>

                            <td>
                                <?= e($book['soLuongBan']) ?>
                            </td>

                            <td>
                                <?= e(money($book['doanhThuSach'])) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (!$bestSellers): ?>
                        <tr>
                            <td colspan="4">
                                <div class="admin-empty">
                                    <h3>Chưa có dữ liệu bán hàng</h3>
                                    <p>
                                        Không có sách đã giao trong
                                        khoảng thời gian này.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

    </section>

<?php endif; ?>
