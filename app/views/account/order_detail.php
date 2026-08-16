<?php /** @var array $order @var array $items @var array $payments */
$statusBadge = function (string $s): string {
    return ['placed'=>'bg-info text-dark','confirmed'=>'bg-primary','shipped'=>'bg-secondary','delivered'=>'bg-success','cancelled'=>'bg-danger','refunded'=>'bg-warning text-dark'][$s] ?? 'bg-light text-dark';
};
?>
<div class="dash-head d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold mb-0">Order <?= e($order['order_number']) ?></h4>
        <p class="text-muted mb-0">Placed on <?= e(date('F j, Y \a\t g:i A', strtotime($order['created_at']))) ?></p>
    </div>
    <a href="<?= url('/order/' . $order['id'] . '/invoice') ?>" target="_blank" class="btn btn-outline-primary"><i class="fas fa-print me-1"></i> Invoice</a>
</div>

<div class="card mb-3">
    <div class="card-body">
        <div class="row g-3">
            <div class="col-md-4">
                <h6 class="text-muted text-uppercase small">Status</h6>
                <span class="badge <?= $statusBadge($order['status']) ?> fs-6"><?= ucfirst(e($order['status'])) ?></span>
            </div>
            <div class="col-md-4">
                <h6 class="text-muted text-uppercase small">Payment</h6>
                <span class="text-capitalize"><?= e($order['payment_method']) ?></span> ·
                <span class="badge bg-light text-dark"><?= ucfirst(e($order['payment_status'])) ?></span>
            </div>
            <div class="col-md-4">
                <h6 class="text-muted text-uppercase small">Shipping To</h6>
                <div class="small"><?= e($order['shipping_name']) ?><br><?= nl2br(e($order['shipping_address'])) ?><br><?= e($order['shipping_city']) ?></div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <h6 class="mb-3">Items</h6>
        <?php foreach ($items as $it): ?>
            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                <div class="d-flex align-items-center gap-3">
                    <img src="<?= media_url($it['product_image'] ?? null) ?>" alt="" style="width:48px;height:48px;object-fit:cover;border-radius:6px">
                    <div>
                        <div class="fw-semibold"><?= e($it['product_name']) ?></div>
                        <div class="text-muted small"><?= (int)$it['quantity'] ?> × <?= money($it['unit_price']) ?></div>
                    </div>
                </div>
                <strong><?= money($it['subtotal']) ?></strong>
            </div>
        <?php endforeach; ?>
        <div class="d-flex justify-content-between mt-3 text-muted small"><span>Subtotal</span><span><?= money($order['subtotal']) ?></span></div>
        <div class="d-flex justify-content-between text-muted small"><span>Shipping</span><span><?= $order['shipping_fee'] == 0 ? 'Free' : money($order['shipping_fee']) ?></span></div>
        <div class="d-flex justify-content-between fw-bold fs-5 mt-1"><span>Total</span><span><?= money($order['total']) ?></span></div>
    </div>
</div>
