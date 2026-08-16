<?php /** @var array $items @var float $subtotal */ ?>
<section class="page-head">
    <div class="container">
        <nav aria-label="breadcrumb" class="small">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('/') ?>">Home</a></li>
                <li class="breadcrumb-item active">Cart</li>
            </ol>
        </nav>
        <h1 class="h3 fw-bold mb-0">Your Shopping Cart</h1>
    </div>
</section>

<div class="container py-4">
    <?php if (!$items): ?>
        <div class="text-center py-5">
            <i class="fas fa-cart-shopping fa-3x text-muted mb-3"></i>
            <h4>Your cart is empty</h4>
            <p class="text-muted">Looks like you haven't added anything yet.</p>
            <a href="<?= url('/') ?>" class="btn btn-primary mt-2">Start Shopping</a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <div class="col-lg-8">
                <form method="post" action="<?= url('/cart/update') ?>" id="cartForm">
                    <?= csrf_field() ?>
                    <div class="card">
                        <div class="card-body p-0">
                            <?php foreach ($items as $it): ?>
                                <?php
                                $line = (float)$it['effective_price'] * (int)$it['quantity'];
                                $unavailable = $it['status'] !== 'approved' || (int)$it['stock'] < 1;
                                $cid = (int)$it['cart_id'];
                                ?>
                                <div class="cart-row <?= $unavailable ? 'cart-row-unavailable' : '' ?>">
                                    <div class="cart-row-main">
                                        <a href="<?= url('/product/' . $it['slug']) ?>" class="cart-thumb">
                                            <img src="<?= media_url($it['image'] ?? null) ?>" alt="<?= e($it['name']) ?>">
                                        </a>
                                        <div class="cart-info">
                                            <a href="<?= url('/product/' . $it['slug']) ?>" class="fw-semibold text-dark text-decoration-none"><?= e($it['name']) ?></a>
                                            <div class="small text-muted"><?= e($it['shop_name']) ?></div>
                                            <div class="small"><?= money($it['effective_price']) ?> each</div>
                                            <?php if ($unavailable): ?>
                                                <div class="badge bg-warning text-dark mt-1">No longer available — please remove</div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="cart-row-aside">
                                        <input type="hidden" name="items[<?= $cid ?>][id]" value="<?= $cid ?>">
                                        <div class="qty-selector qty-sm">
                                            <button type="button" class="js-qty-minus" data-min="1">−</button>
                                            <input type="number" name="items[<?= $cid ?>][quantity]" class="js-qty" value="<?= (int)$it['quantity'] ?>" min="1" max="<?= max(1, (int)$it['stock']) ?>">
                                            <button type="button" class="js-qty-plus" data-max="<?= max(1, (int)$it['stock']) ?>">+</button>
                                        </div>
                                        <div class="cart-line-total fw-bold"><?= money($line) ?></div>
                                        <!-- Submit to a different action via formaction (no nested form needed) -->
                                        <button type="submit" formaction="<?= url('/cart/remove') ?>" name="cart_id" value="<?= $cid ?>" class="btn btn-link text-danger p-0" title="Remove" onclick="return confirm('Remove this item?')">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </form>
                <div class="d-flex justify-content-between mt-3">
                    <a href="<?= url('/') ?>" class="btn btn-link ps-0"><i class="fas fa-arrow-left me-1"></i> Continue shopping</a>
                    <div>
                        <form method="post" action="<?= url('/cart/clear') ?>" class="d-inline">
                            <?= csrf_field() ?>
                            <button class="btn btn-outline-danger btn-sm">Clear cart</button>
                        </form>
                        <button type="submit" form="cartForm" class="btn btn-outline-primary btn-sm">Update cart</button>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="summary-card">
                    <h5 class="summary-title">Order Summary</h5>
                    <div class="summary-row"><span>Subtotal</span><span><?= money($subtotal) ?></span></div>
                    <?php $threshold = (float) setting('free_shipping_threshold', 0); ?>
                    <?php $shipping = ($threshold > 0 && $subtotal >= $threshold) ? 0.0 : (float) setting('shipping_fee', 0); ?>
                    <div class="summary-row"><span>Shipping</span><span><?= $shipping == 0 ? 'Free' : money($shipping) ?></span></div>
                    <hr>
                    <div class="summary-row summary-total"><span>Total</span><span><?= money($subtotal + $shipping) ?></span></div>
                    <a href="<?= url('/checkout') ?>" class="btn btn-primary btn-lg w-100 mt-2">Proceed to Checkout</a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>
