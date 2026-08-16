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

    /** Admin: searchable, role-filterable user list. */
    public static function forAdmin(?string $q = null, ?string $role = null): array
    {
        $where = [];
        $params = [];
        if ($q !== null && $q !== '') {
            $where[] = '(name LIKE ? OR email LIKE ? OR phone LIKE ?)';
            $like = '%' . $q . '%';
            array_push($params, $like, $like, $like);
        }
        if ($role && $role !== '') {
            $where[] = 'role = ?';
            $params[] = $role;
        }
        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        return Database::fetchAll("SELECT * FROM users $whereSql ORDER BY id DESC", $params);
    }
}
