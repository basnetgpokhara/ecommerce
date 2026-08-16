<?php /** @var array $order @var array $payments */ ?>
<section class="page-head">
    <div class="container">
        <nav aria-label="breadcrumb" class="small">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('/') ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/account/orders') ?>">My Orders</a></li>
                <li class="breadcrumb-item active">Payment</li>
            </ol>
        </nav>
        <h1 class="h3 fw-bold mb-0">Payment Unsuccessful</h1>
    </div>
</section>

<div class="container py-4">
    <div class="card mx-auto" style="max-width:560px">
        <div class="card-body text-center p-5">
            <i class="fas fa-circle-exclamation fa-3x text-danger mb-3"></i>
            <h4 class="fw-bold">We couldn't process your payment</h4>
            <p class="text-muted">
                Your order <strong><?= e($order['order_number']) ?></strong> was created, but the payment was not
                confirmed. No money has been deducted, or any deduction has been reversed by the gateway.
            </p>
            <?php if (($order['payment_method'] ?? '') !== 'cod'): ?>
                <a href="<?= url('/checkout/pay/' . $order['id']) ?>" class="btn btn-primary mt-2">
                    <i class="fas fa-rotate me-1"></i> Try payment again
                </a>
            <?php endif; ?>
            <div class="mt-2">
                <a href="<?= url('/') ?>" class="btn btn-outline-primary">Continue shopping</a>
                <a href="<?= url('/account/orders/' . $order['id']) ?>" class="btn btn-link">View order</a>
            </div>
        </div>
    </div>
</div>
