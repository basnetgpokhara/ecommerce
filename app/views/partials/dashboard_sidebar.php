<?php
use App\Core\Auth;
$user = current_user();
$role = $user['role'] ?? 'customer';
$current = $_SERVER['REQUEST_URI'] ?? '';
$isCurrent = function (string $path) use ($current) {
    return rtrim($current, '/') === rtrim($path, '/') || strpos($current, $path . '/') === 0;
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
        <li><a href="<?= url('/account') ?>"        class="<?= $isCurrent('/account') ? 'active' : '' ?>"><i class="fas fa-gauge"></i> Dashboard</a></li>
        <li><a href="<?= url('/account/orders') ?>" class="<?= $isCurrent('/account/orders') ? 'active' : '' ?>"><i class="fas fa-box"></i> My Orders</a></li>
        <li><a href="<?= url('/account/addresses') ?>" class="<?= $isCurrent('/account/addresses') ? 'active' : '' ?>"><i class="fas fa-location-dot"></i> Addresses</a></li>
        <li><a href="<?= url('/account/profile') ?>" class="<?= $isCurrent('/account/profile') ? 'active' : '' ?>"><i class="fas fa-user"></i> Profile</a></li>
    <?php elseif ($role === 'seller'): ?>
        <li><a href="<?= url('/seller') ?>" class="<?= $isCurrent('/seller') ? 'active' : '' ?>"><i class="fas fa-gauge"></i> Dashboard</a></li>
        <li><a href="<?= url('/account/orders') ?>" class="<?= $isCurrent('/account/orders') ? 'active' : '' ?>"><i class="fas fa-box"></i> My Orders</a></li>
        <li><a href="<?= url('/account/profile') ?>" class="<?= $isCurrent('/account/profile') ? 'active' : '' ?>"><i class="fas fa-user"></i> Profile</a></li>
    <?php elseif ($role === 'admin'): ?>
        <li><a href="<?= url('/admin') ?>" class="<?= $isCurrent('/admin') ? 'active' : '' ?>"><i class="fas fa-gauge"></i> Dashboard</a></li>
        <li><a href="<?= url('/account/profile') ?>" class="<?= $isCurrent('/account/profile') ? 'active' : '' ?>"><i class="fas fa-user"></i> Profile</a></li>
    <?php endif; ?>

    <li class="dash-nav-sep"></li>
    <li><a href="<?= url('/') ?>"><i class="fas fa-store"></i> Back to Store</a></li>
    <?php if (Auth::is('customer')): ?>
        <li><a href="<?= url('/register?role=seller') ?>"><i class="fas fa-store"></i> Become a Seller</a></li>
    <?php endif; ?>
    <li><a href="<?= url('/logout') ?>" class="text-danger"><i class="fas fa-right-from-bracket"></i> Logout</a></li>
</ul>

<div class="dash-phase-note">
    <i class="fas fa-circle-info me-1"></i>
    Full seller &amp; admin management tools arrive in Phase 2.
</div>
