<?php
namespace App\Models;

use App\Core\Database;

/**
 * Cart — operates on cart_items for a logged-in user (guests are sent to
 * login per the PRD permission matrix). All prices use effective price.
 */
class Cart
{
    public static function contents(int $userId): array
    {
        return Database::fetchAll(
            "SELECT ci.id AS cart_id, ci.product_id, ci.quantity,
                    p.name, p.slug, p.price, p.discount_price, p.stock, p.status,
                    s.shop_name, s.slug AS shop_slug,
                    IFNULL(p.discount_price, p.price) AS effective_price,
                    (SELECT pi.image_path FROM product_images pi
                       WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.sort_order, pi.id LIMIT 1) AS image
             FROM cart_items ci
             JOIN products p ON p.id = ci.product_id
             JOIN sellers s  ON s.id = p.seller_id
             WHERE ci.user_id = ? AND p.deleted_at IS NULL
             ORDER BY ci.id",
            [$userId]
        );
    }

    public static function add(int $userId, int $productId, int $qty = 1): void
    {
        $product = Product::find($productId);
        if (!$product || $product['deleted_at']) {
            return;
        }
        $max = max(1, (int) $product['stock']);
        $existing = Database::fetch(
            'SELECT id, quantity FROM cart_items WHERE user_id = ? AND product_id = ?',
            [$userId, $productId]
        );
        $newQty = ($existing ? (int) $existing['quantity'] : 0) + max(1, $qty);
        $newQty = min($newQty, $max);

        if ($existing) {
            Database::update('cart_items', ['quantity' => $newQty], ['id' => $existing['id']]);
        } else {
            Database::insert('cart_items', [
                'user_id'    => $userId,
                'product_id' => $productId,
                'quantity'   => $newQty,
            ]);
        }
    }

    public static function setQuantity(int $userId, int $cartId, int $qty): void
    {
        $row = Database::fetch(
            'SELECT ci.id, p.stock FROM cart_items ci JOIN products p ON p.id = ci.product_id WHERE ci.id = ? AND ci.user_id = ?',
            [$cartId, $userId]
        );
        if (!$row) {
            return;
        }
        $qty = max(1, min($qty, max(1, (int) $row['stock'])));
        Database::update('cart_items', ['quantity' => $qty], ['id' => $cartId]);
    }

    public static function remove(int $userId, int $cartId): void
    {
        Database::delete('cart_items', ['id' => $cartId, 'user_id' => $userId]);
    }

    public static function clear(int $userId): void
    {
        Database::delete('cart_items', ['user_id' => $userId]);
    }

    public static function count(int $userId): int
    {
        return (int) (Database::scalar(
            'SELECT COALESCE(SUM(quantity), 0) FROM cart_items WHERE user_id = ?',
            [$userId]
        ) ?? 0);
    }

    public static function subtotal(int $userId): float
    {
        $items = self::contents($userId);
        $sum = 0.0;
        foreach ($items as $item) {
            $sum += (float) $item['effective_price'] * (int) $item['quantity'];
        }
        return $sum;
    }

    /** Is every item in the cart actually buyable (approved + in stock)? */
    public static function isBuyable(int $userId): bool
    {
        foreach (self::contents($userId) as $item) {
            if ($item['status'] !== 'approved' || (int) $item['stock'] < 1) {
                return false;
            }
        }
        return true;
    }
}
