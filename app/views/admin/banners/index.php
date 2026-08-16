<?php /** @var array $banners */ ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Banners</h4>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card"><div class="card-body">
            <h6 class="mb-3"><i class="fas fa-plus me-1"></i> Add banner</h6>
            <form method="post" action="<?= url('/admin/banners') ?>" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="form-outline mb-3"><input type="text" name="title" class="form-control"><label class="form-label">Title</label></div>
                <div class="form-outline mb-3"><input type="text" name="subtitle" class="form-control"><label class="form-label">Subtitle</label></div>
                <div class="form-outline mb-3"><input type="text" name="link" class="form-control" placeholder="/category/electronics"><label class="form-label">Link (path)</label></div>
                <div class="form-outline mb-3"><input type="text" name="button_text" class="form-control" placeholder="Shop Now"><label class="form-label">Button text</label></div>
                <div class="row g-2">
                    <div class="col-6"><select name="position" class="form-select"><option value="hero">Hero</option><option value="promo">Promo</option></select><label class="form-label d-block">Position</label></div>
                    <div class="col-6"><input type="number" name="sort_order" class="form-control" value="0"><label class="form-label d-block">Order</label></div>
                </div>
                <div class="mb-3 mt-2"><input type="file" name="image" class="form-control" accept="image/png,image/jpeg,image/webp"></div>
                <?php if ($e=errors('image')): ?><small class="text-danger d-block"><?= e($e) ?></small><?php endif; ?>
                <button class="btn btn-primary w-100">Add Banner</button>
            </form>
        </div></div>
    </div>
    <div class="col-lg-8">
        <div class="card"><div class="card-body">
            <div class="table-responsive">
                <table class="table tbl-compact align-middle">
                    <thead><tr><th></th><th>Title</th><th>Position</th><th>Order</th><th>Status</th><th class="text-end"></th></tr></thead>
                    <tbody>
                    <?php foreach ($banners as $b): ?>
                        <tr>
                            <td><?php if ($b['image']): ?><img src="<?= media_url($b['image']) ?>" style="width:80px;height:34px;object-fit:cover;border-radius:4px"><?php endif; ?></td>
                            <td class="small fw-semibold"><?= e($b['title'] ?: '(no title)') ?><div class="text-muted"><?= e($b['subtitle'] ?: '') ?></div></td>
                            <td><span class="badge bg-light text-dark text-capitalize"><?= e($b['position']) ?></span></td>
                            <td class="small"><?= (int)$b['sort_order'] ?></td>
                            <td><span class="badge <?= $b['status']==='active'?'bg-success':'bg-secondary' ?> text-capitalize"><?= e($b['status']) ?></span></td>
                            <td class="text-end">
                                <form method="post" action="<?= url('/admin/banners/'.$b['id'].'/delete') ?>" class="d-inline" onsubmit="return confirm('Delete this banner?')"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$banners): ?><tr><td colspan="6" class="text-center text-muted py-4">No banners yet.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div></div>
    </div>
</div>
