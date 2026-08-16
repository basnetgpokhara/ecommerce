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

    /** Sellers awaiting admin approval. */
    public static function pending(): array
    {
        return Database::fetchAll(
            "SELECT s.*, u.name AS owner_name, u.email, u.phone
             FROM sellers s JOIN users u ON u.id = s.user_id
             WHERE s.status = 'pending' ORDER BY s.id DESC"
        );
    }

    /** All sellers with product counts + earnings, for the admin. */
    public static function allWithStats(): array
    {
        return Database::fetchAll(
            "SELECT s.*, u.name AS owner_name, u.email,
                    (SELECT COUNT(*) FROM products p WHERE p.seller_id = s.id AND p.deleted_at IS NULL) AS product_count,
                    (SELECT COALESCE(SUM(oi.seller_earnings), 0) FROM order_items oi
                       JOIN orders o ON o.id = oi.order_id
                       WHERE oi.seller_id = s.id AND o.status <> 'cancelled') AS earnings
             FROM sellers s JOIN users u ON u.id = s.user_id
             ORDER BY s.id DESC"
        );
    }

    public static function findWithUser(int $id): ?array
    {
        return Database::fetch(
            "SELECT s.*, u.email, u.name AS owner_name
             FROM sellers s JOIN users u ON u.id = s.user_id WHERE s.id = ?",
            [$id]
        );
    }

    public static function setStatus(int $id, string $status): void
    {
        Database::query('UPDATE sellers SET status = ? WHERE id = ?', [$status, $id]);
    }
}
