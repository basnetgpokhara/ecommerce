<?php
namespace App\Models;

use App\Core\Database;

/**
 * Setting — config-driven site settings stored in the `settings` table.
 * Cached per request. Values are strings; cast at read sites as needed.
 */
class Setting extends \App\Core\Model
{
    protected static string $table = 'settings';
    private static ?array $cache = null;

    private static function load(): array
    {
        if (self::$cache === null) {
            self::$cache = [];
            try {
                foreach (Database::fetchAll('SELECT setting_key, setting_value FROM settings') as $row) {
                    self::$cache[$row['setting_key']] = $row['setting_value'];
                }
            } catch (\Throwable $e) {
                self::$cache = [];
            }
        }
        return self::$cache;
    }

    public static function get(string $key, $default = null)
    {
        $all = self::load();
        return array_key_exists($key, $all) ? $all[$key] : $default;
    }

    public static function set(string $key, $value): void
    {
        Database::query(
            'INSERT INTO settings (setting_key, setting_value) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
            [$key, (string) $value]
        );
        self::$cache = null;
    }

    public static function all(): array
    {
        return self::load();
    }

    /** Numeric helper (e.g. commission rate, booleans). */
    public static function getBool(string $key, bool $default = false): bool
    {
        $v = self::get($key);
        return in_array((string) $v, ['1', 'true', 'yes', 'on'], true);
    }
}
