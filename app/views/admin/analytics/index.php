<?php /** @var int $days @var array $chartLabels @var array $chartRevenue @var array $chartOrders @var array $kpis @var array $byStatus @var array $byMethod @var array $topSellers @var array $topProducts @var array $couponUsage */ ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Analytics &amp; Reports</h4>
    <form method="get" class="d-flex align-items-center gap-2">
        <label class="small text-muted mb-0">Last</label>
        <select name="days" class="form-select form-select-sm" style="width:auto" onchange="this.form.submit()">
            <?php foreach ([7, 14, 30, 60, 90] as $d): ?>
                <option value="<?= $d ?>" <?= $days===$d?'selected':'' ?>><?= $d ?> days</option>
            <?php endforeach; ?>
        </select>
    </form>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-2"><div class="kpi-card kpi-green"><div class="kpi-value"><?= money($kpis['revenue']) ?></div><div class="kpi-label">Revenue</div></div></div>
    <div class="col-6 col-md-2"><div class="kpi-card kpi-blue"><div class="kpi-value"><?= (int)$kpis['orders'] ?></div><div class="kpi-label">Orders</div></div></div>
    <div class="col-6 col-md-2"><div class="kpi-card kpi-purple"><div class="kpi-value"><?= money($kpis['avg_order']) ?></div><div class="kpi-label">Avg. order</div></div></div>
    <div class="col-6 col-md-2"><div class="kpi-card kpi-amber"><div class="kpi-value"><?= (int)$kpis['customers'] ?></div><div class="kpi-label">New customers</div></div></div>
    <div class="col-6 col-md-2"><div class="kpi-card kpi-green"><div class="kpi-value"><?= (int)$kpis['products'] ?></div><div class="kpi-label">Products</div></div></div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card"><div class="card-body">
            <h5 class="mb-1">Revenue</h5>
            <p class="text-muted small mb-3">Daily gross sales (excl. cancelled orders)</p>
            <canvas id="revenueChart" height="110"></canvas>
        </div></div>
    </div>
    <div class="col-lg-4">
        <div class="card"><div class="card-body">
            <h5 class="mb-1">Orders</h5>
            <p class="text-muted small mb-3">Daily order count</p>
            <canvas id="ordersChart" height="110"></canvas>
        </div></div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-4">
        <div class="card"><div class="card-body">
            <h5 class="mb-3">Orders by status</h5>
            <canvas id="statusChart" height="150"></canvas>
            <div class="mt-3">
                <?php foreach ($byStatus as $r): ?>
                    <div class="d-flex justify-content-between small border-bottom py-1">
                        <span class="text-capitalize"><?= e($r['status']) ?></span>
                        <span><?= (int)$r['n'] ?> · <?= money($r['revenue']) ?></span>
                    </div>
                <?php endforeach; ?>
                <?php if (!$byStatus): ?><p class="text-muted text-center py-2 mb-0">No data</p><?php endif; ?>
            </div>
        </div></div>
    </div>

    <div class="col-lg-4">
        <div class="card"><div class="card-body">
            <h5 class="mb-3">Payment methods</h5>
            <canvas id="methodChart" height="150"></canvas>
            <div class="mt-3">
                <?php foreach ($byMethod as $r): ?>
                    <div class="d-flex justify-content-between small border-bottom py-1">
                        <span class="text-capitalize"><?= e($r['payment_method']) ?></span>
                        <span><?= (int)$r['n'] ?> · <?= money($r['revenue']) ?></span>
                    </div>
                <?php endforeach; ?>
                <?php if (!$byMethod): ?><p class="text-muted text-center py-2 mb-0">No data</p><?php endif; ?>
            </div>
        </div></div>
    </div>

    <div class="col-lg-4">
        <div class="card"><div class="card-body">
            <h5 class="mb-3">Coupon usage</h5>
            <?php if ($couponUsage): ?>
                <?php foreach ($couponUsage as $c): ?>
                    <div class="d-flex justify-content-between small border-bottom py-1">
                        <span class="fw-semibold text-uppercase"><?= e($c['coupon_code']) ?></span>
                        <span><?= (int)$c['uses'] ?>× · −<?= money($c['total_discount']) ?></span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted text-center py-2 mb-0">No coupons used in this period.</p>
            <?php endif; ?>
        </div></div>
    </div>
</div>

<div class="row g-4 mt-1">
    <div class="col-lg-6">
        <div class="card"><div class="card-body">
            <h5 class="mb-3">Top sellers by earnings</h5>
            <div class="table-responsive"><table class="table tbl-compact align-middle mb-0">
                <thead><tr><th>Shop</th><th class="text-end">Orders</th><th class="text-end">Earnings</th></tr></thead>
                <tbody>
                <?php foreach ($topSellers as $s): ?>
                    <tr><td class="fw-semibold"><?= e($s['shop_name']) ?></td><td class="text-end"><?= (int)$s['orders'] ?></td><td class="text-end"><?= money($s['earnings']) ?></td></tr>
                <?php endforeach; ?>
                <?php if (!$topSellers): ?><tr><td colspan="3" class="text-center text-muted py-3">No data</td></tr><?php endif; ?>
                </tbody>
            </table></div>
        </div></div>
    </div>
    <div class="col-lg-6">
        <div class="card"><div class="card-body">
            <h5 class="mb-3">Top products by revenue</h5>
            <div class="table-responsive"><table class="table tbl-compact align-middle mb-0">
                <thead><tr><th>Product</th><th class="text-end">Sold</th><th class="text-end">Revenue</th></tr></thead>
                <tbody>
                <?php foreach ($topProducts as $p): ?>
                    <tr><td class="fw-semibold"><?= e($p['product_name']) ?></td><td class="text-end"><?= (int)$p['qty'] ?></td><td class="text-end"><?= money($p['revenue']) ?></td></tr>
                <?php endforeach; ?>
                <?php if (!$topProducts): ?><tr><td colspan="3" class="text-center text-muted py-3">No data</td></tr><?php endif; ?>
                </tbody>
            </table></div>
        </div></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
(function () {
    var labels = <?= json_encode($chartLabels) ?>;
    var revenue = <?= json_encode($chartRevenue) ?>;
    var orders  = <?= json_encode($chartOrders) ?>;

    new Chart(document.getElementById('revenueChart'), {
        type: 'line',
        data: { labels: labels, datasets: [{ label: 'Revenue', data: revenue, borderColor: '#3b71ca', backgroundColor: 'rgba(59,113,202,.12)', fill: true, tension: .3 }] },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });
    new Chart(document.getElementById('ordersChart'), {
        type: 'bar',
        data: { labels: labels, datasets: [{ label: 'Orders', data: orders, backgroundColor: '#20c997' }] },
        options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });
    new Chart(document.getElementById('statusChart'), {
        type: 'doughnut',
        data: { labels: <?= json_encode(array_map(fn($r) => ucfirst($r['status']), $byStatus)) ?>, datasets: [{ data: <?= json_encode(array_map(fn($r) => (int)$r['n'], $byStatus)) ?>, backgroundColor: ['#3b71ca','#20c997','#6f42c1','#fd7e14','#dc3545','#ffc107','#0dcaf0'] }] },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
    new Chart(document.getElementById('methodChart'), {
        type: 'doughnut',
        data: { labels: <?= json_encode(array_map(fn($r) => strtoupper($r['payment_method']), $byMethod)) ?>, datasets: [{ data: <?= json_encode(array_map(fn($r) => (int)$r['n'], $byMethod)) ?>, backgroundColor: ['#6f42c1','#0dcaf0','#fd7e14','#20c997','#dc3545'] }] },
        options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
    });
})();
</script>
