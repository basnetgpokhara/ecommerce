<?php /** @var array $listing */
$statusBadge = function (string $s): string {
    return ['placed'=>'bg-info text-dark','confirmed'=>'bg-primary','shipped'=>'bg-secondary','delivered'=>'bg-success','cancelled'=>'bg-danger','refunded'=>'bg-warning text-dark'][$s] ?? 'bg-light text-dark';
};
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Orders</h4>
    <span class="text-muted small"><?= (int)$listing['total'] ?> total</span>
</div>

<form method="get" class="admin-toolbar">
    <input type="text" name="q" class="form-control form-control-sm" placeholder="Order # / customer" value="<?= e($_GET['q'] ?? '') ?>" style="max-width:240px">
    <select name="status" class="form-select form-select-sm" style="max-width:160px" onchange="this.form.submit()">
        <option value="">All status</option>
        <?php foreach (['placed','confirmed','shipped','delivered','cancelled','refunded'] as $st): ?>
            <option value="<?= $st ?>" <?= (($_GET['status'] ?? '')===$st)?'selected':'' ?>><?= ucfirst($st) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn btn-outline-primary btn-sm"><i class="fas fa-search"></i></button>
    <a href="<?= url('/admin/orders') ?>" class="btn btn-link btn-sm">Reset</a>
</form>

<div class="card"><div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover tbl-compact align-middle">
            <thead><tr><th>Order</th><th>Customer</th><th>Date</th><th>Payment</th><th>Total</th><th>Status</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($listing['items'] as $o): ?>
                <tr>
                    <td class="small fw-semibold"><?= e($o['order_number']) ?></td>
                    <td class="small"><?= e($o['customer_name']) ?><div class="text-muted"><?= e($o['customer_email']) ?></div></td>
                    <td class="small text-muted"><?= e(date('M j, Y', strtotime($o['created_at']))) ?></td>
                    <td class="small text-capitalize"><?= e($o['payment_method']) ?> · <?= e($o['payment_status']) ?></td>
                    <td class="fw-semibold"><?= money($o['total']) ?></td>
                    <td><span class="badge <?= $statusBadge($o['status']) ?>"><?= ucfirst(e($o['status'])) ?></span></td>
                    <td class="text-end"><a href="<?= url('/admin/orders/'.$o['id']) ?>" class="btn btn-sm btn-outline-primary">View</a></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$listing['items']): ?><tr><td colspan="7" class="text-center text-muted py-4">No orders found.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div></div>
