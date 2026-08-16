<?php
namespace App\Core;

/**
 * Model — base active-record-ish model. Subclasses declare the table name.
 * Soft-delete is supported opt-in by filtering on `deleted_at IS NULL`.
 */
abstract class Model
{
    protected static string $table;
    protected static string $pk = 'id';

    public static function table(): string
    {
        return static::$table;
    }

    public static function find(int $id): ?array
    {
        return Database::fetch(
            'SELECT * FROM ' . static::$table . ' WHERE ' . static::$pk . ' = ?',
            [$id]
        );
    }

    public static function findBy(string $column, $value): ?array
    {
        return Database::fetch(
            'SELECT * FROM ' . static::$table . ' WHERE ' . $column . ' = ? LIMIT 1',
            [$value]
        );
    }

    public static function all(string $orderBy = ''): array
    {
        $sql = 'SELECT * FROM ' . static::$table;
        if ($orderBy !== '') {
            $sql .= ' ORDER BY ' . $orderBy;
        }
        return Database::fetchAll($sql);
    }

    public static function count(string $where = '', array $params = []): int
    {
        $sql = 'SELECT COUNT(*) FROM ' . static::$table;
        if ($where !== '') {
            $sql .= ' WHERE ' . $where;
        }
        return (int) (Database::scalar($sql, $params) ?? 0);
    }

    public static function create(array $data): int
    {
        return Database::insert(static::$table, $data);
    }

    public static function updateById(int $id, array $data): int
    {
        return Database::update(static::$table, $data, [static::$pk => $id]);
    }

    public static function deleteById(int $id): int
    {
        return Database::delete(static::$table, [static::$pk => $id]);
    }
}
