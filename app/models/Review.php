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

    /** Create a review (one per product/customer — duplicates are ignored). */
    public static function createReview(int $productId, int $customerId, int $rating, ?string $comment): void
    {
        $status = setting('review_auto_approve', '1') === '1' ? 'approved' : 'pending';
        try {
            Database::insert('reviews', [
                'product_id'  => $productId,
                'customer_id' => $customerId,
                'rating'      => $rating,
                'comment'     => ($comment !== '' ? $comment : null),
                'status'      => $status,
            ]);
            self::recalcProduct($productId);
        } catch (\PDOException $e) {
            // Duplicate (product_id, customer_id) — silently ignore.
        }
    }

    public static function forSeller(int $sellerId): array
    {
        return Database::fetchAll(
            "SELECT r.*, p.name AS product_name, p.slug AS product_slug, u.name AS customer_name
             FROM reviews r
             JOIN products p ON p.id = r.product_id
             JOIN users u ON u.id = r.customer_id
             WHERE p.seller_id = ?
             ORDER BY r.id DESC",
            [$sellerId]
        );
    }

    public static function forAdmin(array $f): array
    {
        $where = [];
        $params = [];
        if (!empty($f['status'])) {
            $where[] = 'r.status = ?';
            $params[] = $f['status'];
        }
        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        return Database::fetchAll(
            "SELECT r.*, p.name AS product_name, u.name AS customer_name
             FROM reviews r
             JOIN products p ON p.id = r.product_id
             JOIN users u ON u.id = r.customer_id
             $whereSql ORDER BY r.id DESC",
            $params
        );
    }

    public static function setStatus(int $id, string $status): void
    {
        Database::query('UPDATE reviews SET status = ? WHERE id = ?', [$status, $id]);
        $r = Database::fetch('SELECT product_id FROM reviews WHERE id = ?', [$id]);
        if ($r) {
            self::recalcProduct((int) $r['product_id']);
        }
    }

    public static function findOwned(int $id, int $customerId): ?array
    {
        return Database::fetch('SELECT * FROM reviews WHERE id = ? AND customer_id = ?', [$id, $customerId]);
    }
}
