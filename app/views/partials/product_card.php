<?php
/** @var array $product — a storefront product row (with effective_price, image, shop_*). */
use App\Models\Product;
$price   = (float) $product['price'];
$eff     = (float) ($product['effective_price'] ?? Product::effectivePrice($product));
$hasDisc = $eff < $price;
$off     = Product::discountPercent($product);
$img     = $product['image'] ?? null;
$out     = (int) ($product['stock'] ?? 0) < 1;
?>
<div class="product-card h-100">
    <a href="<?= url('/product/' . $product['slug']) ?>" class="product-card-media">
        <img src="<?= media_url($img) ?>" alt="<?= e($product['name']) ?>" loading="lazy">
        <?php if ($hasDisc): ?><span class="badge bg-danger product-badge">-<?= $off ?>%</span><?php endif; ?>
        <?php if ($out): ?><span class="product-soldout">Out of stock</span><?php endif; ?>
    </a>
    <div class="product-card-body d-flex flex-column">
        <div class="product-shop"><?= e($product['shop_name'] ?? '') ?></div>
        <h6 class="product-name">
            <a href="<?= url('/product/' . $product['slug']) ?>"><?= e($product['name']) ?></a>
        </h6>
        <div class="product-price mt-1">
            <?php if ($hasDisc): ?>
                <span class="now"><?= money($eff) ?></span>
                <span class="was"><?= money($price) ?></span>
            <?php else: ?>
                <span class="now"><?= money($price) ?></span>
            <?php endif; ?>
        </div>
        <?php if (!empty($product['rating_count'])): ?>
            <div class="product-rating small">
                <i class="fas fa-star text-warning"></i> <?= e(number_format((float)$product['rating_avg'], 1)) ?>
                <span class="text-muted">(<?= (int)$product['rating_count'] ?>)</span>
            </div>
        <?php endif; ?>
        <form action="<?= url('/cart/add') ?>" method="post" class="mt-auto pt-2">
            <?= csrf_field() ?>
            <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
            <input type="hidden" name="quantity" value="1">
            <button type="submit" class="btn btn-primary btn-sm w-100" <?= $out ? 'disabled' : '' ?>>
                <i class="fas fa-cart-plus me-1"></i> Add to Cart
            </button>
        </form>
    </div>
</div>
