<?php
// Include this file at the bottom of view/admin/order.php.
$noticeRows = $statusNotifications ?? [];
$mailLabels = [
    'sent' => 'Đã gửi', 'failed' => 'Gửi lỗi', 'disabled' => 'Chưa bật SMTP',
    'pending' => 'Chờ gửi', 'sending' => 'Đang gửi / chưa rõ kết quả',
];
?>
<section class="admin-panel" style="margin-top: 24px; padding: 20px;">
    <h2>Lịch sử thông báo trạng thái đơn hàng</h2>
    <p class="muted">Email được gửi sau khi lưu trạng thái. Lỗi email không hoàn tác đơn hàng.</p>
    <div class="admin-table-wrap"><table class="admin-table">
        <thead><tr><th>Thời gian</th><th>Trạng thái mới</th><th>Ghi chú</th><th>Email</th><th>Thao tác</th></tr></thead>
        <tbody>
        <?php foreach ($noticeRows as $notice): ?>
            <tr>
                <td><?= e($notice['ngayTao']) ?></td>
                <td><?= e(\App\Models\AdminModel::STATUSES[$notice['trangThaiMoi']] ?? $notice['trangThaiMoi']) ?></td>
                <td><?= e($notice['lyDo'] ?: '—') ?></td>
                <td><strong><?= e($mailLabels[$notice['trangThaiGui']] ?? $notice['trangThaiGui']) ?></strong><br>
                    <small><?= (int) $notice['soLanGui'] ?> lần thử</small></td>
                <td>
                <?php if (in_array($notice['trangThaiGui'], ['pending', 'failed', 'disabled'], true)): ?>
                    <form method="post" action="<?= e(url('admin-order', ['id' => $order['maDH']])) ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="notification_action" value="retry">
                        <input type="hidden" name="notification_id" value="<?= (int) $notice['maTB'] ?>">
                        <button type="submit" class="button button-outline">Gửi email lại</button>
                    </form>
                <?php elseif ($notice['trangThaiGui'] === 'sending'): ?>
                    <small>Đang xử lý hoặc không rõ SMTP đã nhận mail; kiểm tra log trước khi gửi lại.</small>
                <?php else: ?>—<?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (!$noticeRows): ?>
            <tr><td colspan="5">Đơn hàng chưa có lịch sử thông báo trạng thái mới.</td></tr>
        <?php endif; ?>
        </tbody>
    </table></div>
</section>
