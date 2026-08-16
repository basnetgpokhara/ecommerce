<?php
use App\Core\Auth;

$user = current_user();
$role = $user['role'] ?? 'customer';

// Compute the app-relative path (strip BASE) for accurate active-link matching.
$raw  = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$path = $raw;
if (defined('BASE') && BASE !== '' && str_starts_with($raw, BASE)) {
    $path = substr($raw, strlen(BASE));
}
$path = '/' . ltrim($path, '/');

$active = function (string $seg) use ($path): string {
    $seg = '/' . trim($seg, '/');
    if ($path === $seg || str_starts_with($path, $seg . '/')) {
        return 'active';
    }
    return '';
};
?>
<div class="dash-user">
    <div class="dash-avatar"><?= e(strtoupper(mb_substr($user['name'] ?? 'U', 0, 1))) ?></div>
    <div>
        <div class="fw-bold"><?= e($user['name'] ?? '') ?></div>
        <span class="badge text-capitalize dash-role-<?= e($role) ?>"><?= e($role) ?></span>
    </div>
</div>

<ul class="dash-nav">
    <?php if ($role === 'customer'): ?>
        <li><a href="<?= url('/account') ?>" class="<?= $path === '/account' ? 'active' : '' ?>"><i class="fas fa-gauge"></i> Dashboard</a></li>
        <li><a href="<?= url('/account/orders') ?>" class="<?= $active('/account/orders') ?>"><i class="fas fa-box"></i> My Orders</a></li>
        <li><a href="<?= url('/account/addresses') ?>" class="<?= $active('/account/addresses') ?>"><i class="fas fa-location-dot"></i> Addresses</a></li>
        <li><a href="<?= url('/account/profile') ?>" class="<?= $active('/account/profile') ?>"><i class="fas fa-user"></i> Profile</a></li>

    <?php elseif ($role === 'seller'): ?>
        <li class="dash-nav-head">Seller</li>
        <li><a href="<?= url('/seller') ?>" class="<?= $path === '/seller' ? 'active' : '' ?>"><i class="fas fa-gauge"></i> Dashboard</a></li>
        <li><a href="<?= url('/seller/products') ?>" class="<?= $active('/seller/products') ?>"><i class="fas fa-tags"></i> Products</a></li>
        <li><a href="<?= url('/seller/orders') ?>" class="<?= $active('/seller/orders') ?>"><i class="fas fa-truck"></i> Orders</a></li>
        <li><a href="<?= url('/seller/reviews') ?>" class="<?= $active('/seller/reviews') ?>"><i class="fas fa-star"></i> Reviews</a></li>
        <li><a href="<?= url('/seller/shop') ?>" class="<?= $active('/seller/shop') ?>"><i class="fas fa-store"></i> Shop Profile</a></li>
        <li class="dash-nav-sep"></li>
        <li class="dash-nav-head">Buyer</li>
        <li><a href="<?= url('/account/orders') ?>" class="<?= $active('/account/orders') ?>"><i class="fas fa-box"></i> My Orders</a></li>
        <li><a href="<?= url('/account/profile') ?>" class="<?= $active('/account/profile') ?>"><i class="fas fa-user"></i> Profile</a></li>

    <?php elseif ($role === 'admin'): ?>
        <li class="dash-nav-head">Administration</li>
        <li><a href="<?= url('/admin') ?>" class="<?= $path === '/admin' ? 'active' : '' ?>"><i class="fas fa-gauge"></i> Dashboard</a></li>
        <li><a href="<?= url('/admin/users') ?>" class="<?= $active('/admin/users') ?>"><i class="fas fa-users"></i> Users</a></li>
        <li><a href="<?= url('/admin/sellers') ?>" class="<?= $active('/admin/sellers') ?>"><i class="fas fa-store"></i> Sellers</a></li>
        <li><a href="<?= url('/admin/products') ?>" class="<?= $active('/admin/products') ?>"><i class="fas fa-tags"></i> Products</a></li>
        <li><a href="<?= url('/admin/categories') ?>" class="<?= $active('/admin/categories') ?>"><i class="fas fa-folder-tree"></i> Categories</a></li>
        <li><a href="<?= url('/admin/orders') ?>" class="<?= $active('/admin/orders') ?>"><i class="fas fa-clipboard-list"></i> Orders</a></li>
        <li><a href="<?= url('/admin/reviews') ?>" class="<?= $active('/admin/reviews') ?>"><i class="fas fa-star-half-stroke"></i> Reviews</a></li>
        <li><a href="<?= url('/admin/banners') ?>" class="<?= $active('/admin/banners') ?>"><i class="fas fa-image"></i> Banners</a></li>
        <li><a href="<?= url('/admin/pages') ?>" class="<?= $active('/admin/pages') ?>"><i class="fas fa-file-lines"></i> Pages</a></li>
        <li><a href="<?= url('/admin/settings') ?>" class="<?= $active('/admin/settings') ?>"><i class="fas fa-gear"></i> Settings</a></li>
        <li><a href="<?= url('/admin/audit') ?>" class="<?= $active('/admin/audit') ?>"><i class="fas fa-clipboard-check"></i> Audit Log</a></li>
    <?php endif; ?>

    <li class="dash-nav-sep"></li>
    <li><a href="<?= url('/') ?>"><i class="fas fa-store"></i> Back to Store</a></li>
    <?php if (Auth::is('customer')): ?>
        <li><a href="<?= url('/register?role=seller') ?>"><i class="fas fa-store"></i> Become a Seller</a></li>
    <?php endif; ?>
    <li><a href="<?= url('/logout') ?>" class="text-danger"><i class="fas fa-right-from-bracket"></i> Logout</a></li>
</ul>
