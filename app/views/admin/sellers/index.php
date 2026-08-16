<?php /** @var array $pending @var array $sellers */
$statusBadge = ['active'=>'bg-success','pending'=>'bg-warning text-dark','suspended'=>'bg-danger'];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Sellers</h4>
</div>

<?php if ($pending): ?>
<div class="card border-warning mb-3"><div class="card-body">
    <h6 class="text-warning mb-3"><i class="fas fa-clock me-1"></i> Pending applications (<?= count($pending) ?>)</h6>
    <div class="table-responsive">
        <table class="table tbl-compact align-middle mb-0">
            <thead><tr><th>Shop</th><th>Owner</th><th>Email</th><th>Phone</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php foreach ($pending as $s): ?>
                <tr>
                    <td class="fw-semibold"><?= e($s['shop_name']) ?></td>
                    <td class="small"><?= e($s['owner_name']) ?></td>
                    <td class="small"><?= e($s['email']) ?></td>
                    <td class="small"><?= e($s['phone'] ?: '—') ?></td>
                    <td class="text-end text-nowrap">
                        <form method="post" action="<?= url('/admin/sellers/'.$s['id'].'/approve') ?>" class="d-inline"><?= csrf_field() ?><button class="btn btn-sm btn-success"><i class="fas fa-check me-1"></i>Approve</button></form>
                        <form method="post" action="<?= url('/admin/sellers/'.$s['id'].'/suspend') ?>" class="d-inline"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="fas fa-xmark"></i></button></form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div></div>
<?php endif; ?>

<div class="card"><div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover tbl-compact align-middle">
            <thead><tr><th>Shop</th><th>Owner</th><th>Commission</th><th>Products</th><th>Earnings</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php foreach ($sellers['items'] as $s): ?>
                <tr>
                    <td class="fw-semibold"><?= e($s['shop_name']) ?><div class="small text-muted"><a href="<?= url('/shop/'.$s['slug']) ?>" target="_blank">view shop</a></div></td>
                    <td class="small"><?= e($s['owner_name']) ?><div class="small text-muted"><?= e($s['email']) ?></div></td>
                    <td><?= e(number_format((float)$s['commission_rate'], 2)) ?>%</td>
                    <td><?= (int)$s['product_count'] ?></td>
                    <td class="fw-semibold"><?= money($s['earnings']) ?></td>
                    <td><span class="badge <?= $statusBadge[$s['status']] ?? 'bg-light' ?> text-capitalize"><?= e($s['status']) ?></span></td>
                    <td class="text-end text-nowrap">
                        <?php if ($s['status']!=='active'): ?>
                            <form method="post" action="<?= url('/admin/sellers/'.$s['id'].'/approve') ?>" class="d-inline"><?= csrf_field() ?><button class="btn btn-sm btn-outline-success" title="Activate"><i class="fas fa-check"></i></button></form>
                        <?php else: ?>
                            <form method="post" action="<?= url('/admin/sellers/'.$s['id'].'/suspend') ?>" class="d-inline"><?= csrf_field() ?><button class="btn btn-sm btn-outline-warning" title="Suspend"><i class="fas fa-pause"></i></button></form>
                        <?php endif; ?>
                        <a href="<?= url('/admin/sellers/'.$s['id'].'/edit') ?>" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-pen"></i></a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$sellers['items']): ?><tr><td colspan="7" class="text-center text-muted py-4">No sellers yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php \App\Core\View::partial('pagination', ['listing' => $sellers, 'base' => '/admin/sellers']); ?>
</div></div>
