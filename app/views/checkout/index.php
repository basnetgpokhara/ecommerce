<?php /** @var array $items @var array $addresses @var float $subtotal @var float $discount @var ?array $coupon @var float $shippingFee @var float $total @var array $paymentMethods */ ?>
<section class="page-head">
    <div class="container">
        <nav aria-label="breadcrumb" class="small">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('/') ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/cart') ?>">Cart</a></li>
                <li class="breadcrumb-item active">Checkout</li>
            </ol>
        </nav>
        <h1 class="h3 fw-bold mb-0">Checkout</h1>
    </div>
</section>

<div class="container py-4">
    <form method="post" action="<?= url('/checkout/place') ?>" id="checkoutForm">
        <?= csrf_field() ?>
        <div class="row g-4">
            <div class="col-lg-8">
                <!-- Shipping address -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="mb-3"><i class="fas fa-truck me-2 text-primary"></i>Shipping Address</h5>

                        <?php if ($addresses): ?>
                            <?php foreach ($addresses as $i => $a): ?>
                                <label class="address-option <?= $i === 0 ? 'selected' : '' ?>">
                                    <input type="radio" name="address_id" value="<?= (int)$a['id'] ?>" class="address-radio" <?= $i === 0 ? 'checked' : '' ?>>
                                    <div>
                                        <strong><?= e($a['full_name']) ?></strong>
                                        <?php if ($a['is_default']): ?><span class="badge bg-primary ms-1">Default</span><?php endif; ?><br>
                                        <span class="small text-muted">
                                            <?= e($a['address_line1']) ?><?= !empty($a['address_line2']) ? ', ' . e($a['address_line2']) : '' ?>,
                                            <?= e($a['city']) ?><?= !empty($a['district']) ? ', ' . e($a['district']) : '' ?><br>
                                            <?= e($a['phone']) ?>
                                        </span>
                                    </div>
                                </label>
                            <?php endforeach; ?>

                            <div class="mt-3">
                                <a class="btn btn-link p-0" data-mdb-toggle="collapse" href="#newAddress">
                                    <i class="fas fa-plus me-1"></i> Ship to a new address
                                </a>
                                <div class="collapse" id="newAddress">
                                    <div class="card card-body mt-2 bg-light">
                                        <p class="small text-muted mb-2">Fill this in to use a new address (leave a saved address selected above to keep it).</p>
                                        <?php include __DIR__ . '/_address_fields.php'; ?>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <input type="hidden" name="address_id" value="">
                            <p class="small text-muted mb-3">Add your shipping address to continue.</p>
                            <?php include __DIR__ . '/_address_fields.php'; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Payment method -->
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-3"><i class="fas fa-wallet me-2 text-primary"></i>Payment Method</h5>
                        <?php foreach ($paymentMethods as $code => $label): ?>
                            <label class="address-option selected">
                                <input type="radio" name="payment_method" value="<?= e($code) ?>" class="address-radio" <?= $code === 'cod' ? 'checked' : '' ?>>
                                <div>
                                    <strong><?= e($label) ?></strong><br>
                                    <span class="small text-muted">
                                        <?= $code === 'cod' ? 'Pay with cash when your order is delivered.' : 'You\'ll be taken to a secure page to complete this payment.' ?>
                                    </span>
                                </div>
                            </label>
                        <?php endforeach; ?>

                        <div class="form-outline mt-3">
                            <textarea name="notes" class="form-control" rows="2" placeholder="Optional order notes (optional)"></textarea>
                            <label class="form-label">Order notes (optional)</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="summary-card">
                    <h5 class="summary-title">Your Order</h5>
                    <div class="checkout-items">
                        <?php foreach ($items as $it): ?>
                            <div class="checkout-item">
                                <img src="<?= media_url($it['image'] ?? null) ?>" alt="">
                                <div class="flex-grow-1">
                                    <div class="small fw-semibold"><?= e($it['name']) ?></div>
                                    <div class="text-muted small"><?= (int)$it['quantity'] ?> × <?= money($it['effective_price']) ?></div>
                                </div>
                                <div class="small fw-bold"><?= money((float)$it['effective_price'] * (int)$it['quantity']) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <hr>
                    <div class="summary-row"><span>Subtotal</span><span><?= money($subtotal) ?></span></div>
                    <?php if ($coupon && $discount > 0): ?>
                        <div class="summary-row text-success">
                            <span>Coupon <?= e($coupon['code']) ?></span>
                            <span>− <?= money($discount) ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="summary-row"><span>Shipping</span><span><?= $shippingFee == 0 ? 'Free' : money($shippingFee) ?></span></div>
                    <div class="summary-row summary-total"><span>Total</span><span><?= money($total) ?></span></div>
                    <button type="submit" class="btn btn-success btn-lg w-100 mt-3">
                        <i class="fas fa-lock me-1"></i> Place Order
                    </button>
                    <p class="small text-muted text-center mt-2 mb-0">
                        By placing your order you agree to our <a href="<?= url('/page/terms') ?>">Terms</a>.
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>
