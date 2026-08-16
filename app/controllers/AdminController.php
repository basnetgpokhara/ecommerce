<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Models\Order;
use App\Models\Product;
use App\Models\Seller;
use App\Models\User;

class AdminController extends \App\Core\Controller
{
    public function __construct()
    {
        parent::__construct();
        Auth::requireRole('admin');
    }

    /** Admin dashboard home — site-wide KPIs (full management lands in Phase 2). */
    public function dashboard(): void
    {
        $kpis = [
            'sales'    => (float) (Database::scalar("SELECT COALESCE(SUM(total),0) FROM orders WHERE status <> 'cancelled'") ?? 0),
            'orders'   => (int) (Database::scalar("SELECT COUNT(*) FROM orders") ?? 0),
            'users'    => User::countByRole('customer'),
            'sellers'  => User::countByRole('seller'),
            'products' => (int) (Database::scalar("SELECT COUNT(*) FROM products WHERE deleted_at IS NULL") ?? 0),
        ];

        $recentOrders = Database::fetchAll(
            "SELECT o.*, u.name AS customer_name
             FROM orders o JOIN users u ON u.id = o.customer_id
             ORDER BY o.id DESC LIMIT 8"
        );

        $topProducts = Database::fetchAll(
            "SELECT oi.product_name, SUM(oi.quantity) AS qty, SUM(oi.subtotal) AS revenue
             FROM order_items oi JOIN orders o ON o.id = oi.order_id
             WHERE o.status <> 'cancelled'
             GROUP BY oi.product_id, oi.product_name
             ORDER BY revenue DESC LIMIT 5"
        );

        $this->view('admin/dashboard', compact('kpis', 'recentOrders', 'topProducts'), 'dashboard');
    }
}
