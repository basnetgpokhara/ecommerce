<?php /** @var array $orders */
$statusBadge = function (string $s): string {
    return ['placed'=>'bg-info text-dark','confirmed'=>'bg-primary','shipped'=>'bg-secondary','delivered'=>'bg-success','cancelled'=>'bg-danger','refunded'=>'bg-warning text-dark'][$s] ?? 'bg-light text-dark';
};
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Orders</h4>
</div>
<div class="card"><div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover tbl-compact align-middle">
            <thead><tr><th>Order</th><th>Customer</th><th>Date</th><th>Items</th><th>Your Earnings</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($orders as $o): ?>
                <tr>
                    <td class="small fw-semibold"><?= e($o['order_number']) ?></td>
                    <td class="small"><?= e($o['customer_name']) ?></td>
                    <td class="small text-muted"><?= e(date('M j, Y', strtotime($o['created_at']))) ?></td>
                    <td><?= (int)$o['item_count'] ?></td>
                    <td class="fw-semibold"><?= money($o['earnings']) ?></td>
                    <td><span class="badge <?= $statusBadge($o['status']) ?>"><?= ucfirst(e($o['status'])) ?></span></td>
                    <td class="text-end"><a href="<?= url('/seller/orders/'.$o['id']) ?>" class="btn btn-sm btn-outline-primary">Manage</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$orders): ?><tr><td colspan="7" class="text-center text-muted py-4">No orders containing your products yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div></div>
