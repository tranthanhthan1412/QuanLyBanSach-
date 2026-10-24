<?php
[$status, $tone] = order_status($order['trangThai']);
[$codLabel, $codTone] = cod_status($order['trangThaiCOD'] ?? 'ChuaThu');
$canReturn = in_array(\App\Models\AdminModel::statusCode($order['trangThai']), ['DaGiao'], true);
$activeReturn = $order['return_request'] ?? null;
?>
<div class="container page-content">
    <?php $breadcrumbs = [['label' => 'Đơn hàng', 'url' => url('orders')], ['label' => '#' . $order['maDH']]]; require ROOT_PATH . '/view/partials/breadcrumb.php'; ?>
    <?php if (!empty($cancelNotice)): ?>
    <div class="notice<?= empty($cancelNotice['success']) ? ' notice-error' : '' ?>"
         role="<?= empty($cancelNotice['success']) ? 'alert' : 'status' ?>">
        <?= e($cancelNotice['message']) ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($flashSuccess)): ?>
    <div class="alert alert-success" style="margin-bottom: 20px; padding: 14px 18px; border-radius: 8px; background: #eaf9ef; color: #1e7040; border: 1px solid #c2eed1;">
        <?= e($flashSuccess) ?>
    </div>
    <?php endif; ?>

    <?php if (!empty($flashError)): ?>
    <div class="alert alert-error" style="margin-bottom: 20px; padding: 14px 18px; border-radius: 8px; background: #fff0f0; color: #ad2929; border: 1px solid #ffd4d4;">
        <?= e($flashError) ?>
    </div>
    <?php endif; ?>

    <div class="page-heading heading-row">
        <div>
            <h1>Đơn hàng #<?= e($order['maDH']) ?></h1>
            <p><?= e($order['tenDH'] ?: 'Chi tiết đơn hàng') ?></p>
        </div>
        <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
            <span class="status status-<?= e($tone) ?>"><?= e($status) ?></span>
            <span class="status status-<?= e($codTone) ?>"><?= e($codLabel) ?></span>
        </div>
    </div>
    <div class="checkout-grid">
        <section class="panel">
            <h2>Sách trong đơn</h2>
            <?php if (!$order['items']): ?><p>Đơn hàng chưa có sách.</p><?php endif; ?>
            <?php foreach ($order['items'] as $line): ?>
            <div class="order-item">
                <div>
                    <a href="<?= e(url('product', ['id' => $line['maSach']])) ?>"><?= e($line['tenSach']) ?></a>
                    <small><?= e($line['tenTG']) ?> · Số lượng: <?= (int) $line['soLuong'] ?></small>
                </div>
                <strong><?= money($line['tongTien']) ?></strong>
            </div>
            <?php endforeach; ?>
            <div class="summary-row"><span>Số lượng</span><span><?= (int) $order['tongSL'] ?> cuốn</span></div>
            <div class="summary-row summary-total"><span>Tổng tiền sách</span><strong><?= money($order['tongTien']) ?></strong></div>
            <div class="summary-row"><span>Phí vận chuyển</span><span><?= money($order['phiVanChuyen']) ?></span></div>
            <div class="summary-row summary-total"><span>Tổng thanh toán (COD)</span><strong><?= money($order['total']) ?></strong></div>
            <div class="summary-row">
                <span>Trạng thái thu tiền COD</span>
                <span class="status status-<?= e($codTone) ?>"><?= e($codLabel) ?></span>
            </div>
            <?php if (!empty($order['ngayDoiSoatCOD'])): ?>
            <div class="summary-row" style="font-size: 12px; color: var(--color-muted);">
                <span>Thời gian đối soát COD</span>
                <span><?= e($order['ngayDoiSoatCOD']) ?></span>
            </div>
            <?php endif; ?>

            <?php if ($canReturn): ?>
            <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid var(--color-border);">
                <h2>Yêu cầu đổi trả & Hoàn tiền</h2>
                <?php if ($activeReturn): [$retLabel, $retTone] = return_status($activeReturn['trangThai']); ?>
                <div style="border: 1px solid var(--color-border); border-radius: 8px; padding: 16px; background: #fafbfc; margin-top: 12px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 12px;">
                        <strong>Yêu cầu đổi trả #<?= e($activeReturn['maYC']) ?></strong>
                        <span class="status status-<?= e($retTone) ?>"><?= e($retLabel) ?></span>
                    </div>
                    <p style="margin-bottom: 6px;"><strong>Loại yêu cầu:</strong> <?= e(return_type_label($activeReturn['loaiYeuCau'])) ?></p>
                    <p style="margin-bottom: 6px;"><strong>Lý do:</strong> <?= e(return_reason_label($activeReturn['lyDo'])) ?></p>
                    <p style="margin-bottom: 6px;"><strong>Mô tả của bạn:</strong> <?= e($activeReturn['chiTiet'] ?: 'Không có.') ?></p>
                    <p style="margin-bottom: 6px; font-size: 13px; color: var(--color-muted);"><strong>Thời gian gửi:</strong> <?= e($activeReturn['ngayTao']) ?></p>
                    <?php if (!empty($activeReturn['phanHoiAdmin'])): ?>
                    <div style="margin-top: 10px; padding: 10px 14px; background: #fff; border-left: 3px solid #3b82f6; border-radius: 4px;">
                        <strong>Phản hồi từ cửa hàng:</strong>
                        <p style="margin-top: 4px;"><?= e($activeReturn['phanHoiAdmin']) ?></p>
                    </div>
                    <?php endif; ?>
                    <?php if ($activeReturn['trangThai'] === 'DaHoanTien'): ?>
                    <p style="margin-top: 10px; color: #1e7040; font-weight: 600;">✓ Cửa hàng đã hoàn tất đổi trả / hoàn tiền thành công (<?= money($activeReturn['soTienHoan']) ?>).</p>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <p style="color: var(--color-muted); font-size: 13px; margin-bottom: 14px;">
                    Đơn hàng đã được giao. Nếu bạn phát hiện sách bị lỗi in ấn, hư hỏng hoặc giao sai tựa sách, bạn có thể gửi yêu cầu đổi trả tại đây.
                </p>
                <form method="post" action="<?= e(url('order-return')) ?>" style="display: grid; gap: 14px;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="order_id" value="<?= e($order['maDH']) ?>">
                    <div>
                        <label for="return-type" style="display: block; font-weight: 550; margin-bottom: 6px;">Hình thức mong muốn</label>
                        <select id="return-type" name="type" required class="full-width" style="padding: 9px 12px; border: 1px solid var(--color-border); border-radius: 6px;">
                            <option value="HoanTien">Trả hàng & Hoàn tiền</option>
                            <option value="DoiHang">Đổi sang cuốn sách mới</option>
                        </select>
                    </div>
                    <div>
                        <label for="return-reason" style="display: block; font-weight: 550; margin-bottom: 6px;">Lý do đổi trả</label>
                        <select id="return-reason" name="reason" required class="full-width" style="padding: 9px 12px; border: 1px solid var(--color-border); border-radius: 6px;">
                            <option value="SachLoi">Sách bị lỗi in ấn / rách trang</option>
                            <option value="GiaoSai">Giao sai sách so với đơn đặt</option>
                            <option value="HuHong">Hư hỏng do quá trình vận chuyển</option>
                            <option value="Khac">Lý do khác</option>
                        </select>
                    </div>
                    <div>
                        <label for="return-detail" style="display: block; font-weight: 550; margin-bottom: 6px;">Mô tả chi tiết tình trạng sách</label>
                        <textarea id="return-detail" name="detail" rows="3" required minlength="5" maxlength="1000" placeholder="Mô tả cụ thể lỗi sách hoặc vấn đề bạn gặp phải..." style="width: 100%; padding: 10px 12px; border: 1px solid var(--color-border); border-radius: 6px;"></textarea>
                    </div>
                    <button class="button" type="submit">Gửi yêu cầu đổi trả</button>
                </form>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </section>

        <aside class="panel">
            <?php if ($order['tenNguoiNhan']): ?>
            <h2>Thông tin giao hàng</h2>
            <p><?= e($order['tenNguoiNhan']) ?> · <?= e($order['sdtNhan']) ?></p>
            <p><?= e($order['diaChiNhan']) ?></p>
            <p>Email nhận đơn: <?= e($order['emailNhan']) ?></p>
            <p>Ngày đặt: <?= e($order['ngayTao']) ?></p>
            <?php endif; ?>
            <h2>Ghi chú</h2>
            <p><?= e($order['ghiChu'] ?: 'Không có ghi chú.') ?></p>
            <?php if ($order['trangThai'] === 'ChoXacNhan'): ?>
            <form method="post" action="<?= e(url('order-cancel')) ?>"
                  onsubmit="return confirm('Bạn có chắc chắn muốn hủy đơn hàng này?');">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= (int) $order['maDH'] ?>">
                <div class="field">
                    <label for="cancel-reason">Lý do hủy (không bắt buộc)</label>
                    <textarea id="cancel-reason" name="reason" maxlength="180" rows="2" placeholder="Ví dụ: Tôi muốn thay đổi đơn hàng"></textarea>
                </div>
                <button class="button button-outline" type="submit">Hủy đơn hàng</button>
                <p class="muted">Chỉ được hủy khi đơn còn Chờ xác nhận.</p>
            </form>
            <?php endif; ?>
            <a class="text-link back-link" href="<?= e(url('orders')) ?>">← Quay lại đơn hàng</a>
        </aside>
    </div>
</div>
