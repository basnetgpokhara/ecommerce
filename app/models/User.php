<?php
namespace App\Models;

use App\Core\Database;

/** users — customers, sellers, admins */
class User extends \App\Core\Model
{
    protected static string $table = 'users';

    public static function findByEmail(string $email): ?array
    {
        return Database::fetch('SELECT * FROM users WHERE email = ? LIMIT 1', [$email]);
    }

    public static function findByResetToken(string $token): ?array
    {
        return Database::fetch(
            'SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW() LIMIT 1',
            [$token]
        );
    }

    public static function countByRole(string $role): int
    {
        return self::count("role = ? AND status = 'active'", [$role]);
    }
}
