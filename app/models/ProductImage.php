<?php
namespace App\Models;

use App\Core\Database;

/** product_images */
class ProductImage extends \App\Core\Model
{
    protected static string $table = 'product_images';

    public static function forProduct(int $productId): array
    {
        return Database::fetchAll(
            'SELECT * FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order, id',
            [$productId]
        );
    }

    public static function primaryFor(int $productId): ?string
    {
        $row = Database::fetch(
            'SELECT image_path FROM product_images WHERE product_id = ? ORDER BY is_primary DESC, sort_order, id LIMIT 1',
            [$productId]
        );
        return $row ? $row['image_path'] : null;
    }

    /** Mark a single image as primary (and all others for the product as not). */
    public static function setPrimary(int $productId, int $imageId): void
    {
        Database::query(
            'UPDATE product_images SET is_primary = (id = ?) WHERE product_id = ?',
            [$imageId, $productId]
        );
    }

    public static function remove(int $imageId): void
    {
        Database::delete('product_images', ['id' => $imageId]);
    }
}
