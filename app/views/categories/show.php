<?php
/** @var array $category @var array $listing @var array $brands @var array $filters */
$base = '/category/' . $category['slug'];
$selectedBrand = $filters['brand'] ?? '';
$min = $filters['min'] ?? '';
$max = $filters['max'] ?? '';
$sort = $filters['sort'] ?? 'default';
?>
<section class="page-head">
    <div class="container">
        <nav aria-label="breadcrumb" class="small">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('/') ?>">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= url('/categories') ?>">Categories</a></li>
                <li class="breadcrumb-item active"><?= e($category['name']) ?></li>
            </ol>
        </nav>
        <h1 class="h3 fw-bold mb-0"><?= e($category['name']) ?></h1>
        <p class="text-muted small mb-0"><?= (int)$listing['total'] ?> products found</p>
    </div>
</section>

<div class="container py-4">
    <div class="row g-4">
        <aside class="col-lg-3 d-none d-lg-block">
            <div class="filter-card">
                <h6 class="filter-title">Filters</h6>
                <form method="get" action="<?= url($base) ?>">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Price range (<?= e(setting('currency_symbol','रू')) ?>)</label>
                        <div class="d-flex gap-2">
                            <input type="number" name="min" class="form-control form-control-sm" placeholder="Min" value="<?= e($min) ?>">
                            <input type="number" name="max" class="form-control form-control-sm" placeholder="Max" value="<?= e($max) ?>">
                        </div>
                    </div>
                    <?php if ($brands): ?>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Brand</label>
                        <select name="brand" class="form-select form-select-sm">
                            <option value="">All brands</option>
                            <?php foreach ($brands as $b): ?>
                                <option value="<?= e($b['brand']) ?>" <?= $selectedBrand === $b['brand'] ? 'selected' : '' ?>><?= e($b['brand']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div class="d-grid">
                        <button class="btn btn-primary btn-sm" type="submit"><i class="fas fa-filter me-1"></i> Apply</button>
                        <a href="<?= url($base) ?>" class="btn btn-link btn-sm">Reset</a>
                    </div>
                </form>
            </div>
        </aside>

        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <button class="btn btn-outline-secondary btn-sm d-lg-none" type="button" data-mdb-toggle="collapse" data-mdb-target="#mobileFilter"><i class="fas fa-sliders"></i> Filters</button>
                <div class="collapse d-lg-none" id="mobileFilter">
                    <div class="filter-card mt-2">
                        <form method="get" action="<?= url($base) ?>">
                            <div class="row g-2">
                                <div class="col-6"><input type="number" name="min" class="form-control form-control-sm" placeholder="Min" value="<?= e($min) ?>"></div>
                                <div class="col-6"><input type="number" name="max" class="form-control form-control-sm" placeholder="Max" value="<?= e($max) ?>"></div>
                            </div>
                            <?php if ($brands): ?>
                            <select name="brand" class="form-select form-select-sm mt-2">
                                <option value="">All brands</option>
                                <?php foreach ($brands as $b): ?>
                                    <option value="<?= e($b['brand']) ?>" <?= $selectedBrand === $b['brand'] ? 'selected' : '' ?>><?= e($b['brand']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php endif; ?>
                            <button class="btn btn-primary btn-sm w-100 mt-2">Apply</button>
                        </form>
                    </div>
                </div>
                <form method="get" action="<?= url($base) ?>" class="d-flex align-items-center gap-2" id="sortForm">
                    <label class="small text-muted">Sort by</label>
                    <select name="sort" class="form-select form-select-sm sort-select" onchange="this.form.submit()">
                        <?php
                        $opts = ['default'=>'Relevance','newest'=>'Newest','price_asc'=>'Price: Low to High','price_desc'=>'Price: High to Low','popular'=>'Popularity'];
                        foreach ($opts as $k=>$v): ?>
                            <option value="<?= $k ?>" <?= $sort === $k ? 'selected' : '' ?>><?= $v ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php foreach (['min','max','brand'] as $keep): if (!empty($$keep)): ?>
                        <input type="hidden" name="<?= $keep ?>" value="<?= e($$keep) ?>">
                    <?php endif; endforeach; ?>
                </form>
            </div>

            <?php if ($listing['items']): ?>
                <div class="product-grid">
                    <?php foreach ($listing['items'] as $p): ?>
                        <?php \App\Core\View::partial('product_card', ['product' => $p]); ?>
                    <?php endforeach; ?>
                </div>
                <?php \App\Core\View::partial('pagination', ['listing' => $listing, 'base' => $base]); ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-magnifying-glass fa-2x text-muted mb-3"></i>
                    <h5>No products found</h5>
                    <p class="text-muted">Try adjusting your filters or check back later.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
