<?php /** @var array $kpis @var array $recentOrders @var array $topProducts */ ?>
<div class="dash-head mb-4">
    <h4 class="fw-bold mb-0">Admin Dashboard</h4>
    <p class="text-muted mb-0">Marketplace overview across all sellers and customers.</p>
</div>

<?php if (!empty($pendingSellers) || !empty($pendingApprovals)): ?>
<div class="row g-2 mb-4">
    <?php if (!empty($pendingSellers)): ?>
        <div class="col-md-6"><a href="<?= url('/admin/sellers') ?>" class="card text-decoration-none text-dark"><div class="card-body d-flex align-items-center gap-3">
            <i class="fas fa-store fa-2x text-warning"></i><div><div class="fw-bold"><?= (int)$pendingSellers ?> seller(s) awaiting approval</div><div class="small text-muted">Review applications →</div></div>
        </div></a></div>
    <?php endif; ?>
    <?php if (!empty($pendingApprovals)): ?>
        <div class="col-md-6"><a href="<?= url('/admin/products?status=pending') ?>" class="card text-decoration-none text-dark"><div class="card-body d-flex align-items-center gap-3">
            <i class="fas fa-tags fa-2x text-warning"></i><div><div class="fw-bold"><?= (int)$pendingApprovals ?> product(s) pending review</div><div class="small text-muted">Approve or reject →</div></div>
        </div></a></div>
    <?php endif; ?>
</div>
<?php endif; ?>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="kpi-card kpi-green"><div class="kpi-value"><?= money($kpis['sales']) ?></div><div class="kpi-label">Gross Sales</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-blue"><div class="kpi-value"><?= (int)$kpis['orders'] ?></div><div class="kpi-label">Orders</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-purple"><div class="kpi-value"><?= (int)$kpis['users'] ?></div><div class="kpi-label">Customers</div></div></div>
    <div class="col-6 col-md-3"><div class="kpi-card kpi-amber"><div class="kpi-value"><?= (int)$kpis['sellers'] ?></div><div class="kpi-label">Sellers</div></div></div>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">Recent Orders</h5>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead><tr><th>Order</th><th>Customer</th><th>Status</th><th>Total</th></tr></thead>
                        <tbody>
                        <?php foreach ($recentOrders as $o): ?>
                            <tr>
                                <td class="small"><?= e($o['order_number']) ?></td>
                                <td class="small"><?= e($o['customer_name']) ?></td>
                                <td class="text-capitalize small"><?= e($o['status']) ?></td>
                                <td class="fw-semibold"><?= money($o['total']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if (!$recentOrders): ?><tr><td colspan="4" class="text-center text-muted py-3">No orders yet.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">Top Products</h5>
                <?php if ($topProducts): ?>
                    <?php foreach ($topProducts as $p): ?>
                        <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                            <span class="small"><?= e($p['product_name']) ?></span>
                            <span class="small"><span class="badge bg-light text-dark me-1"><?= (int)$p['qty'] ?> sold</span> <?= money($p['revenue']) ?></span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-muted text-center py-3 mb-0">No sales data yet.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="dash-phase-note mt-3">
    <i class="fas fa-hammer me-1"></i> User, seller, product, category, order &amp; payment-gateway management arrive in Phase 2.
</div>
