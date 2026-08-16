<?php /** @var string $content — the template path rendered below */ ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= e(($title ?? '') ? $title . ' · ' : '') . setting('site_name', APP_NAME) ?></title>
    <meta name="description" content="<?= e($meta_description ?? setting('site_tagline', 'Multi-vendor marketplace for Nepal')) ?>">
    <?php if (!empty($meta_title)): ?><meta property="og:title" content="<?= e($meta_title) ?>"><?php endif; ?>
    <link rel="icon" type="image/svg+xml" href="<?= asset('img/favicon.svg') ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.2.0/mdb.min.css" rel="stylesheet">
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
    <?php \App\Core\View::partial('header'); ?>

    <main class="flex-grow-1">
        <?= render_flash(); ?>
        <?php require VIEW_PATH . '/' . $content . '.php'; ?>
    </main>

    <?php \App\Core\View::partial('footer'); ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.2.0/mdb.min.js"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
