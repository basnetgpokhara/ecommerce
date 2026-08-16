<?php /** @var array $seller */ ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Edit Seller — <?= e($seller['shop_name']) ?></h4>
    <a href="<?= url('/admin/sellers') ?>" class="btn btn-link btn-sm">&larr; Back</a>
</div>
<form method="post" action="<?= url('/admin/sellers/'.$seller['id']) ?>" class="col-lg-7">
    <?= csrf_field() ?>
    <div class="card"><div class="card-body">
        <div class="form-outline mb-3 <?= has_error('shop_name')?'has-error':'' ?>">
            <input type="text" name="shop_name" class="form-control" value="<?= e(old('shop_name', $seller['shop_name'])) ?>" required>
            <label class="form-label">Shop name</label>
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <div class="form-outline mb-3 <?= has_error('commission_rate')?'has-error':'' ?>">
                    <input type="number" step="0.01" min="0" max="100" name="commission_rate" class="form-control" value="<?= e(old('commission_rate', $seller['commission_rate'])) ?>" required>
                    <label class="form-label">Commission rate (%)</label>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-outline mb-3 <?= has_error('status')?'has-error':'' ?>">
                    <select name="status" class="form-select">
                        <?php foreach (['active','pending','suspended'] as $st): ?>
                            <option value="<?= $st ?>" <?= old('status', $seller['status'])===$st?'selected':'' ?>><?= ucfirst($st) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label class="form-label">Status</label>
                </div>
            </div>
        </div>
        <div class="form-outline mb-3">
            <input type="text" name="contact_phone" class="form-control" value="<?= e(old('contact_phone', $seller['contact_phone'] ?? '')) ?>">
            <label class="form-label">Contact phone</label>
        </div>
        <div class="form-outline mb-3">
            <input type="email" name="contact_email" class="form-control" value="<?= e(old('contact_email', $seller['email'] ?? '')) ?>">
            <label class="form-label">Contact email</label>
        </div>
        <div class="form-outline mb-3">
            <textarea name="description" class="form-control" rows="3"><?= e(old('description', $seller['description'] ?? '')) ?></textarea>
            <label class="form-label">Description</label>
        </div>
        <button class="btn btn-primary">Save Seller</button>
        <a href="<?= url('/admin/sellers') ?>" class="btn btn-link">Cancel</a>
    </div></div>
</form>
