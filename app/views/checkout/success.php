<?php /** @var array $order @var array $items @var array $payments */ ?>
<section class="page-head">
    <div class="container">
        <nav aria-label="breadcrumb" class="small">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('/') ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/account/orders') ?>">My Orders</a></li>
                <li class="breadcrumb-item active">Order confirmed</li>
            </ol>
        </nav>
    </div>
</section>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-4">
                <div class="success-check"><i class="fas fa-check"></i></div>
                <h2 class="fw-bold mt-3">Thank you for your order!</h2>
                <p class="text-muted">Your order <strong><?= e($order['order_number']) ?></strong> has been placed successfully.</p>
                <?php if ($order['payment_method'] === 'cod'): ?>
                    <span class="badge bg-success px-3 py-2">Cash on Delivery</span>
                <?php endif; ?>
            </div>

            <div class="card">
                <div class="card-body">
                    <h6 class="border-bottom pb-2 mb-3">Order summary</h6>
                    <?php foreach ($items as $it): ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span><?= (int)$it['quantity'] ?> × <?= e($it['product_name']) ?></span>
                            <strong><?= money($it['subtotal']) ?></strong>
                        </div>
                    <?php endforeach; ?>
                    <hr>
                    <div class="d-flex justify-content-between text-muted small">
                        <span>Subtotal</span><span><?= money($order['subtotal']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between text-muted small">
                        <span>Shipping</span><span><?= $order['shipping_fee'] == 0 ? 'Free' : money($order['shipping_fee']) ?></span>
                    </div>
                    <div class="d-flex justify-content-between fw-bold mt-1">
                        <span>Total</span><span><?= money($order['total']) ?></span>
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2 justify-content-center mt-4">
                <a href="<?= url('/order/' . $order['id'] . '/invoice') ?>" target="_blank" class="btn btn-outline-primary">
                    <i class="fas fa-print me-1"></i> Print / Download Invoice
                </a>
                <a href="<?= url('/account/orders/' . $order['id']) ?>" class="btn btn-outline-secondary">View Order</a>
                <a href="<?= url('/') ?>" class="btn btn-primary">Continue Shopping</a>
            </div>
        </div>
    </div>
</div>
