<?php /** @var array $order @var array $items @var array $payments @var array|null $customer */ ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Order <?= e($order['order_number']) ?></h4>
    <a href="<?= url('/admin/orders') ?>" class="btn btn-link btn-sm">&larr; All orders</a>
</div>

<div class="row g-3 mb-3">
    <div class="col-md-4"><div class="card h-100"><div class="card-body">
        <h6 class="text-muted text-uppercase small">Customer</h6>
        <div class="fw-semibold"><?= e($customer['name'] ?? '—') ?></div>
        <div class="small text-muted"><?= e($customer['email'] ?? '') ?></div>
    </div></div></div>
    <div class="col-md-4"><div class="card h-100"><div class="card-body">
        <h6 class="text-muted text-uppercase small">Shipping</h6>
        <div class="small"><?= e($order['shipping_name']) ?><br><?= nl2br(e($order['shipping_address'])) ?><br><?= e($order['shipping_city']) ?><br><?= e($order['shipping_phone']) ?></div>
    </div></div></div>
    <div class="col-md-4"><div class="card h-100"><div class="card-body">
        <h6 class="text-muted text-uppercase small">Payment</h6>
        <div class="small text-capitalize">Method: <?= e($order['payment_method']) ?></div>
        <div class="small">Status: <span class="badge bg-light text-dark"><?= ucfirst(e($order['payment_status'])) ?></span></div>
        <div class="small mt-1">Placed: <?= e(date('M j, Y g:i A', strtotime($order['created_at']))) ?></div>
    </div></div></div>
</div>

<div class="card mb-3"><div class="card-body">
    <h6 class="mb-3">Items</h6>
    <div class="table-responsive">
        <table class="table tbl-compact align-middle">
            <thead><tr><th>Item</th><th>Seller</th><th>Qty</th><th>Unit</th><th>Subtotal</th><th>Comm.</th><th>Fulfillment</th></tr></thead>
            <tbody>
            <?php foreach ($items as $it): ?>
                <tr>
                    <td class="small"><?= e($it['product_name']) ?></td>
                    <td class="small text-muted">#<?= (int)$it['seller_id'] ?></td>
                    <td><?= (int)$it['quantity'] ?></td>
                    <td><?= money($it['unit_price']) ?></td>
                    <td><?= money($it['subtotal']) ?></td>
                    <td class="small text-muted"><?= money($it['commission_amount']) ?></td>
                    <td><span class="badge bg-light text-dark text-capitalize"><?= e($it['fulfillment']) ?></span></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="d-flex justify-content-end">
        <div style="min-width:240px">
            <div class="d-flex justify-content-between small text-muted"><span>Subtotal</span><span><?= money($order['subtotal']) ?></span></div>
            <div class="d-flex justify-content-between small text-muted"><span>Shipping</span><span><?= money($order['shipping_fee']) ?></span></div>
            <div class="d-flex justify-content-between fw-bold"><span>Total</span><span><?= money($order['total']) ?></span></div>
        </div>
    </div>
</div></div>

<div class="card"><div class="card-body">
    <h6 class="mb-3">Update order status</h6>
    <form method="post" action="<?= url('/admin/orders/'.$order['id'].'/status') ?>" class="d-flex gap-2 align-items-center">
        <?= csrf_field() ?>
        <select name="status" class="form-select form-select-sm" style="max-width:220px">
            <?php foreach (['placed','confirmed','shipped','delivered','cancelled','refunded'] as $st): ?>
                <option value="<?= $st ?>" <?= $order['status']===$st?'selected':'' ?>><?= ucfirst($st) ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-primary btn-sm">Update Status</button>
        <a class="btn btn-outline-secondary btn-sm" href="<?= url('/order/'.$order['id'].'/invoice') ?>" target="_blank"><i class="fas fa-print me-1"></i>Invoice</a>
    </form>
</div></div>
