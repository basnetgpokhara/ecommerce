<?php /** @var array $reviews */ ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Product Reviews</h4>
</div>
<div class="card"><div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover tbl-compact align-middle">
            <thead><tr><th>Product</th><th>Customer</th><th>Rating</th><th>Comment</th><th>Status</th><th>Date</th></tr></thead>
            <tbody>
            <?php foreach ($reviews as $r): ?>
                <tr>
                    <td><a href="<?= url('/product/'.$r['product_slug']) ?>" class="text-dark text-decoration-none"><?= e($r['product_name']) ?></a></td>
                    <td class="small"><?= e($r['customer_name']) ?></td>
                    <td><?php for ($i=1;$i<=5;$i++): ?><i class="fa<?= $i<=(int)$r['rating']?'s':'r' ?> fa-star text-warning"></i><?php endfor; ?></td>
                    <td class="small"><?= e($r['comment']) ?></td>
                    <td><span class="badge <?= $r['status']==='approved'?'bg-success':($r['status']==='pending'?'bg-warning text-dark':'bg-danger') ?> text-capitalize"><?= e($r['status']) ?></span></td>
                    <td class="small text-muted"><?= e(date('M j, Y', strtotime($r['created_at']))) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$reviews): ?><tr><td colspan="6" class="text-center text-muted py-4">No reviews on your products yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div></div>
