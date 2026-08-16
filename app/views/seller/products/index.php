<?php /** @var array $products @var string $q */ ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">My Products</h4>
    <a href="<?= url('/seller/products/create') ?>" class="btn btn-primary btn-sm"><i class="fas fa-plus me-1"></i> Add Product</a>
</div>

<form method="get" class="admin-toolbar">
    <input type="text" name="q" class="form-control form-control-sm" placeholder="Search products…" value="<?= e($q) ?>" style="max-width:260px">
    <button class="btn btn-outline-primary btn-sm"><i class="fas fa-search"></i></button>
    <a href="<?= url('/seller/products') ?>" class="btn btn-link btn-sm">Reset</a>
</form>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover tbl-compact align-middle">
                <thead><tr><th></th><th>Name</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th></th></tr></thead>
                <tbody>
                <?php foreach ($products as $p):
                    $eff = $p['discount_price'] ? (float)$p['discount_price'] : (float)$p['price'];
                    $badge = ['approved'=>'bg-success','pending'=>'bg-warning text-dark','rejected'=>'bg-danger','inactive'=>'bg-secondary'][$p['status']] ?? 'bg-light';
                ?>
                    <tr>
                        <td><img src="<?= media_url(\App\Models\ProductImage::primaryFor((int)$p['id'])) ?>" class="thumb-sm"></td>
                        <td><a href="<?= url('/product/'.$p['slug']) ?>" class="fw-semibold text-dark text-decoration-none"><?= e($p['name']) ?></a><div class="small text-muted"><?= e($p['sku'] ?: '—') ?></div></td>
                        <td class="small"><?= e($p['category_name'] ?? '—') ?></td>
                        <td class="fw-semibold"><?= money($eff) ?></td>
                        <td><?= (int)$p['stock'] ?></td>
                        <td><span class="badge <?= $badge ?> text-capitalize"><?= e($p['status']) ?></span></td>
                        <td class="text-end text-nowrap">
                            <a href="<?= url('/seller/products/'.$p['id'].'/edit') ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-pen"></i></a>
                            <form method="post" action="<?= url('/seller/products/'.$p['id'].'/delete') ?>" class="d-inline" onsubmit="return confirm('Remove this product?')">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$products): ?><tr><td colspan="7" class="text-center text-muted py-4">No products yet. Click “Add Product”.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
