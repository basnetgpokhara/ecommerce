<?php
namespace App\Controllers;

use App\Core\Auth;
use App\Core\Database;
use App\Core\Upload;
use App\Models\AuditLog;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Page;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Review;
use App\Models\Seller;
use App\Models\Setting;
use App\Models\User;

class AdminController extends \App\Core\Controller
{
    public function __construct()
    {
        parent::__construct();
        Auth::requireRole('admin');
    }

    // ------------------------------------------------------------------
    //  Dashboard
    // ------------------------------------------------------------------
    public function dashboard(): void
    {
        $kpis = [
            'sales'   => (float) (Database::scalar("SELECT COALESCE(SUM(total),0) FROM orders WHERE status <> 'cancelled'") ?? 0),
            'orders'  => (int) (Database::scalar("SELECT COUNT(*) FROM orders") ?? 0),
            'users'   => User::countByRole('customer'),
            'sellers' => User::countByRole('seller'),
            'products'=> (int) (Database::scalar("SELECT COUNT(*) FROM products WHERE deleted_at IS NULL") ?? 0),
        ];
        $recentOrders = Database::fetchAll(
            "SELECT o.*, u.name AS customer_name FROM orders o JOIN users u ON u.id = o.customer_id ORDER BY o.id DESC LIMIT 8"
        );
        $topProducts = Database::fetchAll(
            "SELECT oi.product_name, SUM(oi.quantity) AS qty, SUM(oi.subtotal) AS revenue
             FROM order_items oi JOIN orders o ON o.id = oi.order_id
             WHERE o.status <> 'cancelled'
             GROUP BY oi.product_id, oi.product_name ORDER BY revenue DESC LIMIT 5"
        );
        $pendingApprovals = (int) (Database::scalar("SELECT COUNT(*) FROM products WHERE status='pending' AND deleted_at IS NULL") ?? 0);
        $pendingSellers = (int) (Database::scalar("SELECT COUNT(*) FROM sellers WHERE status='pending'") ?? 0);

        $this->view('admin/dashboard', compact('kpis', 'recentOrders', 'topProducts', 'pendingApprovals', 'pendingSellers'), 'dashboard');
    }

    // ------------------------------------------------------------------
    //  Users
    // ------------------------------------------------------------------
    public function users(): void
    {
        $q = $this->request->query('q', '');
        $role = $this->request->query('role', '');
        $users = User::forAdmin($q, $role, ['page' => $this->request->query('page', 1)]);
        $this->view('admin/users/index', compact('users', 'q', 'role'), 'dashboard');
    }

    public function userStatus(int $id): void
    {
        $user = User::find($id);
        if (!$user) { $this->abort(404); }
        if ($user['id'] == Auth::id()) {
            $this->error("You cannot change your own status.", '/admin/users');
        }
        $new = $user['status'] === 'active' ? 'blocked' : 'active';
        User::updateById($id, ['status' => $new]);
        AuditLog::record(Auth::id(), 'admin.user.status', "{$user['email']} -> {$new}");
        $this->success('User ' . ($new === 'active' ? 'unblocked' : 'blocked') . '.', '/admin/users');
    }

    public function userDelete(int $id): void
    {
        if ($id == Auth::id()) {
            $this->error("You cannot delete your own account.", '/admin/users');
        }
        $user = User::find($id);
        if (!$user) { $this->abort(404); }
        try {
            Database::delete('users', ['id' => $id]);
            AuditLog::record(Auth::id(), 'admin.user.delete', "{$user['email']}");
            $this->success('User deleted.', '/admin/users');
        } catch (\Throwable $e) {
            $this->error('Cannot delete this user (they have orders/records). Block them instead.', '/admin/users');
        }
    }

    // ------------------------------------------------------------------
    //  Sellers
    // ------------------------------------------------------------------
    public function sellers(): void
    {
        $pending = Seller::pending();
        $sellers = Seller::adminListing(['page' => $this->request->query('page', 1)]);
        $this->view('admin/sellers/index', compact('pending', 'sellers'), 'dashboard');
    }

    public function sellerApprove(int $id): void
    {
        Seller::setStatus($id, 'active');
        AuditLog::record(Auth::id(), 'admin.seller.approve', "Seller #{$id}");
        $this->success('Seller approved & shop is now live.', '/admin/sellers');
    }

    public function sellerSuspend(int $id): void
    {
        Seller::setStatus($id, 'suspended');
        AuditLog::record(Auth::id(), 'admin.seller.suspend', "Seller #{$id}");
        $this->success('Seller suspended.', '/admin/sellers');
    }

    public function sellerEdit(int $id): void
    {
        $seller = Seller::findWithUser($id);
        if (!$seller) { $this->abort(404); }
        $this->view('admin/sellers/edit', compact('seller'), 'dashboard');
    }

    public function sellerUpdate(int $id): void
    {
        $seller = Seller::findWithUser($id);
        if (!$seller) { $this->abort(404); }
        $data = $this->request->only(['shop_name', 'commission_rate', 'status', 'description', 'contact_phone', 'contact_email']);
        $errors = $this->validate($data, [
            'shop_name'       => 'required|min:2|max:150',
            'commission_rate' => 'required|numeric',
            'status'          => 'required|in:active,pending,suspended',
        ]);
        if ($errors) { $this->failWith('/admin/sellers/' . $id . '/edit', $errors); }
        Seller::updateById($id, [
            'shop_name'       => trim($data['shop_name']),
            'commission_rate' => (float) $data['commission_rate'],
            'status'          => $data['status'],
            'description'     => $data['description'] ?: null,
            'contact_phone'   => $data['contact_phone'] ?: null,
            'contact_email'   => $data['contact_email'] ?: null,
        ]);
        AuditLog::record(Auth::id(), 'admin.seller.update', "Seller #{$id}");
        $this->success('Seller updated.', '/admin/sellers');
    }

    // ------------------------------------------------------------------
    //  Products
    // ------------------------------------------------------------------
    public function products(): void
    {
        $listing = Product::adminListing([
            'q'         => $this->request->query('q', ''),
            'status'    => $this->request->query('status', ''),
            'seller_id' => $this->request->query('seller_id', ''),
            'page'      => $this->request->query('page', 1),
        ]);
        $sellers = Seller::allWithStats();
        $this->view('admin/products/index', compact('listing', 'sellers'), 'dashboard');
    }

    public function productApprove(int $id): void
    {
        Product::updateById($id, ['status' => 'approved']);
        AuditLog::record(Auth::id(), 'admin.product.approve', "Product #{$id}");
        $this->success('Product approved & published.', '/admin/products');
    }

    public function productReject(int $id): void
    {
        Product::updateById($id, ['status' => 'rejected']);
        AuditLog::record(Auth::id(), 'admin.product.reject', "Product #{$id}");
        $this->success('Product rejected.', '/admin/products');
    }

    public function productFeature(int $id): void
    {
        $p = Product::find($id);
        if (!$p) { $this->abort(404); }
        Product::updateById($id, ['is_featured' => $p['is_featured'] ? 0 : 1]);
        $this->success('Featured status updated.', '/admin/products');
    }

    public function productDelete(int $id): void
    {
        Product::softDelete($id);
        AuditLog::record(Auth::id(), 'admin.product.delete', "Product #{$id}");
        $this->success('Product removed.', '/admin/products');
    }

    // ------------------------------------------------------------------
    //  Categories
    // ------------------------------------------------------------------
    public function categories(): void
    {
        $categories = Category::allOrdered();
        $this->view('admin/categories/index', compact('categories'), 'dashboard');
    }

    public function categoryStore(): void
    {
        $data = $this->request->only(['name', 'parent_id', 'sort_order', 'icon']);
        $errors = $this->validate($data, ['name' => 'required|min:2|max:150']);
        if ($errors) { $this->failWith('/admin/categories', $errors); }
        Category::create([
            'name'       => trim($data['name']),
            'slug'       => $this->categorySlug($data['name']),
            'parent_id'  => $data['parent_id'] ? (int) $data['parent_id'] : null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'icon'       => $data['icon'] ?: null,
            'is_featured'=> 0,
        ]);
        AuditLog::record(Auth::id(), 'admin.category.create', $data['name']);
        $this->success('Category created.', '/admin/categories');
    }

    public function categoryUpdate(int $id): void
    {
        $cat = Category::find($id);
        if (!$cat) { $this->abort(404); }
        $data = $this->request->only(['name', 'parent_id', 'sort_order', 'icon']);
        $errors = $this->validate($data, ['name' => 'required|min:2|max:150']);
        if ($errors) { $this->failWith('/admin/categories', $errors); }
        $parentId = $data['parent_id'] ? (int) $data['parent_id'] : null;
        if ($parentId === $id) { $parentId = null; } // a category can't be its own parent
        Category::updateById($id, [
            'name'       => trim($data['name']),
            'slug'       => $this->categorySlug($data['name'], $id),
            'parent_id'  => $parentId,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'icon'       => $data['icon'] ?: null,
        ]);
        AuditLog::record(Auth::id(), 'admin.category.update', "Category #{$id}");
        $this->success('Category updated.', '/admin/categories');
    }

    public function categoryDelete(int $id): void
    {
        if (Category::hasChildren($id)) {
            $this->error('Delete the sub-categories first.', '/admin/categories');
        }
        if (Category::hasProducts($id)) {
            $this->error('This category still has products. Move or remove them first.', '/admin/categories');
        }
        Category::deleteById($id);
        AuditLog::record(Auth::id(), 'admin.category.delete', "Category #{$id}");
        $this->success('Category deleted.', '/admin/categories');
    }

    private function categorySlug(string $name, ?int $exclude = null): string
    {
        $base = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $name), '-')) ?: 'category';
        $slug = $base;
        $i = 1;
        while (true) {
            $sql = 'SELECT id FROM categories WHERE slug = ?';
            $params = [$slug];
            if ($exclude) { $sql .= ' AND id <> ?'; $params[] = $exclude; }
            if (!Database::fetch($sql, $params)) { break; }
            $slug = $base . '-' . (++$i);
        }
        return $slug;
    }

    // ------------------------------------------------------------------
    //  Orders
    // ------------------------------------------------------------------
    public function orders(): void
    {
        $listing = Order::forAdmin([
            'q'      => $this->request->query('q', ''),
            'status' => $this->request->query('status', ''),
            'page'   => $this->request->query('page', 1),
        ]);
        $this->view('admin/orders/index', compact('listing'), 'dashboard');
    }

    public function order(int $id): void
    {
        $order = Order::find($id);
        if (!$order) { $this->abort(404); }
        $items    = OrderItem::forOrder($id);
        $payments = Payment::forOrder($id);
        $customer = User::find((int) $order['customer_id']);
        $this->view('admin/orders/detail', compact('order', 'items', 'payments', 'customer'), 'dashboard');
    }

    public function orderStatus(int $id): void
    {
        $order = Order::find($id);
        if (!$order) { $this->abort(404); }
        $status = (string) $this->request->input('status', 'placed');
        if (!in_array($status, ['placed', 'confirmed', 'shipped', 'delivered', 'cancelled', 'refunded'], true)) {
            $status = 'placed';
        }
        Order::updateById($id, ['status' => $status]);
        AuditLog::record(Auth::id(), 'admin.order.status', "Order #{$id} -> {$status}");
        $this->success('Order status updated.', '/admin/orders/' . $id);
    }

    // ------------------------------------------------------------------
    //  Reviews (moderation)
    // ------------------------------------------------------------------
    public function reviews(): void
    {
        $reviews = Review::forAdmin([
            'status' => $this->request->query('status', ''),
            'page'   => $this->request->query('page', 1),
        ]);
        $this->view('admin/reviews/index', compact('reviews'), 'dashboard');
    }

    public function reviewSet(int $id, string $status): void
    {
        if (!in_array($status, ['approved', 'rejected', 'pending'], true)) {
            $this->abort(404);
        }
        Review::setStatus($id, $status);
        AuditLog::record(Auth::id(), 'admin.review.' . $status, "Review #{$id}");
        $this->success('Review ' . $status . '.', '/admin/reviews');
    }

    // ------------------------------------------------------------------
    //  Banners (homepage CMS)
    // ------------------------------------------------------------------
    public function banners(): void
    {
        $banners = Banner::all('sort_order, id');
        $this->view('admin/banners/index', compact('banners'), 'dashboard');
    }

    public function bannerStore(): void
    {
        $data = $this->request->only(['title', 'subtitle', 'link', 'button_text', 'position', 'sort_order', 'status']);
        $errors = $this->validate($data, ['position' => 'required|in:hero,promo']);
        if ($errors) { $this->failWith('/admin/banners', $errors); }
        try {
            $image = Upload::image($this->request->file('image'), 'banners');
        } catch (\Throwable $e) {
            \App\Core\Session::setFlash('errors', ['image' => $e->getMessage()]);
            redirect('/admin/banners');
        }
        Banner::create([
            'title'       => $data['title'] ?: null,
            'subtitle'    => $data['subtitle'] ?: null,
            'image'       => $image,
            'link'        => $data['link'] ?: null,
            'button_text' => $data['button_text'] ?: null,
            'position'    => $data['position'],
            'sort_order'  => (int) ($data['sort_order'] ?? 0),
            'status'      => $data['status'] ?? 'active',
        ]);
        AuditLog::record(Auth::id(), 'admin.banner.create', (string) ($data['title'] ?? ''));
        $this->success('Banner added.', '/admin/banners');
    }

    public function bannerDelete(int $id): void
    {
        Banner::deleteById($id);
        AuditLog::record(Auth::id(), 'admin.banner.delete', "Banner #{$id}");
        $this->success('Banner deleted.', '/admin/banners');
    }

    // ------------------------------------------------------------------
    //  Pages (static CMS)
    // ------------------------------------------------------------------
    public function pages(): void
    {
        $pages = Page::all('title');
        $this->view('admin/pages/index', compact('pages'), 'dashboard');
    }

    public function pageStore(): void
    {
        $data = $this->request->only(['title', 'slug', 'body', 'status']);
        $errors = $this->validate($data, ['title' => 'required|min:2|max:200', 'body' => 'required']);
        if ($errors) { $this->failWith('/admin/pages', $errors); }
        $slug = ($data['slug'] ?: $this->pageSlug($data['title']));
        Page::create([
            'title'  => trim($data['title']),
            'slug'   => $slug,
            'body'   => $data['body'],
            'status' => $data['status'] ?? 'published',
        ]);
        AuditLog::record(Auth::id(), 'admin.page.create', $data['title']);
        $this->success('Page created.', '/admin/pages');
    }

    public function pageUpdate(int $id): void
    {
        $page = Page::find($id);
        if (!$page) { $this->abort(404); }
        $data = $this->request->only(['title', 'slug', 'body', 'status']);
        $errors = $this->validate($data, ['title' => 'required|min:2|max:200', 'body' => 'required']);
        if ($errors) { $this->failWith('/admin/pages', $errors); }
        Page::updateById($id, [
            'title'  => trim($data['title']),
            'slug'   => $data['slug'] ?: $this->pageSlug($data['title'], $id),
            'body'   => $data['body'],
            'status' => $data['status'] ?? 'published',
        ]);
        AuditLog::record(Auth::id(), 'admin.page.update', "Page #{$id}");
        $this->success('Page updated.', '/admin/pages');
    }

    public function pageDelete(int $id): void
    {
        Page::deleteById($id);
        AuditLog::record(Auth::id(), 'admin.page.delete', "Page #{$id}");
        $this->success('Page deleted.', '/admin/pages');
    }

    private function pageSlug(string $name, ?int $exclude = null): string
    {
        $base = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', $name), '-')) ?: 'page';
        $slug = $base;
        $i = 1;
        while (true) {
            $sql = 'SELECT id FROM pages WHERE slug = ?';
            $params = [$slug];
            if ($exclude) { $sql .= ' AND id <> ?'; $params[] = $exclude; }
            if (!Database::fetch($sql, $params)) { break; }
            $slug = $base . '-' . (++$i);
        }
        return $slug;
    }

    // ------------------------------------------------------------------
    //  Coupons (Phase 3)
    // ------------------------------------------------------------------
    public function coupons(): void
    {
        $coupons = Coupon::all('id DESC');
        $this->view('admin/coupons/index', compact('coupons'), 'dashboard');
    }

    public function couponStore(): void
    {
        $data = $this->request->only(['code', 'type', 'value', 'min_order', 'expiry', 'usage_limit', 'status']);
        $errors = $this->validate($data, [
            'code'  => 'required|min:2|max:60',
            'type'  => 'required|in:percentage,flat',
            'value' => 'required|numeric',
        ]);
        if ($errors) { $this->failWith('/admin/coupons', $errors); }

        $code = strtoupper(trim($data['code']));
        if (Coupon::findByCode($code)) {
            $this->error('That coupon code already exists.', '/admin/coupons');
        }

        Coupon::create([
            'code'        => $code,
            'type'        => $data['type'],
            'value'       => (float) $data['value'],
            'min_order'   => $data['min_order'] !== '' ? (float) $data['min_order'] : null,
            'expiry'      => $data['expiry'] ?: null,
            'usage_limit' => $data['usage_limit'] !== '' ? (int) $data['usage_limit'] : null,
            'status'      => $data['status'] ?? 'active',
        ]);
        AuditLog::record(Auth::id(), 'admin.coupon.create', $code);
        $this->success("Coupon {$code} created.", '/admin/coupons');
    }

    public function couponUpdate(int $id): void
    {
        $coupon = Coupon::find($id);
        if (!$coupon) { $this->abort(404); }
        $data = $this->request->only(['type', 'value', 'min_order', 'expiry', 'usage_limit', 'status']);
        $errors = $this->validate($data, [
            'type'  => 'required|in:percentage,flat',
            'value' => 'required|numeric',
        ]);
        if ($errors) { $this->failWith('/admin/coupons', $errors); }

        Coupon::updateById($id, [
            'type'        => $data['type'],
            'value'       => (float) $data['value'],
            'min_order'   => $data['min_order'] !== '' ? (float) $data['min_order'] : null,
            'expiry'      => $data['expiry'] ?: null,
            'usage_limit' => $data['usage_limit'] !== '' ? (int) $data['usage_limit'] : null,
            'status'      => $data['status'] ?? 'active',
        ]);
        AuditLog::record(Auth::id(), 'admin.coupon.update', "Coupon #{$id}");
        $this->success('Coupon updated.', '/admin/coupons');
    }

    public function couponDelete(int $id): void
    {
        $coupon = Coupon::find($id);
        if (!$coupon) { $this->abort(404); }
        Coupon::deleteById($id);
        AuditLog::record(Auth::id(), 'admin.coupon.delete', $coupon['code']);
        $this->success('Coupon deleted.', '/admin/coupons');
    }

    // ------------------------------------------------------------------
    //  Analytics & reports (Phase 4)
    // ------------------------------------------------------------------
    public function analytics(): void
    {
        $days   = max(7, min(90, (int) $this->request->query('days', 30)));
        $from   = date('Y-m-d', strtotime("-$days days"));

        // Daily revenue + order counts for the last N days.
        $daily = Database::fetchAll(
            "SELECT DATE(o.created_at) AS d,
                    COALESCE(SUM(o.total),0) AS revenue,
                    COUNT(*) AS orders
             FROM orders o
             WHERE o.created_at >= ? AND o.status <> 'cancelled'
             GROUP BY DATE(o.created_at) ORDER BY d ASC",
            [$from . ' 00:00:00']
        );
        $dailyMap = [];
        foreach ($daily as $row) {
            $dailyMap[$row['d']] = $row;
        }
        $chartLabels = [];
        $chartRevenue = [];
        $chartOrders  = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $chartLabels[]  = date('M j', strtotime($d));
            $chartRevenue[] = (float) ($dailyMap[$d]['revenue'] ?? 0);
            $chartOrders[]  = (int) ($dailyMap[$d]['orders'] ?? 0);
        }

        // KPIs over the same window.
        $kpis = [
            'revenue'  => (float) (Database::scalar(
                "SELECT COALESCE(SUM(total),0) FROM orders WHERE created_at >= ? AND status <> 'cancelled'", [$from . ' 00:00:00']) ?? 0),
            'orders'   => (int) (Database::scalar(
                "SELECT COUNT(*) FROM orders WHERE created_at >= ? AND status <> 'cancelled'", [$from . ' 00:00:00']) ?? 0),
            'customers'=> (int) (Database::scalar(
                "SELECT COUNT(*) FROM users WHERE role='customer' AND created_at >= ?", [$from . ' 00:00:00']) ?? 0),
            'products' => (int) (Database::scalar(
                "SELECT COUNT(*) FROM products WHERE deleted_at IS NULL") ?? 0),
        ];
        $kpis['avg_order'] = $kpis['orders'] > 0 ? round($kpis['revenue'] / $kpis['orders'], 2) : 0.0;

        // Order status breakdown.
        $byStatus = Database::fetchAll(
            "SELECT status, COUNT(*) AS n, COALESCE(SUM(total),0) AS revenue
             FROM orders WHERE created_at >= ? GROUP BY status ORDER BY n DESC", [$from . ' 00:00:00']);

        // Payment method breakdown.
        $byMethod = Database::fetchAll(
            "SELECT payment_method, COUNT(*) AS n, COALESCE(SUM(total),0) AS revenue
             FROM orders WHERE created_at >= ? AND status <> 'cancelled'
             GROUP BY payment_method ORDER BY n DESC", [$from . ' 00:00:00']);

        // Top sellers by earnings.
        $topSellers = Database::fetchAll(
            "SELECT s.id, s.shop_name, COUNT(DISTINCT oi.order_id) AS orders,
                    COALESCE(SUM(oi.seller_earnings),0) AS earnings
             FROM order_items oi
             JOIN orders o ON o.id = oi.order_id
             JOIN sellers s ON s.id = oi.seller_id
             WHERE o.created_at >= ? AND o.status <> 'cancelled'
             GROUP BY s.id, s.shop_name ORDER BY earnings DESC LIMIT 10", [$from . ' 00:00:00']);

        // Top products by revenue.
        $topProducts = Database::fetchAll(
            "SELECT oi.product_id, oi.product_name, SUM(oi.quantity) AS qty, SUM(oi.subtotal) AS revenue
             FROM order_items oi JOIN orders o ON o.id = oi.order_id
             WHERE o.created_at >= ? AND o.status <> 'cancelled'
             GROUP BY oi.product_id, oi.product_name ORDER BY revenue DESC LIMIT 10", [$from . ' 00:00:00']);

        // Coupon usage.
        $couponUsage = Database::fetchAll(
            "SELECT coupon_code, COUNT(*) AS uses, COALESCE(SUM(discount),0) AS total_discount
             FROM orders WHERE coupon_code IS NOT NULL AND coupon_code <> '' AND created_at >= ?
             GROUP BY coupon_code ORDER BY uses DESC LIMIT 10", [$from . ' 00:00:00']);

        $this->view('admin/analytics/index', compact(
            'days', 'chartLabels', 'chartRevenue', 'chartOrders', 'kpis',
            'byStatus', 'byMethod', 'topSellers', 'topProducts', 'couponUsage'
        ), 'dashboard');
    }

    // ------------------------------------------------------------------
    //  Settings (payment gateways + site config)
    // ------------------------------------------------------------------
    public function settings(): void
    {
        $this->view('admin/settings/index', [], 'dashboard');
    }

    public function settingsSave(): void
    {
        foreach (['cod_enabled', 'esewa_enabled', 'khalti_enabled', 'fonepay_enabled', 'review_auto_approve'] as $b) {
            Setting::set($b, $this->request->has($b) ? '1' : '0');
        }
        $texts = [
            'approval_mode', 'default_commission_rate',
            'esewa_merchant_id', 'esewa_secret', 'esewa_environment',
            'khalti_public_key', 'khalti_secret_key', 'khalti_environment',
            'fonepay_merchant_code', 'fonepay_secret', 'fonepay_environment',
            'free_shipping_threshold', 'shipping_fee',
            'site_name', 'site_tagline', 'contact_email', 'contact_phone',
            'currency_symbol', 'currency_code',
        ];
        foreach ($texts as $t) {
            if ($this->request->has($t)) {
                Setting::set($t, (string) $this->request->input($t));
            }
        }
        AuditLog::record(Auth::id(), 'admin.settings.update', 'Updated site settings');
        $this->success('Settings saved.', '/admin/settings');
    }

    // ------------------------------------------------------------------
    //  Audit log
    // ------------------------------------------------------------------
    public function audit(): void
    {
        $logs = AuditLog::recent(300);
        $this->view('admin/audit/index', compact('logs'), 'dashboard');
    }
}
