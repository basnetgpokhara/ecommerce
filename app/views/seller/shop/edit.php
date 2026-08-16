<?php /** @var array $seller */ ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Shop Profile</h4>
    <span class="badge <?= $seller['status']==='active'?'bg-success':($seller['status']==='pending'?'bg-warning text-dark':'bg-danger') ?> text-capitalize"><?= e($seller['status']) ?></span>
</div>

<form method="post" action="<?= url('/seller/shop') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3"><div class="card-body">
                <div class="form-outline mb-3 <?= has_error('shop_name')?'has-error':'' ?>">
                    <input type="text" name="shop_name" class="form-control" value="<?= e(old('shop_name', $seller['shop_name'])) ?>" required>
                    <label class="form-label">Shop name *</label>
                </div>
                <div class="form-outline mb-3">
                    <textarea name="description" class="form-control" rows="3"><?= e(old('description', $seller['description'] ?? '')) ?></textarea>
                    <label class="form-label">About your shop</label>
                </div>
                <div class="row">
                    <div class="col-md-6"><div class="form-outline mb-0">
                        <input type="text" name="contact_phone" class="form-control" value="<?= e(old('contact_phone', $seller['contact_phone'] ?? '')) ?>">
                        <label class="form-label">Contact phone</label>
                    </div></div>
                    <div class="col-md-6"><div class="form-outline mb-0">
                        <input type="email" name="contact_email" class="form-control" value="<?= e(old('contact_email', $seller['contact_email'] ?? '')) ?>">
                        <label class="form-label">Contact email</label>
                    </div></div>
                </div>
            </div></div>
            <div class="card"><div class="card-body">
                <h6 class="mb-3">Commission rate</h6>
                <p class="mb-0 text-muted small">Platform commission on your sales: <strong><?= e(number_format((float)$seller['commission_rate'], 2)) ?>%</strong>. Contact the admin to change this.</p>
            </div></div>
        </div>
        <div class="col-lg-4">
            <div class="card mb-3"><div class="card-body">
                <h6 class="mb-2">Shop logo</h6>
                <?php if ($seller['shop_logo']): ?><img src="<?= media_url($seller['shop_logo']) ?>" class="img-thumbnail mb-2" style="max-height:90px"><?php endif; ?>
                <input type="file" name="shop_logo" class="form-control" accept="image/png,image/jpeg,image/webp">
            </div></div>
            <div class="card"><div class="card-body">
                <h6 class="mb-2">Shop banner</h6>
                <?php if ($seller['shop_banner']): ?><img src="<?= media_url($seller['shop_banner']) ?>" class="img-fluid rounded mb-2"><?php endif; ?>
                <input type="file" name="shop_banner" class="form-control" accept="image/png,image/jpeg,image/webp">
            </div></div>
            <?php if ($e=errors('images')): ?><small class="text-danger d-block mt-2"><?= e($e) ?></small><?php endif; ?>
        </div>
    </div>
    <div class="mt-3"><button class="btn btn-primary">Save Shop Profile</button></div>
</form>
