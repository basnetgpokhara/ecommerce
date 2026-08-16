<?php
namespace App\Models;

use App\Core\Database;

/** addresses — shipping addresses for a user */
class Address extends \App\Core\Model
{
    protected static string $table = 'addresses';

    public static function forUser(int $userId): array
    {
        return Database::fetchAll(
            'SELECT * FROM addresses WHERE user_id = ? ORDER BY is_default DESC, id DESC',
            [$userId]
        );
    }

    public static function findOwned(int $id, int $userId): ?array
    {
        return Database::fetch(
            'SELECT * FROM addresses WHERE id = ? AND user_id = ? LIMIT 1',
            [$id, $userId]
        );
    }

    public static function defaultFor(int $userId): ?array
    {
        return Database::fetch(
            'SELECT * FROM addresses WHERE user_id = ? AND is_default = 1 LIMIT 1',
            [$userId]
        );
    }

    public static function clearDefault(int $userId): void
    {
        Database::query('UPDATE addresses SET is_default = 0 WHERE user_id = ?', [$userId]);
    }
}
