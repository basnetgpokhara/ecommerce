<h5 class="auth-heading">Reset your password</h5>
<p class="text-muted small mb-4">Enter your email and we'll generate a reset link.</p>

<form method="post" action="<?= url('/forgot') ?>" novalidate>
    <?= csrf_field() ?>
    <div class="form-outline mb-3 <?= has_error('email') ? 'has-error' : '' ?>">
        <input type="email" name="email" id="email" class="form-control" value="<?= e(old('email')) ?>" required autofocus>
        <label class="form-label" for="email">Email address</label>
    </div>
    <button class="btn btn-primary btn-block w-100 mb-3" type="submit">Send reset link</button>
</form>

<p class="text-center small mb-0"><a href="<?= url('/login') ?>">Back to login</a></p>
