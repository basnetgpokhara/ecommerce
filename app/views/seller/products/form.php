<?php
/** @var array|null $product @var array $categories @var array $images */
$isEdit = $product !== null;
$old = function (string $key, $default = '') use ($product) {
    return \App\Core\Session::getFlash('old', [])[$key] ?? ($product[$key] ?? $default);
};
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><?= $isEdit ? 'Edit Product' : 'Add Product' ?></h4>
    <a href="<?= url('/seller/products') ?>" class="btn btn-link btn-sm">&larr; Back to products</a>
</div>

<form method="post" action="<?= url($isEdit ? '/seller/products/'.$product['id'] : '/seller/products') ?>" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card mb-3"><div class="card-body">
                <div class="form-outline mb-3 <?= has_error('name')?'has-error':'' ?>">
                    <input type="text" name="name" class="form-control" value="<?= e($old('name')) ?>" required>
                    <label class="form-label">Product name *</label>
                    <?php if ($e=errors('name')): ?><small class="text-danger d-block"><?= e($e) ?></small><?php endif; ?>
                </div>
                <div class="form-outline mb-3">
                    <textarea name="short_description" class="form-control" rows="2"><?= e($old('short_description')) ?></textarea>
                    <label class="form-label">Short description</label>
                </div>
                <div class="form-outline mb-3">
                    <textarea name="description" class="form-control" rows="4"><?= e($old('description')) ?></textarea>
                    <label class="form-label">Full description</label>
                </div>
                <div class="form-outline mb-0">
                    <textarea name="specifications" class="form-control" rows="3" placeholder="Brand: …&#10;Warranty: …"><?= e($old('specifications')) ?></textarea>
                    <label class="form-label">Specifications</label>
                </div>
            </div></div>

            <div class="card"><div class="card-body">
                <h6 class="mb-3">Images</h6>
                <?php if ($isEdit && $images): ?>
                    <div class="row g-2 mb-3">
                        <?php foreach ($images as $img): ?>
                            <div class="col-4 col-md-3 text-center">
                                <img src="<?= media_url($img['image_path']) ?>" class="img-thumbnail" style="height:80px;object-fit:contain">
                                <div class="form-check form-check-inline mt-1">
                                    <input class="form-check-input" type="radio" name="primary_image" id="pi<?= $img['id'] ?>" value="<?= $img['id'] ?>" <?= $img['is_primary']?'checked':'' ?>>
                                    <label class="form-check-label small" for="pi<?= $img['id'] ?>">Main</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="checkbox" name="remove_images[]" value="<?= $img['id'] ?>" id="rm<?= $img['id'] ?>">
                                    <label class="form-check-label small" for="rm<?= $img['id'] ?>">Remove</label>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                <input type="file" name="images[]" multiple accept="image/png,image/jpeg,image/webp,image/gif" class="form-control">
                <small class="text-muted">JPG, PNG, WEBP or GIF — max 3 MB each. The first image is used as the main photo.</small>
                <?php if ($e=errors('images')): ?><small class="text-danger d-block"><?= e($e) ?></small><?php endif; ?>
            </div></div>
        </div>

        <div class="col-lg-4">
            <div class="card mb-3"><div class="card-body">
                <h6 class="mb-3">Pricing &amp; inventory</h6>
                <div class="form-outline mb-3 <?= has_error('price')?'has-error':'' ?>">
                    <input type="number" step="0.01" name="price" class="form-control" value="<?= e($old('price')) ?>" required>
                    <label class="form-label">Price (<?= e(setting('currency_symbol','रू')) ?>) *</label>
                </div>
                <div class="form-outline mb-3 <?= has_error('discount_price')?'has-error':'' ?>">
                    <input type="number" step="0.01" name="discount_price" class="form-control" value="<?= e($old('discount_price')) ?>">
                    <label class="form-label">Discount price (optional)</label>
                    <?php if ($e=errors('discount_price')): ?><small class="text-danger d-block"><?= e($e) ?></small><?php endif; ?>
                </div>
                <div class="form-outline mb-3 <?= has_error('stock')?'has-error':'' ?>">
                    <input type="number" name="stock" class="form-control" value="<?= e($old('stock')) ?>" required>
                    <label class="form-label">Stock quantity *</label>
                </div>
                <div class="form-outline mb-0">
                    <input type="text" name="sku" class="form-control" value="<?= e($old('sku')) ?>">
                    <label class="form-label">SKU (optional)</label>
                </div>
            </div></div>

            <div class="card mb-3"><div class="card-body">
                <h6 class="mb-3">Organization</h6>
                <div class="form-outline mb-3 <?= has_error('category_id')?'has-error':'' ?>">
                    <select name="category_id" class="form-select" required>
                        <option value="">Select category…</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($old('category_id'))==$cat['id']?'selected':'' ?>><?= e($cat['name']) ?></option>
                            <?php foreach ($cat['children'] as $ch): ?>
                                <option value="<?= $ch['id'] ?>" <?= ($old('category_id'))==$ch['id']?'selected':'' ?>>— <?= e($ch['name']) ?></option>
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    </select>
                    <label class="form-label">Category *</label>
                </div>
                <div class="form-outline mb-0">
                    <input type="text" name="brand" class="form-control" value="<?= e($old('brand')) ?>">
                    <label class="form-label">Brand (optional)</label>
                </div>
            </div></div>

            <button class="btn btn-primary w-100 mb-2"><?= $isEdit ? 'Save Changes' : 'Publish Product' ?></button>
            <a href="<?= url('/seller/products') ?>" class="btn btn-link btn-sm w-100">Cancel</a>
            <?php if (setting('approval_mode','auto')==='pending'): ?>
                <small class="text-muted d-block mt-2">New products require admin approval before going live.</small>
            <?php endif; ?>
        </div>
    </div>
</form>
