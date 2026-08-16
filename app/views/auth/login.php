<h5 class="auth-heading">Welcome back</h5>
<p class="text-muted small mb-4">Log in to continue shopping.</p>

<form method="post" action="<?= url('/login') ?>" novalidate>
    <?= csrf_field() ?>

    <div class="form-outline mb-3 <?= has_error('email') ? 'has-error' : '' ?>">
        <input type="email" name="email" id="email" class="form-control" value="<?= e(old('email')) ?>" required autofocus>
        <label class="form-label" for="email">Email address</label>
        <?php if ($err = errors('email')): ?><small class="text-danger d-block mt-1"><?= e($err) ?></small><?php endif; ?>
    </div>

    <div class="form-outline mb-2 <?= has_error('password') ? 'has-error' : '' ?>">
        <input type="password" name="password" id="password" class="form-control" required>
        <label class="form-label" for="password">Password</label>
    </div>

    <div class="d-flex justify-content-end mb-3">
        <a href="<?= url('/forgot') ?>" class="small">Forgot password?</a>
    </div>

    <button class="btn btn-primary btn-block w-100 mb-3" type="submit">Log in</button>
</form>

<p class="text-center small mb-0">
    New here?
    <a href="<?= url('/register') ?>">Create an account</a>
    &nbsp;·&nbsp;
    <a href="<?= url('/register?role=seller') ?>">Sell with us</a>
</p>
