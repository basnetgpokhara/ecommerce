<?php
namespace App\Models;

use App\Core\Database;

/** products */
class Product extends \App\Core\Model
{
    protected static string $table = 'products';

    /** Effective price (discount when valid). */
    public static function effectivePrice(array $p): float
    {
        $d = isset($p['discount_price']) ? (float) $p['discount_price'] : 0.0;
        $price = (float) $p['price'];
        return ($d > 0 && $d < $price) ? $d : $price;
    }

    /** Discount percentage for badges (0-100). */
    public static function discountPercent(array $p): int
    {
        $d = isset($p['discount_price']) ? (float) $p['discount_price'] : 0.0;
        $price = (float) $p['price'];
        if ($price <= 0 || $d <= 0 || $d >= $price) {
            return 0;
        }
        return (int) round((($price - $d) / $price) * 100);
    }

    public static function findBySlug(string $slug): ?array
    {
        return Database::fetch(
            "SELECT p.*, s.shop_name, s.slug AS shop_slug, s.status AS shop_status
             FROM products p
             JOIN sellers s ON s.id = p.seller_id
             WHERE p.slug = ? AND p.deleted_at IS NULL
             LIMIT 1",
            [$slug]
        );
    }

    /** Featured products for the homepage. */
    public static function featured(int $limit = 8): array
    {
        return self::selectList(
            "p.is_featured = 1 AND p.status = 'approved'",
            [],
            'ORDER BY p.created_at DESC',
            $limit
        );
    }

    public static function newArrivals(int $limit = 8): array
    {
        return self::selectList(
            "p.status = 'approved'",
            [],
            'ORDER BY p.created_at DESC',
            $limit
        );
    }

    public static function deals(int $limit = 8): array
    {
        return self::selectList(
            "p.status = 'approved' AND p.discount_price IS NOT NULL AND p.discount_price > 0 AND p.discount_price < p.price",
            [],
            'ORDER BY (p.price - p.discount_price) DESC',
            $limit
        );
    }

    /** Related products (same category). */
    public static function related(int $productId, ?int $categoryId, int $limit = 4): array
    {
        if (!$categoryId) {
            return [];
        }
        return self::selectList(
            "p.category_id = ? AND p.id <> ? AND p.status = 'approved'",
            [$categoryId, $productId],
            'ORDER BY p.created_at DESC',
            $limit
        );
    }

    /** Products belonging to a seller (storefront view). */
    public static function bySeller(int $sellerId, int $limit = 50): array
    {
        return self::selectList(
            "p.seller_id = ? AND p.status = 'approved'",
            [$sellerId],
            'ORDER BY p.created_at DESC',
            $limit
        );
    }

    /**
     * Filtered/sorted/paginated storefront listing.
     * Supported filters: category (int), q (search), min, max, brand, sort.
     */
    public static function listing(array $f): array
    {
        $where = ["p.status = 'approved'"];
        $params = [];

        if (!empty($f['category'])) {
            $ids = Category::descendantIds((int) $f['category']);
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $where[] = "p.category_id IN ($placeholders)";
            foreach ($ids as $id) {
                $params[] = $id;
            }
        }
        if (!empty($f['q'])) {
            $where[] = '(p.name LIKE ? OR p.short_description LIKE ? OR p.brand LIKE ?)';
            $like = '%' . $f['q'] . '%';
            array_push($params, $like, $like, $like);
        }
        if (isset($f['min']) && $f['min'] !== '' && $f['min'] !== null) {
            $where[] = 'IFNULL(p.discount_price, p.price) >= ?';
            $params[] = (float) $f['min'];
        }
        if (isset($f['max']) && $f['max'] !== '' && $f['max'] !== null) {
            $where[] = 'IFNULL(p.discount_price, p.price) <= ?';
            $params[] = (float) $f['max'];
        }
        if (!empty($f['brand'])) {
            $where[] = 'p.brand = ?';
            $params[] = $f['brand'];
        }

        $whereSql = implode(' AND ', $where);

        $sortMap = [
            'popular'   => 'ORDER BY p.rating_count DESC, p.rating_avg DESC',
            'newest'    => 'ORDER BY p.created_at DESC',
            'price_asc' => 'ORDER BY effective_price ASC',
            'price_desc'=> 'ORDER BY effective_price DESC',
            'default'   => 'ORDER BY p.is_featured DESC, p.created_at DESC',
        ];
        $sort = $sortMap[$f['sort'] ?? 'default'] ?? $sortMap['default'];

        $perPage = max(1, min(60, (int) ($f['per_page'] ?? 12)));
        $page = max(1, (int) ($f['page'] ?? 1));
        $offset = ($page - 1) * $perPage;

        $total = (int) (Database::scalar(
            "SELECT COUNT(*) FROM products p WHERE $whereSql",
            $params
        ) ?? 0);

        $select = self::listSelect() . " FROM products p LEFT JOIN sellers s ON s.id = p.seller_id";
        $rows = Database::fetchAll(
            "$select WHERE $whereSql $sort LIMIT $perPage OFFSET $offset",
            $params
        );

        return [
            'items'      => $rows,
            'total'      => $total,
            'page'       => $page,
            'per_page'   => $perPage,
            'last_page'  => (int) max(1, ceil($total / $perPage)),
        ];
    }

    /** Shared SELECT columns including the computed effective price + primary image. */
    private static function listSelect(): string
    {
        return "SELECT p.*, s.shop_name, s.slug AS shop_slug,
                IFNULL(p.discount_price, p.price) AS effective_price,
                (SELECT pi.image_path FROM product_images pi
                   WHERE pi.product_id = p.id ORDER BY pi.is_primary DESC, pi.sort_order, pi.id LIMIT 1) AS image";
    }

    private static function selectList(string $where, array $params, string $order, int $limit): array
    {
        $sql = self::listSelect()
            . " FROM products p LEFT JOIN sellers s ON s.id = p.seller_id"
            . " WHERE $where $order LIMIT " . max(1, (int) $limit);
        return Database::fetchAll($sql, $params);
    }

    public static function decrementStock(int $id, int $qty): void
    {
        Database::query(
            'UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?',
            [$qty, $id, $qty]
        );
    }

    public static function brands(): array
    {
        return Database::fetchAll(
            "SELECT DISTINCT brand FROM products WHERE brand IS NOT NULL AND brand <> '' AND status='approved' ORDER BY brand"
        );
    }

    /** Generate a unique slug for a new product. */
    public static function uniqueSlug(string $name): string
    {
        $base = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $name), '-')) ?: 'product';
        $slug = $base;
        $i = 1;
        while (self::findBySlug($slug)) {
            $slug = $base . '-' . (++$i);
        }
        return $slug;
    }

    /** Find a product only if it belongs to the given seller. */
    public static function findForSeller(int $id, int $sellerId): ?array
    {
        return Database::fetch(
            'SELECT * FROM products WHERE id = ? AND seller_id = ? AND deleted_at IS NULL LIMIT 1',
            [$id, $sellerId]
        );
    }

    /** Seller's own products (with category name), optional search. */
    public static function forSeller(int $sellerId, ?string $q = null): array
    {
        $sql = "SELECT p.*, c.name AS category_name
                FROM products p LEFT JOIN categories c ON c.id = p.category_id
                WHERE p.seller_id = ? AND p.deleted_at IS NULL";
        $params = [$sellerId];
        if ($q !== null && $q !== '') {
            $sql .= " AND (p.name LIKE ? OR p.sku LIKE ?)";
            $params[] = '%' . $q . '%';
            $params[] = '%' . $q . '%';
        }
        $sql .= " ORDER BY p.id DESC";
        return Database::fetchAll($sql, $params);
    }

    /** Admin: all products with seller + category, filtered & paginated. */
    public static function adminListing(array $f): array
    {
        $where = ["p.deleted_at IS NULL"];
        $params = [];
        if (!empty($f['q'])) {
            $where[] = '(p.name LIKE ? OR p.sku LIKE ? OR s.shop_name LIKE ?)';
            $like = '%' . $f['q'] . '%';
            array_push($params, $like, $like, $like);
        }
        if (!empty($f['status'])) {
            $where[] = 'p.status = ?';
            $params[] = $f['status'];
        }
        if (!empty($f['seller_id'])) {
            $where[] = 'p.seller_id = ?';
            $params[] = (int) $f['seller_id'];
        }
        $whereSql = implode(' AND ', $where);
        $perPage = max(1, min(100, (int) ($f['per_page'] ?? 15)));
        $page = max(1, (int) ($f['page'] ?? 1));
        $offset = ($page - 1) * $perPage;

        $total = (int) (Database::scalar(
            "SELECT COUNT(*) FROM products p LEFT JOIN sellers s ON s.id = p.seller_id WHERE $whereSql",
            $params
        ) ?? 0);
        $rows = Database::fetchAll(
            "SELECT p.*, s.shop_name, c.name AS category_name
             FROM products p
             LEFT JOIN sellers s ON s.id = p.seller_id
             LEFT JOIN categories c ON c.id = p.category_id
             WHERE $whereSql ORDER BY p.id DESC LIMIT $perPage OFFSET $offset",
            $params
        );
        return [
            'items' => $rows, 'total' => $total, 'page' => $page,
            'per_page' => $perPage, 'last_page' => (int) max(1, ceil($total / $perPage)),
        ];
    }

    /** Soft delete (keeps historical order_items valid via SET NULL FK). */
    public static function softDelete(int $id): void
    {
        Database::query('UPDATE products SET deleted_at = NOW(), status = ? WHERE id = ?', ['inactive', $id]);
    }
}
