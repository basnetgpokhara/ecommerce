<?php
/**
 * @var array $listing — result of Product::listing() with page/last_page
 * @var string $base   — route to keep, e.g. /category/electronics
 */
$page      = $listing['page'] ?? 1;
$lastPage  = $listing['last_page'] ?? 1;
if ($lastPage <= 1) { return; }
?>
<nav class="pagination-wrap mt-4" aria-label="Pagination">
    <ul class="pagination justify-content-center">
        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= query_url($base, ['page' => $page - 1]) ?>"><i class="fas fa-chevron-left"></i></a>
        </li>
        <?php
        $start = max(1, $page - 2);
        $end   = min($lastPage, $page + 2);
        if ($start > 1) {
            echo '<li class="page-item"><a class="page-link" href="' . query_url($base, ['page' => 1]) . '">1</a></li>';
            if ($start > 2) { echo '<li class="page-item disabled"><span class="page-link">…</span></li>'; }
        }
        for ($i = $start; $i <= $end; $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                <a class="page-link" href="<?= query_url($base, ['page' => $i]) ?>"><?= $i ?></a>
            </li>
        <?php endforeach;
        if ($end < $lastPage) {
            if ($end < $lastPage - 1) { echo '<li class="page-item disabled"><span class="page-link">…</span></li>'; }
            echo '<li class="page-item"><a class="page-link" href="' . query_url($base, ['page' => $lastPage]) . '">' . $lastPage . '</a></li>';
        }
        ?>
        <li class="page-item <?= $page >= $lastPage ? 'disabled' : '' ?>">
            <a class="page-link" href="<?= query_url($base, ['page' => $page + 1]) ?>"><i class="fas fa-chevron-right"></i></a>
        </li>
    </ul>
</nav>
