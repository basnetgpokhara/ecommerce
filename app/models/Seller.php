<?php
namespace App\Models;

use App\Core\Database;

/** sellers — one per user of role=seller */
class Seller extends \App\Core\Model
{
    protected static string $table = 'sellers';

    public static function findBySlug(string $slug): ?array
    {
        return Database::fetch('SELECT * FROM sellers WHERE slug = ? LIMIT 1', [$slug]);
    }

    public static function findByUserId(int $userId): ?array
    {
        return Database::fetch('SELECT * FROM sellers WHERE user_id = ? LIMIT 1', [$userId]);
    }

    /** Shops visible on the storefront. */
    public static function active(): array
    {
        return Database::fetchAll(
            "SELECT s.*, (SELECT COUNT(*) FROM products p WHERE p.seller_id = s.id AND p.status = 'approved' AND p.deleted_at IS NULL) AS product_count
             FROM sellers s
             WHERE s.status = 'active'
             ORDER BY s.shop_name"
        );
    }

    /** Lightweight stats for the admin/seller dashboards. */
    public static function productCount(int $sellerId): int
    {
        return (int) Database::scalar(
            "SELECT COUNT(*) FROM products WHERE seller_id = ? AND deleted_at IS NULL",
            [$sellerId]
        );
    }
}
