<?php /** @var array $shops */ ?>
<section class="page-head">
    <div class="container">
        <nav aria-label="breadcrumb" class="small">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('/') ?>">Home</a></li>
                <li class="breadcrumb-item active">Shops</li>
            </ol>
        </nav>
        <h1 class="h3 fw-bold mb-0">Our Sellers</h1>
        <p class="text-muted small mb-0">Discover products from independent Nepali shops.</p>
    </div>
</section>

<div class="container py-4">
    <div class="shop-grid">
        <?php foreach ($shops as $s): ?>
            <?php \App\Core\View::partial('shop_card', ['seller' => $s]); ?>
        <?php endforeach; ?>
    </div>
    <?php if (!$shops): ?>
        <p class="text-muted">No shops are active yet.</p>
    <?php endif; ?>
</div>
