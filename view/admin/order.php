<?php
[$statusLabel, $statusColor] = order_status($order['trangThai']);
[$codLabel, $codColor] = cod_status($order['trangThaiCOD'] ?? 'ChuaThu');
$next = \App\Models\AdminModel::nextStatuses($order['trangThai']);
$returnRequests = $order['return_requests'] ?? [];
$reconciler = $order['reconciler'] ?? null;
?>
<a class="admin-back" href="<?= e(url('admin-orders')) ?>">← Danh sách đơn hàng</a>
<div class="admin-editor">
    <div>
        <section class="admin-panel">
            <div class="admin-panel-heading">
                <div>
                    <h2><?= e($order['tenDH'] ?: 'Đơn #' . $order['maDH']) ?></h2>
                    <p><?= e($order['ngayTao'] ?: 'Chưa ghi nhận ngày tạo') ?></p>
                </div>
                <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                    <span class="admin-badge <?= e($statusColor) ?>"><?= e($statusLabel) ?></span>
                    <span class="admin-badge <?= e($codColor) ?>"><?= e($codLabel) ?></span>
                </div>
            </div>
            <div class="admin-table-wrap">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Sản phẩm</th>
                            <th>Số lượng</th>
                            <th>Thành tiền đã lưu</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order['items'] as $item): ?>
                        <tr>
                            <td>
                                <strong><?= e($item['tenSach']) ?></strong>
                                <small>Mã #<?= e($item['maSach']) ?> · Còn <?= e($item['tonKho']) ?> cuốn</small>
                            </td>
                            <td><?= e($item['soLuong']) ?></td>
                            <td><?= e(money($item['tongTien'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (!$order['items']): ?>
                        <tr><td colspan="3">Đơn chưa có sản phẩm.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="admin-order-total"><span>Tổng tiền sách</span><strong><?= e(money($order['total'])) ?></strong></div>
            <div class="admin-order-total"><span>Phí vận chuyển</span><strong><?= e(money($order['phiVanChuyen'])) ?></strong></div>
            <div class="admin-order-total"><span>Tổng thanh toán (COD)</span><strong><?= e(money($order['total'] + $order['phiVanChuyen'])) ?></strong></div>
        </section>

        <section class="admin-panel admin-form-panel admin-spaced">
            <h2>Ghi chú của đơn hàng</h2>
            <p class="admin-preserve"><?= e($order['ghiChu'] ?: 'Không có ghi chú.') ?></p>
        </section>

        <?php if ($order['tenNguoiNhan']): ?>
        <section class="admin-panel admin-form-panel admin-spaced">
            <h2>Thông tin giao hàng</h2>
            <dl class="admin-details">
                <dt>Người nhận</dt><dd><?= e($order['tenNguoiNhan']) ?></dd>
                <dt>Điện thoại</dt><dd><?= e($order['sdtNhan']) ?></dd>
                <dt>Địa chỉ giao hàng</dt><dd><?= e($order['diaChiNhan']) ?></dd>
                <dt>Email nhận đơn</dt><dd><?= e($order['emailNhan']) ?></dd>
                <dt>Email thông báo</dt><dd><?= e(match ($order['trangThaiEmail']) { 'sent' => 'Đã gửi', 'failed' => 'Gửi không thành công', 'pending' => 'Chưa có kết quả gửi', default => 'Chưa bật gửi mail' }) ?></dd>
            </dl>
        </section>
        <?php endif; ?>

        <section class="admin-panel admin-form-panel admin-spaced">
            <div class="admin-panel-heading">
                <div>
                    <h2>Yêu cầu đổi trả & Hoàn tiền</h2>
                    <p>Quản lý các yêu cầu đổi trả hoặc hoàn tiền từ khách hàng cho đơn này</p>
                </div>
            </div>
            <?php if (!$returnRequests): ?>
                <p class="admin-preserve">Chưa có yêu cầu đổi trả nào từ khách hàng cho đơn này.</p>
            <?php else: ?>
                <div style="display: grid; gap: 16px;">
                    <?php foreach ($returnRequests as $req): [$reqLabel, $reqColor] = return_status($req['trangThai']); ?>
                    <article style="border: 1px solid #e2e4ea; border-radius: 8px; padding: 16px; background: #fafbfc;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; gap: 8px; flex-wrap: wrap;">
                            <div>
                                <strong>Yêu cầu #<?= e($req['maYC']) ?></strong> ·
                                <small style="color: #6c7080;"><?= e($req['ngayTao']) ?></small>
                            </div>
                            <span class="admin-badge <?= e($reqColor) ?>"><?= e($reqLabel) ?></span>
                        </div>
                        <dl class="admin-details" style="margin-bottom: 12px;">
                            <dt>Loại yêu cầu</dt><dd><strong><?= e(return_type_label($req['loaiYeuCau'])) ?></strong></dd>
                            <dt>Lý do</dt><dd><?= e(return_reason_label($req['lyDo'])) ?></dd>
                            <dt>Chi tiết từ khách</dt><dd class="admin-preserve"><?= e($req['chiTiet'] ?: 'Không có mô tả thêm.') ?></dd>
                            <?php if ($req['soTienHoan'] > 0): ?>
                            <dt>Số tiền hoàn</dt><dd><strong><?= e(money($req['soTienHoan'])) ?></strong></dd>
                            <?php endif; ?>
                            <dt>Trạng thái tồn kho</dt><dd><?= (int) $req['hoanKho'] === 1 ? 'Đã nhập lại kho sách' : 'Chưa hoàn kho' ?></dd>
                            <?php if ($req['phanHoiAdmin']): ?>
                            <dt>Phản hồi của shop</dt><dd class="admin-preserve"><?= e($req['phanHoiAdmin']) ?></dd>
                            <?php endif; ?>
                            <?php if ($req['ngayXuLy']): ?>
                            <dt>Thời gian xử lý</dt><dd><?= e($req['ngayXuLy']) ?></dd>
                            <?php endif; ?>
                        </dl>

                        <?php if (in_array($req['trangThai'], ['ChoXuLy', 'ChapNhan'], true)): ?>
                        <details style="border-top: 1px dashed #d1d5db; padding-top: 12px; margin-top: 12px;">
                            <summary style="cursor: pointer; font-weight: 600; color: #3b82f6;">Xử lý yêu cầu này ↓</summary>
                            <form method="post" action="<?= e(url('admin-order-return')) ?>" style="margin-top: 12px;" data-confirm="Xác nhận cập nhật xử lý đổi trả?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="order_id" value="<?= e($order['maDH']) ?>">
                                <input type="hidden" name="request_id" value="<?= e($req['maYC']) ?>">
                                <div class="field">
                                    <label for="return-status-<?= e($req['maYC']) ?>">Hành động tiếp theo</label>
                                    <select id="return-status-<?= e($req['maYC']) ?>" name="return_status" required>
                                        <?php if ($req['trangThai'] === 'ChoXuLy'): ?>
                                        <option value="ChapNhan">Chấp nhận đổi trả (Hẹn khách gửi sách)</option>
                                        <option value="TuChoi">Từ chối đổi trả</option>
                                        <?php endif; ?>
                                        <option value="DaHoanTien">Hoàn tất & Hoàn tiền cho khách</option>
                                    </select>
                                </div>
                                <div class="field">
                                    <label for="refund-amount-<?= e($req['maYC']) ?>">Số tiền hoàn (₫)</label>
                                    <input id="refund-amount-<?= e($req['maYC']) ?>" type="number" step="1000" min="0" max="<?= e($order['total'] + $order['phiVanChuyen']) ?>" name="refund_amount" value="<?= e($req['soTienHoan'] ?: ($order['total'] + $order['phiVanChuyen'])) ?>">
                                </div>
                                <div class="field" style="display: flex; align-items: center; gap: 8px;">
                                    <input type="checkbox" id="restock-<?= e($req['maYC']) ?>" name="restock" value="1" <?= (int) $req['hoanKho'] === 1 ? 'checked disabled' : 'checked' ?>>
                                    <label for="restock-<?= e($req['maYC']) ?>" style="margin-bottom: 0;">Nhập lại số lượng sách trong đơn vào tồn kho</label>
                                </div>
                                <div class="field">
                                    <label for="admin-response-<?= e($req['maYC']) ?>">Phản hồi gửi khách hàng / Ghi chú</label>
                                    <textarea id="admin-response-<?= e($req['maYC']) ?>" name="admin_response" rows="3" maxlength="1000" placeholder="VD: Shop đã nhận lại sách và hoàn tiền thành công..."><?= e($req['phanHoiAdmin']) ?></textarea>
                                </div>
                                <button class="button full-width" type="submit">Lưu kết quả xử lý</button>
                            </form>
                        </details>
                        <?php endif; ?>
                    </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <aside class="admin-editor-side">
        <section class="admin-panel admin-form-panel">
            <h2>Thông tin tài khoản khách hàng</h2>
            <dl class="admin-details">
                <dt>Họ tên</dt><dd><?= e($order['tenND']) ?></dd>
                <dt>Email</dt><dd><?= e($order['email']) ?></dd>
                <dt>Điện thoại</dt><dd><?= e($order['SDT'] ?: 'Chưa cập nhật') ?></dd>
                <dt>Địa chỉ hồ sơ</dt><dd><?= e($order['diaChi'] ?: 'Chưa cập nhật') ?></dd>
            </dl>
        </section>

        <section class="admin-panel admin-form-panel">
            <h2>Đối soát thu tiền COD</h2>
            <div style="margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between;">
                <span>Trạng thái COD:</span>
                <span class="admin-badge <?= e($codColor) ?>"><?= e($codLabel) ?></span>
            </div>
            <?php if (!empty($order['ngayDoiSoatCOD'])): ?>
            <dl class="admin-details" style="margin-bottom: 14px;">
                <dt>Ngày đối soát</dt><dd><?= e($order['ngayDoiSoatCOD']) ?></dd>
                <?php if ($reconciler): ?><dt>Người đối soát</dt><dd><?= e($reconciler['tenND']) ?></dd><?php endif; ?>
                <?php if (!empty($order['ghiChuCOD'])): ?><dt>Ghi chú</dt><dd><?= e($order['ghiChuCOD']) ?></dd><?php endif; ?>
            </dl>
            <?php endif; ?>
            <form method="post" action="<?= e(url('admin-order-cod')) ?>" data-confirm="Xác nhận cập nhật đối soát COD?">
                <?= csrf_field() ?>
                <input type="hidden" name="id" value="<?= e($order['maDH']) ?>">
                <div class="field">
                    <label for="cod-status">Cập nhật trạng thái COD</label>
                    <select id="cod-status" name="cod_status" required>
                        <?php foreach (\App\Models\AdminModel::COD_STATUSES as $val => $lbl): ?>
                        <option value="<?= e($val) ?>" <?= ($order['trangThaiCOD'] ?? 'ChuaThu') === $val ? 'selected' : '' ?>><?= e($lbl) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="cod-note">Ghi chú đối soát</label>
                    <textarea id="cod-note" name="cod_note" rows="2" maxlength="500" placeholder="Mã phiếu thu, đơn vị giao vận, thời gian nhận tiền..."><?= e($order['ghiChuCOD'] ?? '') ?></textarea>
                </div>
                <button class="button button-outline full-width" type="submit">Lưu đối soát COD</button>
            </form>
        </section>

        <section class="admin-panel admin-form-panel">
            <h2>Xử lý tiến trình đơn hàng</h2>
            <?php if ($next): ?>
            <form method="post" action="<?= e(url('admin-order', ['id' => $order['maDH']])) ?>" data-confirm="Xác nhận cập nhật trạng thái đơn hàng?">
                <?= csrf_field() ?>
                <input type="hidden" name="expected" value="<?= e($order['trangThai']) ?>">
                <div class="field">
                    <label for="order-status">Trạng thái tiếp theo</label>
                    <select id="order-status" name="status" required>
                        <?php foreach ($next as $value): ?>
                        <option value="<?= e($value) ?>"><?= e(\App\Models\AdminModel::STATUSES[$value]) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label for="order-note">Ghi chú nội bộ / lý do hủy</label>
                    <textarea id="order-note" name="note" rows="3" maxlength="500"></textarea>
                </div>
                <p class="admin-hint">Xác nhận đơn sẽ trừ kho. Hủy đơn hoàn lại số lượng đã trừ bởi hệ thống và cần ghi rõ lý do.</p>
                <button class="button full-width" type="submit">Cập nhật trạng thái</button>
            </form>
            <?php else: ?>
            <p>Đơn đã hoàn tất hoặc có trạng thái kết thúc.</p>
            <?php endif; ?>
        </section>
    </aside>
</div>
