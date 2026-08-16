<?php /** @var array $pages */ ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">CMS Pages</h4>
</div>

<div class="card mb-3"><div class="card-body">
    <h6 class="mb-3"><i class="fas fa-plus me-1"></i> New page</h6>
    <form method="post" action="<?= url('/admin/pages') ?>">
        <?= csrf_field() ?>
        <div class="row g-2">
            <div class="col-md-6"><div class="form-outline mb-2 <?= has_error('title')?'has-error':'' ?>"><input type="text" name="title" class="form-control" required><label class="form-label">Title *</label></div></div>
            <div class="col-md-3"><div class="form-outline mb-2"><input type="text" name="slug" class="form-control"><label class="form-label">Slug (auto)</label></div></div>
            <div class="col-md-3"><select name="status" class="form-select"><option value="published">Published</option><option value="draft">Draft</option></select><label class="form-label d-block">Status</label></div>
        </div>
        <div class="form-outline mb-2 <?= has_error('body')?'has-error':'' ?>"><textarea name="body" class="form-control" rows="3" required placeholder="HTML allowed"></textarea><label class="form-label">Body (HTML) *</label></div>
        <button class="btn btn-primary btn-sm">Create Page</button>
    </form>
</div></div>

<div class="row g-3">
<?php foreach ($pages as $p): ?>
    <div class="col-lg-6">
        <div class="card h-100"><div class="card-body">
            <form method="post" action="<?= url('/admin/pages/'.$p['id']) ?>">
                <?= csrf_field() ?>
                <div class="d-flex gap-2 mb-2">
                    <div class="form-outline flex-grow-1"><input type="text" name="title" class="form-control" value="<?= e($p['title']) ?>"><label class="form-label">Title</label></div>
                    <div class="form-outline" style="max-width:140px"><input type="text" name="slug" class="form-control" value="<?= e($p['slug']) ?>"><label class="form-label">Slug</label></div>
                </div>
                <div class="form-outline mb-2"><textarea name="body" class="form-control" rows="4"><?= e($p['body']) ?></textarea><label class="form-label">Body (HTML)</label></div>
                <div class="d-flex align-items-center gap-2 mb-0">
                    <select name="status" class="form-select form-select-sm" style="max-width:150px">
                        <option value="published" <?= $p['status']==='published'?'selected':'' ?>>Published</option>
                        <option value="draft" <?= $p['status']==='draft'?'selected':'' ?>>Draft</option>
                    </select>
                    <button class="btn btn-primary btn-sm">Save</button>
                    <a href="<?= url('/page/'.$p['slug']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm"><i class="fas fa-eye"></i></a>
                </div>
            </form>
            <form method="post" action="<?= url('/admin/pages/'.$p['id'].'/delete') ?>" class="text-end mt-2" onsubmit="return confirm('Delete this page?')"><?= csrf_field() ?><button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash me-1"></i>Delete page</button></form>
        </div></div>
    </div>
<?php endforeach; ?>
<?php if (!$pages): ?><div class="col-12"><div class="text-center text-muted py-4">No pages yet.</div></div><?php endif; ?>
</div>
