<?php
/** @var array|null $seller @var int $productCount @var int $pending @var float $earnings @var array $recentItems */
$statusBadge = function (string $s): string {
    return ['placed'=>'bg-info text-dark','confirmed'=>'bg-primary','shipped'=>'bg-secondary','delivered'=>'bg-success','cancelled'=>'bg-danger','refunded'=>'bg-warning text-dark'][$s] ?? 'bg-light text-dark';
};
?>
<div class="dash-head mb-4">
    <h4 class="fw-bold mb-0">Seller Dashboard</h4>
    <p class="text-muted mb-0">
        <?= $seller ? e($seller['shop_name']) : 'Your shop' ?>
        <?php if ($seller && $seller['status'] !== 'active'): ?>
            <span class="badge bg-warning text-dark ms-1"><?= ucfirst(e($seller['status'])) ?></span>
        <?php endif; ?>
    </p>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="kpi-card kpi-green"><div class="kpi-value"><?= money($earnings) ?></div><div class="kpi-label">Net Earnings</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-amber"><div class="kpi-value"><?= (int)$pending ?></div><div class="kpi-label">Orders to Fulfill</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-blue"><div class="kpi-value"><?= (int)$productCount ?></div><div class="kpi-label">Products</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-purple"><div class="kpi-value"><?= $seller ? e(number_format((float)$seller['commission_rate'], 0)) . '%' : '—' ?></div><div class="kpi-label">Commission Rate</div></div></div>
</div>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Recent Orders</h5>
        </div>
        <?php if ($recentItems): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Order</th><th>Customer</th><th>Item</th><th>Qty</th><th>Earnings</th><th>Status</th></tr></thead>
                    <tbody>
                    <?php foreach ($recentItems as $it): ?>
                        <tr>
                            <td class="small"><?= e($it['order_number']) ?></td>
                            <td class="small"><?= e($it['shipping_name']) ?></td>
                            <td class="small"><?= e($it['product_name']) ?></td>
                            <td><?= (int)$it['quantity'] ?></td>
                            <td class="fw-semibold"><?= money($it['seller_earnings']) ?></td>
                            <td><span class="badge <?= $statusBadge($it['order_status']) ?>"><?= ucfirst(e($it['order_status'])) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-muted text-center py-4 mb-0">No orders containing your products yet.</p>
        <?php endif; ?>
    </div>
</div>

<div class="dash-phase-note mt-3">
    <i class="fas fa-hammer me-1"></i> Product CRUD, shop profile editing and payout requests arrive in Phase 2.
</div>
