<?php /** @var array $orders */
$statusBadge = function (string $s): string {
    return ['placed'=>'bg-info text-dark','confirmed'=>'bg-primary','shipped'=>'bg-secondary','delivered'=>'bg-success','cancelled'=>'bg-danger','refunded'=>'bg-warning text-dark'][$s] ?? 'bg-light text-dark';
};
?>
<div class="dash-head mb-4">
    <h4 class="fw-bold mb-0">My Orders</h4>
    <p class="text-muted mb-0">Track and review your purchases.</p>
</div>

<div class="card">
    <div class="card-body">
        <?php if ($orders): ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead><tr><th>Order #</th><th>Date</th><th>Payment</th><th>Status</th><th>Total</th><th></th></tr></thead>
                    <tbody>
                    <?php foreach ($orders as $o): ?>
                        <tr>
                            <td class="fw-semibold"><?= e($o['order_number']) ?></td>
                            <td class="small text-muted"><?= e(date('M j, Y', strtotime($o['created_at']))) ?></td>
                            <td><span class="badge bg-light text-dark text-capitalize"><?= e($o['payment_method']) ?></span></td>
                            <td><span class="badge <?= $statusBadge($o['status']) ?>"><?= ucfirst(e($o['status'])) ?></span></td>
                            <td class="fw-bold"><?= money($o['total']) ?></td>
                            <td class="text-end"><a href="<?= url('/account/orders/' . $o['id']) ?>" class="btn btn-sm btn-outline-primary">Details</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-4">
                <p class="text-muted mb-2">No orders yet.</p>
                <a href="<?= url('/') ?>" class="btn btn-primary btn-sm">Start Shopping</a>
            </div>
        <?php endif; ?>
    </div>
</div>
