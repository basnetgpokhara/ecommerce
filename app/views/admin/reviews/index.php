<?php /** @var array $reviews */ ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Reviews</h4>
</div>
<form method="get" class="admin-toolbar">
    <select name="status" class="form-select form-select-sm" style="max-width:180px" onchange="this.form.submit()">
        <option value="">All status</option>
        <?php foreach (['approved','pending','rejected'] as $st): ?>
            <option value="<?= $st ?>" <?= (($_GET['status'] ?? '')===$st)?'selected':'' ?>><?= ucfirst($st) ?></option>
        <?php endforeach; ?>
    </select>
</form>
<div class="card"><div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover tbl-compact align-middle">
            <thead><tr><th>Product</th><th>Customer</th><th>Rating</th><th>Comment</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php foreach ($reviews['items'] as $r): ?>
                <tr>
                    <td class="small"><?= e($r['product_name']) ?></td>
                    <td class="small"><?= e($r['customer_name']) ?></td>
                    <td><?php for ($i=1;$i<=5;$i++): ?><i class="fa<?= $i<=(int)$r['rating']?'s':'r' ?> fa-star text-warning"></i><?php endfor; ?></td>
                    <td class="small"><?= e($r['comment']) ?></td>
                    <td><span class="badge <?= $r['status']==='approved'?'bg-success':($r['status']==='pending'?'bg-warning text-dark':'bg-danger') ?> text-capitalize"><?= e($r['status']) ?></span></td>
                    <td class="text-end text-nowrap">
                        <form method="post" action="<?= url('/admin/reviews/'.$r['id'].'/approved') ?>" class="d-inline"><?= csrf_field() ?><button class="btn btn-sm btn-outline-success" title="Approve"><i class="fas fa-check"></i></button></form>
                        <form method="post" action="<?= url('/admin/reviews/'.$r['id'].'/rejected') ?>" class="d-inline"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger" title="Reject"><i class="fas fa-xmark"></i></button></form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$reviews['items']): ?><tr><td colspan="6" class="text-center text-muted py-4">No reviews.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php \App\Core\View::partial('pagination', ['listing' => $reviews, 'base' => '/admin/reviews']); ?>
</div></div>
