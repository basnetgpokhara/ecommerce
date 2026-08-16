<?php /** @var array $page */ ?>
<section class="page-head">
    <div class="container">
        <nav aria-label="breadcrumb" class="small">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?= url('/') ?>">Home</a></li>
                <li class="breadcrumb-item active"><?= e($page['title']) ?></li>
            </ol>
        </nav>
        <h1 class="h3 fw-bold mb-0"><?= e($page['title']) ?></h1>
    </div>
</section>

<div class="container py-4">
    <div class="cms-page rich-text">
        <?= $page['body'] // stored as trusted HTML from CMS admin (Phase 2) ?>
    </div>
</div>
