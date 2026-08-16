<?php
namespace App\Models;

use App\Core\Database;

/** categories — self-referencing (parent_id) for sub-categories */
class Category extends \App\Core\Model
{
    protected static string $table = 'categories';

    public static function findBySlug(string $slug): ?array
    {
        return Database::fetch('SELECT * FROM categories WHERE slug = ? LIMIT 1', [$slug]);
    }

    /** Top-level categories (for the mega-menu & homepage grid). */
    public static function roots(): array
    {
        return Database::fetchAll(
            'SELECT * FROM categories WHERE parent_id IS NULL ORDER BY sort_order, name'
        );
    }

    public static function featured(): array
    {
        return Database::fetchAll(
            'SELECT * FROM categories WHERE is_featured = 1 ORDER BY sort_order, name'
        );
    }

    public static function children(int $parentId): array
    {
        return Database::fetchAll(
            'SELECT * FROM categories WHERE parent_id = ? ORDER BY sort_order, name',
            [$parentId]
        );
    }

    /** Tree for the navigation: roots with their children preloaded. */
    public static function tree(): array
    {
        $roots = self::roots();
        foreach ($roots as &$root) {
            $root['children'] = self::children((int) $root['id']);
        }
        unset($root);
        return $roots;
    }

    /** A category plus all descendant ids (for listing page scoping). */
    public static function descendantIds(int $categoryId): array
    {
        $ids = [$categoryId];
        $queue = [$categoryId];
        while ($queue) {
            $current = array_shift($queue);
            $rows = Database::fetchAll(
                'SELECT id FROM categories WHERE parent_id = ?',
                [$current]
            );
            foreach ($rows as $row) {
                $ids[] = (int) $row['id'];
                $queue[] = (int) $row['id'];
            }
        }
        return $ids;
    }

    /** Every category, parents first then children, for admin tables. */
    public static function allOrdered(): array
    {
        return Database::fetchAll(
            'SELECT * FROM categories ORDER BY COALESCE(parent_id, id), sort_order, name'
        );
    }

    public static function findBySlugOrCreate(string $name): int
    {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $name), '-'));
        $existing = self::findBySlug($slug);
        if ($existing) {
            return (int) $existing['id'];
        }
        return self::create(['name' => $name, 'slug' => $slug, 'sort_order' => 0]);
    }

    public static function hasChildren(int $id): bool
    {
        return (int) (Database::scalar('SELECT COUNT(*) FROM categories WHERE parent_id = ?', [$id]) ?? 0) > 0;
    }

    public static function hasProducts(int $id): bool
    {
        return (int) (Database::scalar('SELECT COUNT(*) FROM products WHERE category_id = ? AND deleted_at IS NULL', [$id]) ?? 0) > 0;
    }
}
