<?php
/** @var array $counts @var array $orders */
$statusBadge = function (string $s): string {
    return [
        'placed'    => 'bg-info text-dark',
        'confirmed' => 'bg-primary',
        'shipped'   => 'bg-secondary',
        'delivered' => 'bg-success',
        'cancelled' => 'bg-danger',
        'refunded'  => 'bg-warning text-dark',
    ][$s] ?? 'bg-light text-dark';
};
?>
<div class="dash-head mb-4">
    <h4 class="fw-bold mb-0">Hello, <?= e(explode(' ', current_user()['name'])[0]) ?> 👋</h4>
    <p class="text-muted mb-0">Here's an overview of your account.</p>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-blue">
            <div class="kpi-value"><?= (int)$counts['total'] ?></div>
            <div class="kpi-label">Total Orders</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-amber">
            <div class="kpi-value"><?= (int)$counts['pending'] ?></div>
            <div class="kpi-label">In Progress</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-green">
            <div class="kpi-value"><?= (int)$counts['delivered'] ?></div>
            <div class="kpi-label">Delivered</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-card kpi-red">
            <div class="kpi-value"><?= (int)$counts['cancelled'] ?></div>
            <div class="kpi-label">Cancelled</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Recent Orders</h5>
            <a href="<?= url('/account/orders') ?>" class="small fw-bold">View all</a>
        </div>
        <?php if ($orders): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Order</th><th>Date</th><th>Status</th><th>Total</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td class="fw-semibold"><?= e($o['order_number']) ?></td>
                            <td class="small text-muted"><?= e(date('M j, Y', strtotime($o['created_at']))) ?></td>
                            <td><span class="badge <?= $statusBadge($o['status']) ?>"><?= ucfirst(e($o['status'])) ?></span></td>
                            <td class="fw-bold"><?= money($o['total']) ?></td>
                            <td class="text-end"><a href="<?= url('/account/orders/' . $o['id']) ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <p class="text-muted mb-2">You haven't placed any orders yet.</p>
                <a href="<?= url('/') ?>" class="btn btn-primary btn-sm">Start Shopping</a>
            </div>
        <?php endif; ?>
    </div>
</div>
