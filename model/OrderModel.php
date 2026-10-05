<?php
declare(strict_types=1);

namespace App\Models;

final class OrderModel
{
    public function all(): array
    {
        $orders = require __DIR__ . '/data/orders.php';
        $products = array_column((new ProductModel())->all(), null, 'id');

        return array_map(function (array $order) use ($products): array {
            $order['subtotal'] = 0;
            foreach ($order['items'] as &$line) {
                $line['product'] = $products[$line['id']];
                $line['total'] = $line['product']['price'] * $line['qty'];
                $order['subtotal'] += $line['total'];
            }
            unset($line);
            $order['total'] = $order['subtotal'] + $order['shipping'];
            return $order;
        }, $orders);
    }

    public function find(string $id): ?array
    {
        foreach ($this->all() as $order) {
            if ($order['id'] === $id) {
                return $order;
            }
        }
        return null;
    }
}
