<?php
namespace App\Models;

use App\Core\Database;

/** reviews */
class Review extends \App\Core\Model
{
    protected static string $table = 'reviews';

    public static function forProduct(int $productId): array
    {
        return Database::fetchAll(
            "SELECT r.*, u.name AS customer_name
             FROM reviews r JOIN users u ON u.id = r.customer_id
             WHERE r.product_id = ? AND r.status = 'approved'
             ORDER BY r.created_at DESC",
            [$productId]
        );
    }

    public static function average(int $productId): float
    {
        return (float) (Database::scalar(
            "SELECT COALESCE(AVG(rating), 0) FROM reviews WHERE product_id = ? AND status = 'approved'",
            [$productId]
        ) ?? 0);
    }

    /** Recompute cached aggregate rating on a product. */
    public static function recalcProduct(int $productId): void
    {
        $row = Database::fetch(
            "SELECT AVG(rating) AS avg, COUNT(*) AS n FROM reviews
             WHERE product_id = ? AND status = 'approved'",
            [$productId]
        );
        Database::update('products', [
            'rating_avg'   => round((float) ($row['avg'] ?? 0), 2),
            'rating_count' => (int) ($row['n'] ?? 0),
        ], ['id' => $productId]);
    }
}
