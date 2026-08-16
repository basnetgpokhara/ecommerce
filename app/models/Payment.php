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

    /** Most recent payment attempt for an order (used to guard double-marking). */
    public static function latestForOrder(int $orderId): ?array
    {
        return Database::fetch(
            'SELECT * FROM payments WHERE order_id = ? ORDER BY id DESC LIMIT 1',
            [$orderId]
        );
    }

    /** Record a confirmed, verified payment. */
    public static function recordSuccess(int $orderId, ?string $ref, ?string $raw): void
    {
        $latest = self::latestForOrder($orderId);
        if ($latest && $latest['status'] === 'success') {
            return; // already marked — idempotent
        }
        self::create([
            'order_id'        => $orderId,
            'gateway'         => $latest['gateway'] ?? 'cod',
            'transaction_ref' => $ref ?: null,
            'amount'          => $latest['amount'] ?? 0,
            'status'          => 'success',
            'raw_response'    => $raw ? mb_substr($raw, 0, 4000) : null,
        ]);
    }

    /** Record a failed / unverified payment attempt. */
    public static function recordFailure(int $orderId, ?string $raw): void
    {
        $latest = self::latestForOrder($orderId);
        if (!$latest) {
            return;
        }
        if ($latest['status'] === 'success') {
            return; // never downgrade a confirmed payment
        }
        self::updateById($latest['id'], [
            'status'       => 'failed',
            'raw_response' => $raw ? mb_substr($raw, 0, 4000) : null,
        ]);
    }
}
