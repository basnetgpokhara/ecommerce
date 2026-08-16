<?php /** Centered auth layout (login / register / forgot / reset). */ ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(($title ?? 'Welcome') . ' · ' . setting('site_name', APP_NAME)) ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= asset('img/favicon.svg') ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.2.0/mdb.min.css" rel="stylesheet">
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
</head>
<body class="auth-body">
    <div class="auth-wrapper">
        <a href="<?= url('/') ?>" class="auth-back"><i class="fas fa-arrow-left me-1"></i> Back to store</a>
        <div class="auth-card">
            <div class="auth-brand">
                <img src="<?= asset('img/logo.svg') ?>" alt="logo" height="44">
                <h1 class="h5 mt-2 mb-0"><?= e(setting('site_name', APP_NAME)) ?></h1>
            </div>
            <?= render_flash(); ?>
            <?php require VIEW_PATH . '/' . $content . '.php'; ?>
        </div>
        <p class="auth-foot text-center small text-white-50 mt-3">
            &copy; <?= date('Y') . ' ' . e(setting('site_name', APP_NAME)) ?>
        </p>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.2.0/mdb.min.js"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
