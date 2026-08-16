<?php /** @var array $category */ ?>
<a href="<?= url('/category/' . $category['slug']) ?>" class="category-card">
    <div class="category-card-media">
        <img src="<?= media_url($category['image'] ?? null) ?>" alt="<?= e($category['name']) ?>" loading="lazy">
    </div>
    <div class="category-card-name"><?= e($category['name']) ?></div>
</a>
