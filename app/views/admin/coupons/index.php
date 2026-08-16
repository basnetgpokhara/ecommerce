<?php /** @var array $coupons */ ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Coupons</h4>
    <span class="text-muted small"><?= count($coupons) ?> total</span>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card"><div class="card-body">
            <h6 class="mb-3"><i class="fas fa-plus me-1"></i> Create coupon</h6>
            <form method="post" action="<?= url('/admin/coupons') ?>">
                <?= csrf_field() ?>
                <?php if ($e = errors('code')): ?><small class="text-danger d-block mb-1"><?= e($e) ?></small><?php endif; ?>
                <div class="form-outline mb-3"><input type="text" name="code" class="form-control" value="<?= e(old('code')) ?>" placeholder="SUMMER20" maxlength="60"><label class="form-label">Code</label></div>
                <div class="row g-2">
                    <div class="col-6"><select name="type" class="form-select">
                        <option value="percentage">Percentage (%)</option>
                        <option value="flat">Flat (₹)</option>
                    </select><label class="form-label d-block">Type</label></div>
                    <div class="col-6"><div class="form-outline"><input type="number" step="0.01" name="value" class="form-control"><label class="form-label">Value</label></div></div>
                </div>
                <div class="row g-2 mt-1">
                    <div class="col-6"><div class="form-outline"><input type="number" step="0.01" name="min_order" class="form-control"><label class="form-label">Min. order</label></div></div>
                    <div class="col-6"><div class="form-outline"><input type="number" name="usage_limit" class="form-control"><label class="form-label">Usage limit</label></div></div>
                </div>
                <div class="form-outline mb-2 mt-2"><input type="date" name="expiry" class="form-control"><label class="form-label">Expires</label></div>
                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="status" value="active" id="cnewactive" checked>
                    <label class="form-check-label" for="cnewactive">Active</label>
                </div>
                <button class="btn btn-primary w-100">Create Coupon</button>
            </form>
        </div></div>
    </div>

    <div class="col-lg-8">
        <div class="card"><div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover tbl-compact align-middle">
                    <thead><tr><th>Code</th><th>Type</th><th>Value</th><th>Min</th><th>Uses</th><th>Expires</th><th>Status</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($coupons as $c): ?>
                        <tr>
                            <td class="fw-bold"><?= e($c['code']) ?></td>
                            <td class="small text-capitalize"><?= e($c['type']) ?></td>
                            <td><?= $c['type'] === 'percentage' ? (float)$c['value'] . '%' : money($c['value']) ?></td>
                            <td class="small"><?= $c['min_order'] !== null ? money($c['min_order']) : '—' ?></td>
                            <td class="small"><?= (int)$c['used'] ?><?= $c['usage_limit'] !== null ? '/' . (int)$c['usage_limit'] : '' ?></td>
                            <td class="small"><?= $c['expiry'] ? e(date('M j, Y', strtotime($c['expiry']))) : '—' ?></td>
                            <td><span class="badge <?= $c['status']==='active'?'bg-success':'bg-secondary' ?> text-capitalize"><?= e($c['status']) ?></span></td>
                            <td class="text-end text-nowrap">
                                <button class="btn btn-sm btn-outline-primary" data-mdb-toggle="collapse" data-mdb-target="#coupon-<?= (int)$c['id'] ?>"><i class="fas fa-pen"></i></button>
                                <form method="post" action="<?= url('/admin/coupons/'.$c['id'].'/delete') ?>" class="d-inline" onsubmit="return confirm('Delete coupon <?= e($c['code']) ?>?')"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button></form>
                            </td>
                        </tr>
                        <tr class="collapse" id="coupon-<?= (int)$c['id'] ?>">
                            <td colspan="8">
                                <form method="post" action="<?= url('/admin/coupons/'.$c['id']) ?>" class="row g-2 align-items-end">
                                    <?= csrf_field() ?>
                                    <div class="col-6 col-md-2"><select name="type" class="form-select form-select-sm">
                                        <option value="percentage" <?= $c['type']==='percentage'?'selected':'' ?>>%</option>
                                        <option value="flat" <?= $c['type']==='flat'?'selected':'' ?>>Flat</option>
                                    </select></div>
                                    <div class="col-6 col-md-2"><input type="number" step="0.01" name="value" class="form-control form-control-sm" value="<?= e($c['value']) ?>"></div>
                                    <div class="col-6 col-md-2"><input type="number" step="0.01" name="min_order" class="form-control form-control-sm" value="<?= e($c['min_order'] ?? '') ?>" placeholder="Min"></div>
                                    <div class="col-6 col-md-2"><input type="number" name="usage_limit" class="form-control form-control-sm" value="<?= e($c['usage_limit'] ?? '') ?>" placeholder="Limit"></div>
                                    <div class="col-6 col-md-2"><input type="date" name="expiry" class="form-control form-control-sm" value="<?= e($c['expiry'] ?? '') ?>"></div>
                                    <div class="col-6 col-md-1"><select name="status" class="form-select form-select-sm">
                                        <option value="active" <?= $c['status']==='active'?'selected':'' ?>>Active</option>
                                        <option value="inactive" <?= $c['status']==='inactive'?'selected':'' ?>>Inactive</option>
                                    </select></div>
                                    <div class="col-auto"><button class="btn btn-sm btn-primary">Save</button></div>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$coupons): ?><tr><td colspan="8" class="text-center text-muted py-4">No coupons yet. Create one to offer discounts.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div></div>
    </div>
</div>
