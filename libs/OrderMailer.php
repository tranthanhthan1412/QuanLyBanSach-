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

    /** Send a status-change notice without changing order confirmation mail. */
    public function sendStatusChange(array $notice): string
    {
        try {
            $settings = require ROOT_PATH . '/config/mail.php';
            if (!$settings['enabled']) { return 'disabled'; }
            if (!in_array($settings['encryption'], ['tls', 'ssl', 'none'], true)
                || $settings['host'] === '' || $settings['port'] < 1 || $settings['port'] > 65535) {
                throw new \RuntimeException('Invalid mail configuration');
            }
            if (!filter_var($notice['emailNhan'] ?? '', FILTER_VALIDATE_EMAIL)) {
                throw new \RuntimeException('Invalid recipient email');
            }

            $labels = [
                'ChoXacNhan' => 'Chờ xác nhận', 'DaXacNhan' => 'Đã xác nhận',
                'DangGiao' => 'Đang giao', 'DaGiao' => 'Đã giao', 'DaHuy' => 'Đã hủy',
            ];
            $status = $notice['trangThaiMoi'];
            if (!isset($labels[$status])) {
                throw new \RuntimeException('Invalid order status');
            }
            $orderId = (int) $notice['maDH'];
            $recipient = (string) $notice['tenNguoiNhan'];
            $reason = trim((string) ($notice['lyDo'] ?? ''));
            $message = match ($status) {
                'DaXacNhan' => 'Đơn hàng đã được cửa hàng xác nhận.',
                'DangGiao' => 'Đơn hàng đang được giao đến bạn.',
                'DaGiao' => 'Đơn hàng đã được đánh dấu là giao thành công.',
                'DaHuy' => 'Đơn hàng đã bị hủy.',
                default => 'Đơn hàng đã được cập nhật.',
            };
            $subject = 'BookStore - ' . $labels[$status] . ' - Đơn hàng #' . $orderId;

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
            $mail->addAddress($notice['emailNhan'], $recipient);
            $mail->Subject = $subject;
            $escape = static fn (string $value): string => htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $mail->isHTML(true);
            $body = '<!doctype html><html lang="vi"><head><meta charset="utf-8"></head><body>'
                . '<h2>BookStore - Cập nhật đơn hàng #' . $orderId . '</h2>'
                . '<p>Xin chào ' . $escape($recipient) . ',</p>'
                . '<p>' . $escape($message) . '</p>'
                . '<p>Trạng thái mới: <strong>' . $escape($labels[$status]) . '</strong></p>';
            if ($status === 'DaHuy' && $reason !== '') {
                $body .= '<p>Lý do hủy: ' . nl2br($escape($reason)) . '</p>';
            }
            $mail->Body = $body . '<p>Vui lòng đăng nhập website BookStore để xem chi tiết đơn hàng.</p></body></html>';
            $lines = [
                'BookStore - Cập nhật đơn hàng #' . $orderId,
                'Xin chào ' . $recipient . ',',
                $message,
                'Trạng thái mới: ' . $labels[$status],
            ];
            if ($status === 'DaHuy' && $reason !== '') { $lines[] = 'Lý do hủy: ' . $reason; }
            $lines[] = 'Vui lòng đăng nhập website BookStore để xem chi tiết đơn hàng.';
            $mail->AltBody = implode("\n", $lines);
            $mail->send();
            return 'sent';
        } catch (\Throwable $error) {
            error_log('Order status email failed for order #' . (int) ($notice['maDH'] ?? 0)
                . ' (' . get_class($error) . '). Check SMTP configuration.');
            return 'failed';
        }
    }
}
