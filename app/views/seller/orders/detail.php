<?php /** @var array $order @var array $items */
$currentStatus = $items[0]['fulfillment'] ?? 'processing';
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Order <?= e($order['order_number']) ?></h4>
    <a href="<?= url('/seller/orders') ?>" class="btn btn-link btn-sm">&larr; All orders</a>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-6"><div class="card h-100"><div class="card-body">
        <h6 class="text-muted text-uppercase small">Customer</h6>
        <div class="fw-semibold"><?= e($order['shipping_name']) ?></div>
        <div class="small text-muted"><?= e($order['shipping_phone']) ?></div>
    </div></div></div>
    <div class="col-md-6"><div class="card h-100"><div class="card-body">
        <h6 class="text-muted text-uppercase small">Ship To</h6>
        <div class="small"><?= nl2br(e($order['shipping_address'])) ?><br><?= e($order['shipping_city']) ?></div>
    </div></div></div>
</div>

<div class="card mb-3"><div class="card-body">
    <h6 class="mb-3">Your items in this order</h6>
    <div class="table-responsive">
        <table class="table tbl-compact align-middle">
            <thead><tr><th>Item</th><th>Qty</th><th>Unit</th><th>Subtotal</th><th>Commission</th><th>Earnings</th><th>Fulfillment</th></tr></thead>
            <tbody>
            <?php foreach ($items as $it): ?>
                <tr>
                    <td><?= e($it['product_name']) ?></td>
                    <td><?= (int)$it['quantity'] ?></td>
                    <td><?= money($it['unit_price']) ?></td>
                    <td><?= money($it['subtotal']) ?></td>
                    <td class="text-muted small"><?= money($it['commission_amount']) ?></td>
                    <td class="fw-semibold"><?= money($it['seller_earnings']) ?></td>
                    <td><span class="badge bg-secondary text-capitalize"><?= e($it['fulfillment']) ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div></div>

<div class="card"><div class="card-body">
    <h6 class="mb-3">Update fulfillment</h6>
    <form method="post" action="<?= url('/seller/orders/'.$order['id'].'/fulfill') ?>" class="d-flex gap-2 align-items-center">
        <?= csrf_field() ?>
        <select name="fulfillment" class="form-select form-select-sm" style="max-width:220px">
            <?php foreach (['processing','shipped','delivered','cancelled'] as $f): ?>
                <option value="<?= $f ?>" <?= $currentStatus===$f?'selected':'' ?>><?= ucfirst($f) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-primary btn-sm">Update Status</button>
    </form>
    <small class="text-muted">Applies to all of your items in this order.</small>
</div></div>
