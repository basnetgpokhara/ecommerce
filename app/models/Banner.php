<?php
namespace App\Models;

use App\Core\Database;

/** banners — homepage hero slides & promo strips */
class Banner extends \App\Core\Model
{
    protected static string $table = 'banners';

    public static function active(string $position = 'hero'): array
    {
        return Database::fetchAll(
            "SELECT * FROM banners WHERE status = 'active' AND position = ? ORDER BY sort_order, id",
            [$position]
        );
    }
}
