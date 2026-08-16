<?php /** @var array $user */ ?>
<div class="dash-head mb-4">
    <h4 class="fw-bold mb-0">My Profile</h4>
    <p class="text-muted mb-0">Manage your personal information.</p>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card">
            <div class="card-body">
                <h6 class="mb-3">Account details</h6>
                <form method="post" action="<?= url('/account/profile') ?>">
                    <?= csrf_field() ?>
                    <div class="form-outline mb-3 <?= has_error('name') ? 'has-error' : '' ?>">
                        <input type="text" name="name" class="form-control" value="<?= e(old('name', $user['name'])) ?>">
                        <label class="form-label">Full name</label>
                    </div>
                    <div class="form-outline mb-3">
                        <input type="email" class="form-control" value="<?= e($user['email']) ?>" readonly>
                        <label class="form-label">Email (not editable)</label>
                    </div>
                    <div class="form-outline mb-3 <?= has_error('phone') ? 'has-error' : '' ?>">
                        <input type="tel" name="phone" class="form-control" value="<?= e(old('phone', $user['phone'] ?? '')) ?>">
                        <label class="form-label">Phone</label>
                    </div>
                    <button class="btn btn-primary">Save changes</button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card">
            <div class="card-body">
                <h6 class="mb-3">Change password</h6>
                <form method="post" action="<?= url('/account/password') ?>">
                    <?= csrf_field() ?>
                    <div class="form-outline mb-3 <?= has_error('current_password') ? 'has-error' : '' ?>">
                        <input type="password" name="current_password" class="form-control" required>
                        <label class="form-label">Current password</label>
                        <?php if ($e = errors('current_password')): ?><small class="text-danger d-block mt-1"><?= e($e) ?></small><?php endif; ?>
                    </div>
                    <div class="form-outline mb-3 <?= has_error('password') ? 'has-error' : '' ?>">
                        <input type="password" name="password" class="form-control" required>
                        <label class="form-label">New password</label>
                    </div>
                    <div class="form-outline mb-3 <?= has_error('password_confirm') ? 'has-error' : '' ?>">
                        <input type="password" name="password_confirm" class="form-control" required>
                        <label class="form-label">Confirm new password</label>
                    </div>
                    <button class="btn btn-outline-primary">Update password</button>
                </form>
            </div>
        </div>
    </div>
</div>
