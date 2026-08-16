<?php /** @var array $addresses */ ?>
<div class="dash-head mb-4">
    <h4 class="fw-bold mb-0">My Addresses</h4>
    <p class="text-muted mb-0">Saved shipping addresses.</p>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-body">
                <h6 class="mb-3"><i class="fas fa-plus me-1"></i> Add new address</h6>
                <form method="post" action="<?= url('/account/addresses') ?>">
                    <?= csrf_field() ?>
                    <div class="form-outline mb-3 <?= has_error('full_name') ? 'has-error' : '' ?>">
                        <input type="text" name="full_name" class="form-control" value="<?= e(old('full_name')) ?>">
                        <label class="form-label">Full name</label>
                    </div>
                    <div class="form-outline mb-3 <?= has_error('phone') ? 'has-error' : '' ?>">
                        <input type="tel" name="phone" class="form-control" value="<?= e(old('phone')) ?>">
                        <label class="form-label">Phone</label>
                    </div>
                    <div class="form-outline mb-3 <?= has_error('address_line1') ? 'has-error' : '' ?>">
                        <input type="text" name="address_line1" class="form-control" value="<?= e(old('address_line1')) ?>">
                        <label class="form-label">Street address</label>
                    </div>
                    <div class="form-outline mb-3">
                        <input type="text" name="address_line2" class="form-control" value="<?= e(old('address_line2')) ?>">
                        <label class="form-label">Apartment, suite (optional)</label>
                    </div>
                    <div class="row g-2">
                        <div class="col-6"><div class="form-outline mb-3 <?= has_error('city') ? 'has-error' : '' ?>"><input type="text" name="city" class="form-control" value="<?= e(old('city')) ?>"><label class="form-label">City</label></div></div>
                        <div class="col-6"><div class="form-outline mb-3"><input type="text" name="district" class="form-control" value="<?= e(old('district')) ?>"><label class="form-label">District</label></div></div>
                    </div>
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="is_default" value="1" id="isDefault">
                        <label class="form-check-label small" for="isDefault">Set as default</label>
                    </div>
                    <button class="btn btn-primary w-100">Save address</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="d-flex flex-column gap-3">
            <?php if (!$addresses): ?>
                <div class="text-center py-5 text-muted">No saved addresses yet.</div>
            <?php endif; ?>
            <?php foreach ($addresses as $a): ?>
                <div class="card">
                    <div class="card-body d-flex justify-content-between align-items-start">
                        <div>
                            <strong><?= e($a['full_name']) ?></strong>
                            <?php if ($a['is_default']): ?><span class="badge bg-primary ms-1">Default</span><?php endif; ?>
                            <div class="small text-muted mt-1">
                                <?= e($a['address_line1']) ?><?= !empty($a['address_line2']) ? ', ' . e($a['address_line2']) : '' ?><br>
                                <?= e($a['city']) ?><?= !empty($a['district']) ? ', ' . e($a['district']) : '' ?><br>
                                <?= e($a['phone']) ?>
                            </div>
                        </div>
                        <form method="post" action="<?= url('/account/addresses/' . $a['id']) ?>">
                            <?= csrf_field() ?>
                            <?= method_field('delete') ?>
                            <button class="btn btn-link text-danger" title="Delete"><i class="fas fa-trash-can"></i></button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
