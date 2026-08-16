<?php /** @var array $seller */ ?>
<div class="shop-card h-100">
    <div class="shop-card-banner" style="background-image:url('<?= e(media_url($seller['shop_banner'] ?? null)) ?>')">
        <div class="shop-card-logo">
            <img src="<?= media_url($seller['shop_logo'] ?? null) ?>" alt="<?= e($seller['shop_name']) ?>">
        </div>
    </div>
    <div class="shop-card-body text-center p-3">
        <h6 class="mb-1"><?= e($seller['shop_name']) ?></h6>
        <div class="small text-muted mb-2">
            <i class="fas fa-box me-1"></i><?= (int) ($seller['product_count'] ?? 0) ?> products
        </div>
        <a href="<?= url('/shop/' . $seller['slug']) ?>" class="btn btn-outline-primary btn-sm px-4">Visit Shop</a>
    </div>
</div>
