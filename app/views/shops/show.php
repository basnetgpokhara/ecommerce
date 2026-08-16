<?php /** @var array $seller @var array $products */ ?>
<section class="shop-hero">
    <div class="shop-hero-banner" style="background-image:url('<?= e(media_url($seller['shop_banner'] ?? null)) ?>')"></div>
    <div class="container">
        <div class="shop-hero-card">
            <div class="shop-hero-logo"><img src="<?= media_url($seller['shop_logo'] ?? null) ?>" alt="<?= e($seller['shop_name']) ?>"></div>
            <div>
                <h1 class="h4 fw-bold mb-1"><?= e($seller['shop_name']) ?></h1>
                <?php if (!empty($seller['contact_phone'])): ?>
                    <div class="small text-muted"><i class="fas fa-phone me-1"></i><?= e($seller['contact_phone']) ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<div class="container py-4">
    <?php if (!empty($seller['description'])): ?>
        <p class="lead"><?= nl2br(e($seller['description'])) ?></p>
    <?php endif; ?>
    <hr>
    <h3 class="section-title mb-3">Products (<?= count($products) ?>)</h3>
    <?php if ($products): ?>
        <div class="product-grid">
            <?php foreach ($products as $p): ?>
                <?php \App\Core\View::partial('product_card', ['product' => $p]); ?>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p class="text-muted">This shop hasn't listed any products yet.</p>
    <?php endif; ?>
</div>
