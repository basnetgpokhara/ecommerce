<?php
use App\Core\Auth;
use App\Models\Banner;
use App\Models\Cart;
use App\Models\Category;

$categories = Category::tree();
$cartCount  = Auth::check() ? Cart::count((int) Auth::id()) : 0;
$user       = current_user();
$promo      = Banner::active('promo');
$promo      = $promo[0] ?? null;
$searchQ    = $_GET['q'] ?? '';
?>
<?php if ($promo): ?>
<div class="site-promo-bar">
    <div class="container"><?= e($promo['subtitle'] ?? $promo['title'] ?? '') ?></div>
</div>
<?php endif; ?>

<header class="site-header">
    <div class="site-topbar d-none d-md-block">
        <div class="container d-flex justify-content-between align-items-center py-1 small">
            <div>
                <i class="fas fa-phone-alt me-1"></i> <?= e(setting('contact_phone', '+977 1-000-0000')) ?>
                <span class="mx-2 opacity-50">|</span>
                <i class="fas fa-envelope me-1"></i> <?= e(setting('contact_email', 'hello@nepmart.test')) ?>
            </div>
            <div class="d-flex align-items-center gap-3">
                <a href="<?= url('/page/about') ?>" class="text-reset text-decoration-none">About</a>
                <a href="<?= url('/page/returns') ?>" class="text-reset text-decoration-none">Returns</a>
                <a href="<?= url('/page/contact') ?>" class="text-reset text-decoration-none">Help</a>
            </div>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg navbar-main">
        <div class="container">
            <button class="navbar-toggler" type="button" data-mdb-toggle="collapse" data-mdb-target="#mainNav">
                <i class="fas fa-bars"></i>
            </button>

            <a class="navbar-brand d-flex align-items-center gap-2" href="<?= url('/') ?>">
                <img src="<?= asset('img/logo.svg') ?>" alt="<?= e(setting('site_name', APP_NAME)) ?>" height="36">
                <span class="brand-text"><?= e(setting('site_name', APP_NAME)) ?></span>
            </a>

            <form class="d-none d-lg-flex header-search mx-auto" action="<?= url('/search') ?>" method="get" role="search">
                <input class="form-control" type="search" name="q" value="<?= e($searchQ) ?>" placeholder="Search products, brands and more…" aria-label="Search">
                <button class="btn btn-primary px-3" type="submit"><i class="fas fa-search"></i></button>
            </form>

            <div class="d-flex align-items-center gap-1 ms-lg-2">
                <a href="<?= url($user ? '/account' : '/login') ?>" class="btn btn-link text-dark px-2" title="Account">
                    <i class="fas fa-user"></i>
                    <span class="d-none d-xl-inline small"><?= e($user ? explode(' ', $user['name'])[0] : 'Login') ?></span>
                </a>

                <a href="<?= url('/cart') ?>" class="btn btn-link text-dark px-2 position-relative" title="Cart">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="d-none d-xl-inline small">Cart</span>
                    <?php if ($cartCount > 0): ?>
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-count"><?= (int) $cartCount ?></span>
                    <?php endif; ?>
                </a>

                <div class="dropdown">
                    <a class="btn btn-link text-dark px-2 dropdown-toggle" data-mdb-toggle="dropdown">
                        <i class="fas fa-th-list"></i>
                        <span class="d-none d-xl-inline small">More</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <?php if (Auth::check()): ?>
                            <li><h6 class="dropdown-header"><?= e($user['name']) ?></h6></li>
                            <?php if (Auth::is('customer')): ?>
                                <li><a class="dropdown-item" href="<?= url('/account') ?>"><i class="fas fa-gauge me-2"></i>My Account</a></li>
                                <li><a class="dropdown-item" href="<?= url('/account/orders') ?>"><i class="fas fa-box me-2"></i>My Orders</a></li>
                                <li><a class="dropdown-item" href="<?= url('/account/addresses') ?>"><i class="fas fa-location-dot me-2"></i>Addresses</a></li>
                            <?php endif; ?>
                            <?php if (Auth::is('seller')): ?>
                                <li><a class="dropdown-item" href="<?= url('/seller') ?>"><i class="fas fa-store me-2"></i>Seller Dashboard</a></li>
                            <?php endif; ?>
                            <?php if (Auth::is('admin')): ?>
                                <li><a class="dropdown-item" href="<?= url('/admin') ?>"><i class="fas fa-shield-halved me-2"></i>Admin Dashboard</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?= url('/logout') ?>"><i class="fas fa-right-from-bracket me-2"></i>Logout</a></li>
                        <?php else: ?>
                            <li><a class="dropdown-item" href="<?= url('/login') ?>"><i class="fas fa-right-to-bracket me-2"></i>Login</a></li>
                            <li><a class="dropdown-item" href="<?= url('/register') ?>"><i class="fas fa-user-plus me-2"></i>Register as Customer</a></li>
                            <li><a class="dropdown-item" href="<?= url('/register?role=seller') ?>"><i class="fas fa-store me-2"></i>Register as Seller</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <div class="collapse navbar-collapse" id="mainNav">
                <form class="d-flex d-lg-none my-3" action="<?= url('/search') ?>" method="get">
                    <input class="form-control" type="search" name="q" value="<?= e($searchQ) ?>" placeholder="Search…">
                    <button class="btn btn-primary ms-2"><i class="fas fa-search"></i></button>
                </form>
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="<?= url('/') ?>">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= url('/categories') ?>">All Categories</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= url('/shops') ?>">Shops</a></li>
                    <?php foreach ($categories as $cat): ?>
                        <?php $hasKids = !empty($cat['children']); ?>
                        <li class="nav-item <?= $hasKids ? 'dropdown dropdown-mega position-static' : '' ?>">
                            <?php if ($hasKids): ?>
                                <a class="nav-link dropdown-toggle" data-mdb-toggle="dropdown" href="<?= url('/category/' . $cat['slug']) ?>"><?= e($cat['name']) ?></a>
                                <div class="dropdown-menu mega-menu shadow">
                                    <div class="mega-grid">
                                        <div class="mega-col">
                                            <a class="mega-head" href="<?= url('/category/' . $cat['slug']) ?>"><?= e($cat['name']) ?> — all</a>
                                            <?php foreach ($cat['children'] as $ch): ?>
                                                <a class="dropdown-item" href="<?= url('/category/' . $ch['slug']) ?>"><?= e($ch['name']) ?></a>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <a class="nav-link" href="<?= url('/category/' . $cat['slug']) ?>"><?= e($cat['name']) ?></a>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </nav>
</header>
