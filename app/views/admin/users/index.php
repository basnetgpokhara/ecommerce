<?php
/** @var array $users @var string $q @var string $role */
$me = current_user()['id'] ?? 0;
$roleBadge = ['customer'=>'bg-info text-dark','seller'=>'bg-success','admin'=>'bg-purple'];
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0">Users</h4>
    <span class="text-muted small"><?= (int)$users['total'] ?> found</span>
</div>

<form method="get" class="admin-toolbar">
    <input type="text" name="q" class="form-control form-control-sm" placeholder="Search name / email / phone" value="<?= e($q) ?>" style="max-width:260px">
    <select name="role" class="form-select form-select-sm" style="max-width:160px" onchange="this.form.submit()">
        <option value="">All roles</option>
        <?php foreach (['customer','seller','admin'] as $r): ?>
            <option value="<?= $r ?>" <?= $role===$r?'selected':'' ?>><?= ucfirst($r) ?></option>
        <?php endforeach; ?>
    </select>
    <button class="btn btn-outline-primary btn-sm"><i class="fas fa-search"></i></button>
    <a href="<?= url('/admin/users') ?>" class="btn btn-link btn-sm">Reset</a>
</form>

<div class="card"><div class="card-body">
    <div class="table-responsive">
        <table class="table table-hover tbl-compact align-middle">
            <thead><tr><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Status</th><th>Joined</th><th class="text-end">Actions</th></tr></thead>
            <tbody>
            <?php foreach ($users['items'] as $u): ?>
                <tr>
                    <td class="fw-semibold"><?= e($u['name']) ?></td>
                    <td class="small"><?= e($u['email']) ?></td>
                    <td class="small"><?= e($u['phone'] ?: '—') ?></td>
                    <td><span class="badge <?= $roleBadge[$u['role']] ?? 'bg-light' ?> text-capitalize"><?= e($u['role']) ?></span></td>
                    <td><span class="badge <?= $u['status']==='active'?'bg-success':'bg-danger' ?> text-capitalize"><?= e($u['status']) ?></span></td>
                    <td class="small text-muted"><?= e(date('M j, Y', strtotime($u['created_at']))) ?></td>
                    <td class="text-end text-nowrap">
                        <?php if ($u['id'] == $me): ?>
                            <span class="text-muted small">You</span>
                        <?php else: ?>
                            <form method="post" action="<?= url('/admin/users/'.$u['id'].'/status') ?>" class="d-inline">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm <?= $u['status']==='active'?'btn-outline-warning':'btn-outline-success' ?>" title="<?= $u['status']==='active'?'Block':'Unblock' ?>">
                                    <i class="fas <?= $u['status']==='active'?'fa-ban':'fa-circle-check' ?>"></i>
                                </button>
                            </form>
                            <form method="post" action="<?= url('/admin/users/'.$u['id'].'/delete') ?>" class="d-inline" onsubmit="return confirm('Delete this user?')">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$users['items']): ?><tr><td colspan="7" class="text-center text-muted py-4">No users found.</td></tr><?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php \App\Core\View::partial('pagination', ['listing' => $users, 'base' => '/admin/users']); ?>
</div></div>
