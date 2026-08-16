<?php /** @var string $role */ ?>
<h5 class="auth-heading">Create your account</h5>

<div class="role-switch mb-4">
    <a href="<?= url('/register') ?>" class="role-pill <?= $role === 'customer' ? 'active' : '' ?>">I'm a Customer</a>
    <a href="<?= url('/register?role=seller') ?>" class="role-pill <?= $role === 'seller' ? 'active' : '' ?>">I'm a Seller</a>
</div>

<form method="post" action="<?= url('/register') ?>" novalidate>
    <?= csrf_field() ?>
    <input type="hidden" name="role" value="<?= e($role) ?>">

    <?php if ($role === 'seller'): ?>
    <div class="form-outline mb-3 <?= has_error('shop_name') ? 'has-error' : '' ?>">
        <input type="text" name="shop_name" id="shop_name" class="form-control" value="<?= e(old('shop_name')) ?>" required>
        <label class="form-label" for="shop_name">Shop name</label>
        <?php if ($err = errors('shop_name')): ?><small class="text-danger d-block mt-1"><?= e($err) ?></small><?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="form-outline mb-3 <?= has_error('name') ? 'has-error' : '' ?>">
        <input type="text" name="name" id="name" class="form-control" value="<?= e(old('name')) ?>" required>
        <label class="form-label" for="name">Full name</label>
        <?php if ($err = errors('name')): ?><small class="text-danger d-block mt-1"><?= e($err) ?></small><?php endif; ?>
    </div>

    <div class="form-outline mb-3 <?= has_error('email') ? 'has-error' : '' ?>">
        <input type="email" name="email" id="email" class="form-control" value="<?= e(old('email')) ?>" required>
        <label class="form-label" for="email">Email address</label>
        <?php if ($err = errors('email')): ?><small class="text-danger d-block mt-1"><?= e($err) ?></small><?php endif; ?>
    </div>

    <div class="form-outline mb-3 <?= has_error('phone') ? 'has-error' : '' ?>">
        <input type="tel" name="phone" id="phone" class="form-control" value="<?= e(old('phone')) ?>" required>
        <label class="form-label" for="phone">Phone number</label>
        <?php if ($err = errors('phone')): ?><small class="text-danger d-block mt-1"><?= e($err) ?></small><?php endif; ?>
    </div>

    <div class="form-outline mb-2 <?= has_error('password') ? 'has-error' : '' ?>">
        <input type="password" name="password" id="password" class="form-control js-password" required>
        <label class="form-label" for="password">Password</label>
        <?php if ($err = errors('password')): ?><small class="text-danger d-block mt-1"><?= e($err) ?></small><?php endif; ?>
    </div>
    <div class="pw-strength mb-3">
        <div class="progress" style="height:5px"><div class="progress-bar js-strength-bar" style="width:0"></div></div>
        <small class="text-muted js-strength-text">At least 8 characters with letters and numbers.</small>
    </div>

    <div class="form-outline mb-3 <?= has_error('password_confirm') ? 'has-error' : '' ?>">
        <input type="password" name="password_confirm" id="password_confirm" class="form-control" required>
        <label class="form-label" for="password_confirm">Confirm password</label>
        <?php if ($err = errors('password_confirm')): ?><small class="text-danger d-block mt-1"><?= e($err) ?></small><?php endif; ?>
    </div>

    <button class="btn btn-primary btn-block w-100 mb-3" type="submit">
        <?= $role === 'seller' ? 'Create Seller Account' : 'Create Account' ?>
    </button>
</form>

<p class="text-center small mb-0">
    Already have an account? <a href="<?= url('/login') ?>">Log in</a>
</p>
