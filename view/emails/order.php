<!doctype html>
<html lang="vi"><head><meta charset="UTF-8"><title>Thông tin đơn hàng</title></head>
<body style="margin:0;padding:24px;background:#f5f3ed;font-family:Arial,sans-serif;color:#263c30">
<div style="max-width:640px;margin:auto;background:#fff;padding:28px;border-radius:12px">
    <h1 style="font-size:24px">BookStore · Đơn hàng #<?= e($order['maDH']) ?></h1>
    <p>Chào <?= e($order['tenNguoiNhan']) ?>, BookStore đã nhận đơn hàng của bạn.</p>
    <p><strong>Trạng thái:</strong> Chờ xác nhận<br><strong>Thanh toán:</strong> Khi nhận hàng (COD)</p>
    <h2 style="font-size:18px">Thông tin giao hàng</h2>
    <p><?= e($order['tenNguoiNhan']) ?> · <?= e($order['sdtNhan']) ?><br><?= e($order['diaChiNhan']) ?></p>
    <table style="width:100%;border-collapse:collapse" cellpadding="10">
        <thead><tr style="background:#eef3ee"><th align="left">Sách</th><th>Số lượng</th><th align="right">Thành tiền</th></tr></thead>
        <tbody><?php foreach ($order['items'] as $item): ?>
        <tr><td style="border-bottom:1px solid #ddd"><?= e($item['tenSach']) ?></td><td align="center"><?= (int) $item['soLuong'] ?></td><td align="right"><?= money($item['tongTien']) ?></td></tr>
        <?php endforeach; ?></tbody>
    </table>
    <p>Tiền sách: <strong><?= money($order['subtotal']) ?></strong><br>Phí vận chuyển: <strong><?= money($order['phiVanChuyen']) ?></strong></p>
    <p style="font-size:20px">Tổng thanh toán: <strong><?= money($order['total']) ?></strong></p>
    <?php if ($order['ghiChu']): ?><p>Ghi chú: <?= nl2br(e($order['ghiChu'])) ?></p><?php endif; ?>
    <p>Cảm ơn bạn đã mua sách tại BookStore. Bạn có thể theo dõi đơn trong mục Đơn hàng của tài khoản.</p>
</div></body></html>
