<?php
namespace App\Models;

use App\Core\Database;

/** order_items — per-line seller + commission accounting */
class OrderItem extends \App\Core\Model
{
    protected static string $table = 'order_items';

    public static function forOrder(int $orderId): array
    {
        return Database::fetchAll('SELECT * FROM order_items WHERE order_id = ? ORDER BY id', [$orderId]);
    }

    /** Items belonging to a seller (for the seller dashboard / packing slip). */
    public static function forSeller(int $sellerId): array
    {
        return Database::fetchAll(
            "SELECT oi.*, o.order_number, o.created_at AS ordered_at,
                    o.shipping_name, o.status AS order_status, o.payment_status
             FROM order_items oi
             JOIN orders o ON o.id = oi.order_id
             WHERE oi.seller_id = ?
             ORDER BY oi.created_at DESC",
            [$sellerId]
        );
    }

    public static function sellerEarnings(int $sellerId): float
    {
        return (float) (Database::scalar(
            "SELECT COALESCE(SUM(seller_earnings), 0) FROM order_items oi
             JOIN orders o ON o.id = oi.order_id
             WHERE oi.seller_id = ? AND o.status <> 'cancelled'",
            [$sellerId]
        ) ?? 0);
    }

    /** Orders that contain at least one of this seller's items, with totals. */
    public static function ordersForSeller(int $sellerId): array
    {
        return Database::fetchAll(
            "SELECT o.id, o.order_number, o.created_at, o.status, o.payment_status,
                    u.name AS customer_name,
                    SUM(oi.quantity) AS item_count, SUM(oi.seller_earnings) AS earnings
             FROM order_items oi
             JOIN orders o ON o.id = oi.order_id
             LEFT JOIN users u ON u.id = o.customer_id
             WHERE oi.seller_id = ?
             GROUP BY o.id
             ORDER BY o.id DESC",
            [$sellerId]
        );
    }

    public static function updateFulfillment(int $id, int $sellerId, string $status): void
    {
        Database::query(
            'UPDATE order_items SET fulfillment = ? WHERE id = ? AND seller_id = ?',
            [$status, $id, $sellerId]
        );
    }
}
