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
}
