<?php /** Homepage — hero, categories, product sections, shops. */ ?>

<?php if ($banners): ?>
<section class="hero-wrap">
    <div id="heroCarousel" class="carousel slide carousel-fade" data-mdb-ride="carousel" data-mdb-interval="5000">
        <div class="carousel-indicators">
            <?php foreach ($banners as $i => $b): ?>
                <button type="button" data-mdb-target="#heroCarousel" data-mdb-slide-to="<?= $i ?>" <?= $i === 0 ? 'class="active"' : '' ?>></button>
            <?php endforeach; ?>
        </div>
        <div class="carousel-inner">
            <?php foreach ($banners as $i => $b): ?>
                <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                    <img src="<?= media_url($b['image']) ?>" class="d-block w-100 hero-img" alt="<?= e($b['title'] ?? '') ?>">
                    <div class="carousel-caption hero-caption text-start">
                        <?php if (!empty($b['title'])): ?><h2 class="display-6 fw-bold"><?= e($b['title']) ?></h2><?php endif; ?>
                        <?php if (!empty($b['subtitle'])): ?><p class="lead d-none d-md-block"><?= e($b['subtitle']) ?></p><?php endif; ?>
                        <?php if (!empty($b['link'])): ?>
                            <a href="<?= url($b['link']) ?>" class="btn btn-light btn-lg"><?= e($b['button_text'] ?: 'Shop Now') ?></a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-mdb-target="#heroCarousel" data-mdb-slide="prev"><span class="carousel-control-prev-icon"></span></button>
        <button class="carousel-control-next" type="button" data-mdb-target="#heroCarousel" data-mdb-slide="next"><span class="carousel-control-next-icon"></span></button>
    </div>
</section>
<?php else: ?>
<section class="hero-wrap hero-default">
    <div class="container h-100 d-flex align-items-center">
        <div class="hero-default-text">
            <span class="badge bg-primary mb-2">Multi-vendor marketplace</span>
            <h1 class="display-4 fw-bold">Shop local sellers, <br>delivered across Nepal.</h1>
            <p class="lead">Discover electronics, fashion, home essentials and more from trusted Nepali shops.</p>
            <a href="<?= url('/categories') ?>" class="btn btn-light btn-lg me-2">Browse Categories</a>
            <a href="<?= url('/shops') ?>" class="btn btn-outline-light btn-lg">Explore Shops</a>
        </div>
    </div>
</section>
<?php endif; ?>

<div class="container py-4">
    <?php if ($categories): ?>
    <section class="mb-5">
        <div class="d-flex justify-content-between align-items-end mb-3">
            <h3 class="section-title">Shop by Category</h3>
            <a href="<?= url('/categories') ?>" class="small fw-bold">View all <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
        <div class="category-grid">
            <?php foreach ($categories as $cat): ?>
                <?php \App\Core\View::partial('category_card', ['category' => $cat]); ?>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($featured): ?>
    <section class="mb-5">
        <div class="d-flex justify-content-between align-items-end mb-3">
            <h3 class="section-title">Featured Products</h3>
        </div>
        <div class="product-grid">
            <?php foreach ($featured as $p): ?>
                <?php \App\Core\View::partial('product_card', ['product' => $p]); ?>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>

    <?php if ($newArrivals || $deals): ?>
    <section class="mb-5">
        <ul class="nav nav-pills mb-3 tabs-line" role="tablist">
            <li class="nav-item"><a class="nav-link active" data-mdb-toggle="pill" href="#tabNew" role="tab">New Arrivals</a></li>
            <li class="nav-item"><a class="nav-link" data-mdb-toggle="pill" href="#tabDeals" role="tab">Deals</a></li>
        </ul>
        <div class="tab-content">
            <div class="tab-pane fade show active" id="tabNew" role="tabpanel">
                <div class="product-grid">
                    <?php foreach ($newArrivals as $p): ?>
                        <?php \App\Core\View::partial('product_card', ['product' => $p]); ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <div class="tab-pane fade" id="tabDeals" role="tabpanel">
                <div class="product-grid">
                    <?php foreach ($deals as $p): ?>
                        <?php \App\Core\View::partial('product_card', ['product' => $p]); ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section class="promo-strip my-5">
        <div class="row g-0 align-items-center">
            <div class="col-md-8 p-4 p-lg-5">
                <h3 class="fw-bold mb-2">Free shipping on orders over <?= money((float) setting('free_shipping_threshold', 2000)) ?></h3>
                <p class="mb-0 text-muted">Pay your way — eSewa, Khalti, Fonepay or Cash on Delivery.</p>
            </div>
            <div class="col-md-4 text-md-end p-4">
                <a href="<?= url('/search') ?>" class="btn btn-primary btn-lg">Start Shopping</a>
            </div>
        </div>
    </section>

    <?php if ($shops): ?>
    <section class="mb-4">
        <div class="d-flex justify-content-between align-items-end mb-3">
            <h3 class="section-title">Our Shops</h3>
            <a href="<?= url('/shops') ?>" class="small fw-bold">All shops <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
        <div class="shop-grid">
            <?php foreach ($shops as $s): ?>
                <?php \App\Core\View::partial('shop_card', ['seller' => $s]); ?>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endif; ?>
</div>
