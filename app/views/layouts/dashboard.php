<?php /** Layout for account / seller / admin dashboards (sidebar + content). */ ?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e(($title ?? 'Dashboard') . ' · ' . setting('site_name', APP_NAME)) ?></title>
    <link rel="icon" type="image/svg+xml" href="<?= asset('img/favicon.svg') ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.2.0/mdb.min.css" rel="stylesheet">
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
    <?php \App\Core\View::partial('header'); ?>

    <main class="flex-grow-1 py-4">
        <div class="container-fluid px-lg-4">
            <div class="row g-4">
                <aside class="col-lg-3 col-md-4">
                    <div class="dash-sidebar">
                        <?php \App\Core\View::partial('dashboard_sidebar'); ?>
                    </div>
                </aside>
                <section class="col-lg-9 col-md-8">
                    <?= render_flash(); ?>
                    <?php require VIEW_PATH . '/' . $content . '.php'; ?>
                </section>
            </div>
        </div>
    </main>

    <?php \App\Core\View::partial('footer'); ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.2.0/mdb.min.js"></script>
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
