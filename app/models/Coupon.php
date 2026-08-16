<?php
namespace App\Models;

use App\Core\Database;

/** coupons — Phase 3 groundwork; read/validate helpers provided here. */
class Coupon extends \App\Core\Model
{
    protected static string $table = 'coupons';

    public static function findByCode(string $code): ?array
    {
        return Database::fetch('SELECT * FROM coupons WHERE code = ? LIMIT 1', [$code]);
    }

    public static function isValid(array $c, float $cartSubtotal): bool
    {
        if (!$c) {
            return false;
        }
        if ($c['status'] !== 'active') {
            return false;
        }
        if ($c['expiry'] && strtotime($c['expiry']) < time()) {
            return false;
        }
        if ($c['usage_limit'] !== null && (int) $c['used'] >= (int) $c['usage_limit']) {
            return false;
        }
        if ($c['min_order'] !== null && $cartSubtotal < (float) $c['min_order']) {
            return false;
        }
        return true;
    }

    public static function discountFor(array $c, float $cartSubtotal): float
    {
        if ($c['type'] === 'flat') {
            return min((float) $c['value'], $cartSubtotal);
        }
        return round($cartSubtotal * ((float) $c['value'] / 100), 2);
    }

    /** Mark one use of a coupon (called when an order using it is placed). */
    public static function incrementUsage(int $id): void
    {
        Database::query('UPDATE coupons SET used = used + 1 WHERE id = ?', [$id]);
    }
}
