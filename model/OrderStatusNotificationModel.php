<?php
declare(strict_types=1);
namespace App\Models;

use App\Core\OrderMailer;
use DomainException;
use PDO;
use Throwable;

final class OrderStatusNotificationModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = (new \Database())->getConnection();
    }

    public function forOrder(int $orderId): array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM thongbaotrangthaidon
             WHERE maDH = ? ORDER BY maTB DESC LIMIT 50'
        );
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }

    /**
     * Return: sent | failed | disabled | sending | not_allowed.
     * The DB claim is committed before SMTP. A retry never updates an order twice.
     * A timed-out 'sending' record is not automatically retried: SMTP may have
     * delivered the message before the PHP request failed.
     */
    public function deliver(int $notificationId, int $orderId): string
    {
        if ($notificationId <= 0 || $orderId <= 0) {
            throw new DomainException('Thông báo không hợp lệ.');
        }
        $stmt = $this->db->prepare(
            'SELECT * FROM thongbaotrangthaidon WHERE maTB = ? AND maDH = ?'
        );
        $stmt->execute([$notificationId, $orderId]);
        $notification = $stmt->fetch();
        if (!$notification) {
            throw new DomainException('Không tìm thấy thông báo thuộc đơn hàng này.');
        }
        if ($notification['trangThaiGui'] === 'sent') {
            return 'sent';
        }
        if ($notification['trangThaiGui'] === 'sending') {
            return 'sending';
        }

        $claim = $this->db->prepare(
            "UPDATE thongbaotrangthaidon
             SET trangThaiGui = 'sending', soLanGui = soLanGui + 1, ngayThu = NOW()
             WHERE maTB = ? AND maDH = ?
               AND trangThaiGui IN ('pending', 'failed', 'disabled')"
        );
        $claim->execute([$notificationId, $orderId]);
        if ($claim->rowCount() !== 1) {
            return 'not_allowed';
        }

        // SMTP happens AFTER the order update/notification transaction committed.
        // Mailing failures must not undo a customer's order status.
        try {
            $result = (new OrderMailer())->sendStatusChange($notification);
            if (!in_array($result, ['sent', 'failed', 'disabled'], true)) {
                $result = 'failed';
            }
        } catch (Throwable $error) {
            error_log('Status notification mailer error for order #' . $orderId . ' (' . get_class($error) . ').');
            $result = 'failed';
        }

        $finish = $this->db->prepare(
            'UPDATE thongbaotrangthaidon
             SET trangThaiGui = ?, ngayGui = CASE WHEN ? = \'sent\' THEN NOW() ELSE ngayGui END
             WHERE maTB = ? AND maDH = ? AND trangThaiGui = \'sending\''
        );
        $finish->execute([$result, $result, $notificationId, $orderId]);
        return $result;
    }
}
