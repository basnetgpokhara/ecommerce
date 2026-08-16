<?php /** @var array $listing @var string $q */ ?>
<section class="page-head">
    <div class="container">
        <nav aria-label="breadcrumb" class="small">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('/') ?>">Home</a></li>
                <li class="breadcrumb-item active">Search</li>
            </ol>
        </nav>
        <h1 class="h3 fw-bold mb-0">
            Search results <?= $q !== '' ? 'for "' . e($q) . '"' : '' ?>
        </h1>
        <p class="text-muted small mb-0"><?= (int)$listing['total'] ?> products found</p>
    </div>
</section>

<div class="container py-4">
    <?php if ($listing['items']): ?>
        <div class="product-grid">
            <?php foreach ($listing['items'] as $p): ?>
                <?php \App\Core\View::partial('product_card', ['product' => $p]); ?>
            <?php endforeach; ?>
        </div>
        <?php \App\Core\View::partial('pagination', ['listing' => $listing, 'base' => '/search']); ?>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="fas fa-magnifying-glass fa-2x text-muted mb-3"></i>
            <h5>No products found</h5>
            <p class="text-muted">Try a different keyword.</p>
            <form action="<?= url('/search') ?>" method="get" class="d-flex justify-content-center mt-3">
                <input type="search" name="q" class="form-control" style="max-width:360px" placeholder="Search again…" value="<?= e($q) ?>">
                <button class="btn btn-primary ms-2">Search</button>
            </form>
        </div>
    <?php endif; ?>
</div>
