<?php
namespace App\Models;

use App\Core\Database;

/** orders */
class Order extends \App\Core\Model
{
    protected static string $table = 'orders';

    public static function findByNumber(string $number): ?array
    {
        return Database::fetch('SELECT * FROM orders WHERE order_number = ? LIMIT 1', [$number]);
    }

    public static function forCustomer(int $userId): array
    {
        return Database::fetchAll(
            'SELECT * FROM orders WHERE customer_id = ? ORDER BY id DESC',
            [$userId]
        );
    }

    public static function findOwned(int $id, int $userId): ?array
    {
        return Database::fetch(
            'SELECT * FROM orders WHERE id = ? AND customer_id = ? LIMIT 1',
            [$id, $userId]
        );
    }

    public static function items(int $orderId): array
    {
        return Database::fetchAll('SELECT * FROM order_items WHERE order_id = ? ORDER BY id', [$orderId]);
    }

    /** Quick status counts for the customer dashboard widgets. */
    public static function statusCounts(int $userId): array
    {
        $rows = Database::fetchAll(
            'SELECT status, COUNT(*) AS n FROM orders WHERE customer_id = ? GROUP BY status',
            [$userId]
        );
        $out = ['pending' => 0, 'delivered' => 0, 'cancelled' => 0, 'total' => 0];
        foreach ($rows as $r) {
            if (in_array($r['status'], ['placed', 'confirmed', 'shipped'], true)) {
                $out['pending'] += (int) $r['n'];
            } elseif ($r['status'] === 'delivered') {
                $out['delivered'] += (int) $r['n'];
            } elseif ($r['status'] === 'cancelled' || $r['status'] === 'refunded') {
                $out['cancelled'] += (int) $r['n'];
            }
            $out['total'] += (int) $r['n'];
        }
        return $out;
    }

    public static function generateNumber(): string
    {
        return 'NM-' . date('Ymd') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 6));
    }

    /** Admin: all orders with customer, filtered & paginated. */
    public static function forAdmin(array $f): array
    {
        $where = [];
        $params = [];
        if (!empty($f['q'])) {
            $where[] = '(o.order_number LIKE ? OR u.name LIKE ? OR u.email LIKE ?)';
            $like = '%' . $f['q'] . '%';
            array_push($params, $like, $like, $like);
        }
        if (!empty($f['status'])) {
            $where[] = 'o.status = ?';
            $params[] = $f['status'];
        }
        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';
        $perPage = max(1, min(100, (int) ($f['per_page'] ?? 15)));
        $page = max(1, (int) ($f['page'] ?? 1));
        $offset = ($page - 1) * $perPage;

        $total = (int) (Database::scalar(
            "SELECT COUNT(*) FROM orders o LEFT JOIN users u ON u.id = o.customer_id $whereSql",
            $params
        ) ?? 0);
        $rows = Database::fetchAll(
            "SELECT o.*, u.name AS customer_name, u.email AS customer_email
             FROM orders o LEFT JOIN users u ON u.id = o.customer_id
             $whereSql ORDER BY o.id DESC LIMIT $perPage OFFSET $offset",
            $params
        );
        return [
            'items' => $rows, 'total' => $total, 'page' => $page,
            'per_page' => $perPage, 'last_page' => (int) max(1, ceil($total / $perPage)),
        ];
    }
}
