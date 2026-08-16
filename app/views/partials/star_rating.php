<?php
/** @var float|null $rating  (0..5)  @var int|null $count */
$rating = $rating ?? 0;
$count  = $count ?? null;
$full   = (int) floor($rating);
$half   = ($rating - $full) >= 0.25 && ($rating - $full) < 0.75;
if (($rating - $full) >= 0.75) { $full++; $half = false; }
?>
<span class="star-rating" aria-label="Rated <?= e(number_format($rating, 1)) ?> of 5">
    <?php for ($i = 1; $i <= 5; $i++): ?>
        <?php if ($i <= $full): ?><i class="fas fa-star"></i>
        <?php elseif ($i === $full + 1 && $half): ?><i class="fas fa-star-half-stroke"></i>
        <?php else: ?><i class="far fa-star"></i><?php endif; ?>
    <?php endfor; ?>
    <?php if ($count !== null): ?><span class="ms-1 text-muted small">(<?= (int)$count ?>)</span><?php endif; ?>
</span>
