<?php /** @var string $token */ ?>
<h5 class="auth-heading">Set a new password</h5>
<p class="text-muted small mb-4">Choose a strong password for your account.</p>

<form method="post" action="<?= url('/reset') ?>" novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="token" value="<?= e($token) ?>">

    <div class="form-outline mb-2 <?= has_error('password') ? 'has-error' : '' ?>">
        <input type="password" name="password" id="password" class="form-control js-password" required autofocus>
        <label class="form-label" for="password">New password</label>
        <?php if ($err = errors('password')): ?><small class="text-danger d-block mt-1"><?= e($err) ?></small><?php endif; ?>
    </div>
    <div class="pw-strength mb-3">
        <div class="progress" style="height:5px"><div class="progress-bar js-strength-bar" style="width:0"></div></div>
        <small class="text-muted js-strength-text">At least 8 characters with letters and numbers.</small>
    </div>

    <div class="form-outline mb-3 <?= has_error('password_confirm') ? 'has-error' : '' ?>">
        <input type="password" name="password_confirm" id="password_confirm" class="form-control" required>
        <label class="form-label" for="password_confirm">Confirm new password</label>
        <?php if ($err = errors('password_confirm')): ?><small class="text-danger d-block mt-1"><?= e($err) ?></small><?php endif; ?>
    </div>

    <button class="btn btn-primary btn-block w-100 mb-3" type="submit">Reset password</button>
</form>
