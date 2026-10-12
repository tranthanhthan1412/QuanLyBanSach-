<?php
declare(strict_types=1);
namespace App\Core;

use PHPMailer\PHPMailer\PHPMailer;

final class OrderMailer
{
    public function send(array $order): string
    {
        try {
            $settings = require ROOT_PATH . '/config/mail.php';
            if (!$settings['enabled']) { return 'disabled'; }
            if (!in_array($settings['encryption'], ['tls', 'ssl', 'none'], true)
                || $settings['host'] === '' || $settings['port'] < 1 || $settings['port'] > 65535) {
                throw new \RuntimeException('Invalid mail configuration');
            }
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $settings['host'];
            $mail->Port = $settings['port'];
            $mail->SMTPAuth = $settings['username'] !== '';
            $mail->Username = $settings['username'];
            $mail->Password = $settings['password'];
            $mail->SMTPSecure = $settings['encryption'] === 'none' ? '' : $settings['encryption'];
            $mail->SMTPAutoTLS = $settings['encryption'] !== 'none';
            $mail->Timeout = 10;
            $mail->getSMTPInstance()->Timelimit = 15;
            $mail->SMTPDebug = 0;
            $mail->CharSet = PHPMailer::CHARSET_UTF8;
            $mail->setFrom($settings['from_email'] ?: $settings['username'], $settings['from_name']);
            $mail->addAddress($order['emailNhan'], $order['tenNguoiNhan']);
            $mail->Subject = 'BookStore - Đã nhận đơn hàng #' . $order['maDH'];
            $mail->isHTML(true);
            ob_start();
            try { require ROOT_PATH . '/view/emails/order.php'; $mail->Body = ob_get_contents(); }
            finally { ob_end_clean(); }
            $lines = [
                'BookStore - Đơn hàng #' . $order['maDH'],
                'Trạng thái: Chờ xác nhận. Thanh toán khi nhận hàng (COD).',
                'Người nhận: ' . $order['tenNguoiNhan'],
                'Điện thoại: ' . $order['sdtNhan'],
                'Địa chỉ: ' . $order['diaChiNhan'],
            ];
            foreach ($order['items'] as $item) {
                $lines[] = $item['tenSach'] . ' x ' . $item['soLuong'] . ': ' . money($item['tongTien']);
            }
            $lines[] = 'Tiền sách: ' . money($order['subtotal']);
            $lines[] = 'Phí vận chuyển: ' . money($order['phiVanChuyen']);
            $lines[] = 'Tổng thanh toán: ' . money($order['total']);
            $lines[] = 'Ghi chú: ' . ($order['ghiChu'] ?: 'Không có');
            $mail->AltBody = implode("\n", $lines);
            $mail->send();
            return 'sent';
        } catch (\Throwable $error) {
            // No credentials, email addresses or SMTP transcript in logs/responses.
            error_log('Order email failed for order #' . (int) $order['maDH'] . ' (' . get_class($error) . '). Check SMTP configuration.');
            return 'failed';
        }
    }
}
