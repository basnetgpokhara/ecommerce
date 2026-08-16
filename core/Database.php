<?php
namespace App\Core;

/**
 * Database — singleton PDO connection (MySQL/MariaDB) plus convenience
 * helpers. Every query uses prepared statements; no string concatenation
 * of user input into SQL.
 */
class Database
{
    private static ?\PDO $pdo = null;

    public static function pdo(): \PDO
    {
        if (self::$pdo instanceof \PDO) {
            return self::$pdo;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST, DB_PORT, DB_NAME, DB_CHARSET
        );

        self::$pdo = new \PDO($dsn, DB_USER, DB_PASS, [
            \PDO::ATTR_ERRMODE            => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            \PDO::ATTR_EMULATE_PREPARES   => false,
        ]);

        return self::$pdo;
    }

    /** Prepare + execute and return the statement. */
    public static function query(string $sql, array $params = []): \PDOStatement
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /** Fetch a single row or null. */
    public static function fetch(string $sql, array $params = []): ?array
    {
        return self::query($sql, $params)->fetch() ?: null;
    }

    /** Fetch all rows. */
    public static function fetchAll(string $sql, array $params = []): array
    {
        return self::query($sql, $params)->fetchAll();
    }

    /** Fetch a single scalar (first column of first row). */
    public static function scalar(string $sql, array $params = [])
    {
        $row = self::query($sql, $params)->fetch(\PDO::FETCH_NUM);
        return $row[0] ?? null;
    }

    /** INSERT $data into $table; returns the new auto-increment id. */
    public static function insert(string $table, array $data): int
    {
        $cols = array_keys($data);
        $place = array_map(fn($c) => ':' . $c, $cols);
        $sql = 'INSERT INTO ' . $table
             . ' (' . implode(', ', $cols) . ')'
             . ' VALUES (' . implode(', ', $place) . ')';
        $params = [];
        foreach ($cols as $c) {
            $params[':' . $c] = $data[$c];
        }
        self::pdo()->prepare($sql)->execute($params);
        return (int) self::pdo()->lastInsertId();
    }

    /** UPDATE $table SET $data WHERE $where; returns affected row count. */
    public static function update(string $table, array $data, array $where): int
    {
        $set = implode(', ', array_map(fn($c) => $c . ' = :set_' . $c, array_keys($data)));
        $clause = implode(' AND ', array_map(fn($c) => $c . ' = :w_' . $c, array_keys($where)));
        $sql = 'UPDATE ' . $table . ' SET ' . $set . ' WHERE ' . $clause;

        $params = [];
        foreach ($data as $c => $v) {
            $params[':set_' . $c] = $v;
        }
        foreach ($where as $c => $v) {
            $params[':w_' . $c] = $v;
        }
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /** DELETE matching $where; returns affected row count. */
    public static function delete(string $table, array $where): int
    {
        $clause = implode(' AND ', array_map(fn($c) => $c . ' = :w_' . $c, array_keys($where)));
        $sql = 'DELETE FROM ' . $table . ' WHERE ' . $clause;
        $params = [];
        foreach ($where as $c => $v) {
            $params[':w_' . $c] = $v;
        }
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }
}
