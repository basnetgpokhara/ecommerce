<?php /** @var array $categories */ ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Categories</h4>
</div>

<div class="row g-3">
    <div class="col-lg-4">
        <div class="card"><div class="card-body">
            <h6 class="mb-3"><i class="fas fa-plus me-1"></i> New category</h6>
            <form method="post" action="<?= url('/admin/categories') ?>">
                <?= csrf_field() ?>
                <div class="form-outline mb-3 <?= has_error('name')?'has-error':'' ?>">
                    <input type="text" name="name" class="form-control" required>
                    <label class="form-label">Name *</label>
                </div>
                <div class="form-outline mb-3">
                    <select name="parent_id" class="form-select">
                        <option value="">— Top level —</option>
                        <?php foreach ($categories as $c): if ($c['parent_id']) continue; ?>
                            <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label class="form-label">Parent (optional)</label>
                </div>
                <div class="row g-2">
                    <div class="col-7"><div class="form-outline mb-3"><input type="number" name="sort_order" class="form-control" value="0"><label class="form-label">Sort order</label></div></div>
                    <div class="col-5"><div class="form-outline mb-3"><input type="text" name="icon" class="form-control" placeholder="📱"><label class="form-label">Emoji</label></div></div>
                </div>
                <button class="btn btn-primary w-100">Create Category</button>
            </form>
        </div></div>
    </div>

    <div class="col-lg-8">
        <div class="card"><div class="card-body">
            <div class="table-responsive">
                <table class="table tbl-compact align-middle">
                    <thead><tr><th>Name</th><th>Slug</th><th>Parent</th><th>Sort</th><th class="text-end">Actions</th></tr></thead>
                    <tbody>
                    <?php foreach ($categories as $c):
                        $indent = $c['parent_id'] ? 'padding-left:1.2rem;' : '';
                    ?>
                        <tr>
                            <td style="<?= $indent ?>"><?= $c['parent_id'] ? '↳ ' : '' ?><strong><?= e($c['name']) ?></strong></td>
                            <td class="small text-muted"><?= e($c['slug']) ?></td>
                            <td class="small"><?= $c['parent_id'] ? 'sub' : 'root' ?></td>
                            <td class="small"><?= (int)$c['sort_order'] ?></td>
                            <td class="text-end">
                                <form method="post" action="<?= url('/admin/categories/'.$c['id'].'/delete') ?>" class="d-inline" onsubmit="return confirm('Delete this category?')">
                                    <?= csrf_field() ?>
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$categories): ?><tr><td colspan="5" class="text-center text-muted py-4">No categories yet.</td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
            <p class="small text-muted mb-0 mt-2">Tip: create top-level categories first, then add sub-categories with a parent. Categories with products or sub-categories can’t be deleted until they’re emptied.</p>
        </div></div>
    </div>
</div>
