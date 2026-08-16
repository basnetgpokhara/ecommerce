<?php /** @var array $listing @var array $sellers */
$badge = ['approved'=>'bg-success','pending'=>'bg-warning text-dark','rejected'=>'bg-danger','inactive'=>'bg-secondary'];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Products</h4>
    <span class="text-muted small"><?= (int)$listing['total'] ?> total</span>
</div>

<form method="get" class="admin-toolbar">
    <input type="text" name="q" class="form-control form-control-sm" placeholder="Search name / SKU / shop" value="<?= e($_GET['q'] ?? '') ?>" style="max-width:220px">
    <select name="status" class="form-select form-select-sm" style="max-width:150px" onchange="this.form.submit()">
        <option value="">All status</option>
        <?php foreach (['approved','pending','rejected','inactive'] as $st): ?>
            <option value="<?= $st ?>" <?= (($_GET['status'] ?? '')===$st)?'selected':'' ?>><?= ucfirst($st) ?></option>
        <?php endforeach; ?>
    </select>
    <select name="seller_id" class="form-select form-select-sm" style="max-width:180px" onchange="this.form.submit()">
        <option value="">All sellers</option>
        <?php foreach ($sellers as $s): ?>
            <option value="<?= $s['id'] ?>" <?= (($_GET['seller_id'] ?? '')===(string)$s['id'])?'selected':'' ?>><?= e($s['shop_name']) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn btn-outline-primary btn-sm"><i class="fas fa-search"></i></button>
    <a href="<?= url('/admin/products') ?>" class="btn btn-link btn-sm">Reset</a>
</form>

<div class="card"><div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover tbl-compact align-middle">
            <thead><tr><th></th><th>Name</th><th>Shop</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th>Featured</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php foreach ($listing['items'] as $p): $eff = $p['discount_price']?(float)$p['discount_price']:(float)$p['price']; ?>
                <tr>
                    <td><img src="<?= media_url(\App\Models\ProductImage::primaryFor((int)$p['id'])) ?>" class="thumb-sm"></td>
                    <td><a href="<?= url('/product/'.$p['slug']) ?>" class="fw-semibold text-dark text-decoration-none"><?= e($p['name']) ?></a></td>
                    <td class="small"><?= e($p['shop_name'] ?? '—') ?></td>
                    <td class="small"><?= e($p['category_name'] ?? '—') ?></td>
                    <td class="fw-semibold"><?= money($eff) ?></td>
                    <td><?= (int)$p['stock'] ?></td>
                    <td><span class="badge <?= $badge[$p['status']] ?? 'bg-light' ?> text-capitalize"><?= e($p['status']) ?></span></td>
                    <td>
                        <form method="post" action="<?= url('/admin/products/'.$p['id'].'/feature') ?>" class="d-inline"><?= csrf_field() ?>
                            <button class="btn btn-sm btn-link p-0" title="Toggle featured"><i class="fa<?= $p['is_featured']?'s':'r' ?> fa-star <?= $p['is_featured']?'text-warning':'text-muted' ?>"></i></button>
                        </form>
                    </td>
                    <td class="text-end text-nowrap">
                        <?php if ($p['status']!=='approved'): ?>
                            <form method="post" action="<?= url('/admin/products/'.$p['id'].'/approve') ?>" class="d-inline"><?= csrf_field() ?><button class="btn btn-sm btn-outline-success" title="Approve"><i class="fas fa-check"></i></button></form>
                        <?php endif; ?>
                        <?php if ($p['status']!=='rejected'): ?>
                            <form method="post" action="<?= url('/admin/products/'.$p['id'].'/reject') ?>" class="d-inline"><?= csrf_field() ?><button class="btn btn-sm btn-outline-warning" title="Reject"><i class="fas fa-ban"></i></button></form>
                        <?php endif; ?>
                        <form method="post" action="<?= url('/admin/products/'.$p['id'].'/delete') ?>" class="d-inline" onsubmit="return confirm('Remove this product?')"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button></form>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$listing['items']): ?><tr><td colspan="9" class="text-center text-muted py-4">No products found.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div></div>
