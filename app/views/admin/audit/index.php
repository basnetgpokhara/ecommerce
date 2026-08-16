<?php /** @var array $logs */ ?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Audit Log</h4>
    <span class="text-muted small"><?= count($logs) ?> recent entries</span>
</div>
<div class="card"><div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover tbl-compact align-middle">
            <thead><tr><th>When</th><th>User</th><th>Action</th><th>Description</th><th>IP</th></tr></thead>
            <tbody>
            <?php foreach ($logs as $l): ?>
                <tr>
                    <td class="small text-muted"><?= e(date('M j, Y g:i A', strtotime($l['created_at']))) ?></td>
                    <td class="small"><?= e($l['user_name'] ?? '—') ?></td>
                    <td><code class="small"><?= e($l['action']) ?></code></td>
                    <td class="small"><?= e($l['description'] ?? '') ?></td>
                    <td class="small text-muted"><?= e($l['ip'] ?? '') ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$logs): ?><tr><td colspan="5" class="text-center text-muted py-4">No activity logged yet.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
</div></div>
