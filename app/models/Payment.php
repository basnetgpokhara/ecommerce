<?php
namespace App\Models;

use App\Core\Database;

/** payments — gateway transaction log */
class Payment extends \App\Core\Model
{
    protected static string $table = 'payments';

    public static function forOrder(int $orderId): array
    {
        return Database::fetchAll(
            'SELECT * FROM payments WHERE order_id = ? ORDER BY id',
            [$orderId]
        );
    }

    public static function forCustomer(int $userId): array
    {
        return Database::fetchAll(
            'SELECT p.* FROM payments p JOIN orders o ON o.id = p.order_id
             WHERE o.customer_id = ? ORDER BY p.id DESC',
            [$userId]
        );
    }
}
