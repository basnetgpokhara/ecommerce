<?php /** @var array $categories */ ?>
<section class="page-head">
    <div class="container">
        <nav aria-label="breadcrumb" class="small">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('/') ?>">Home</a></li>
                <li class="breadcrumb-item active">Categories</li>
            </ol>
        </nav>
        <h1 class="h3 fw-bold mb-0">All Categories</h1>
    </div>
</section>

<div class="container py-4">
    <div class="category-grid">
        <?php foreach ($categories as $cat): ?>
            <?php \App\Core\View::partial('category_card', ['category' => $cat]); ?>
        <?php endforeach; ?>
    </div>
    <?php if (!$categories): ?>
        <p class="text-muted">No categories yet.</p>
    <?php endif; ?>
</div>
