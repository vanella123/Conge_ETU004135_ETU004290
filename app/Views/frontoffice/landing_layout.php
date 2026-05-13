<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $this->renderSection('title') ?: 'Projet Régime' ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/variables.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/global.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/frontoffice.css') ?>">
    <?= $this->renderSection('head') ?>
</head>
<body class="fo-body">
<?= $this->include('frontoffice/partials/navbar') ?>

<?= $this->renderSection('content') ?>

<?= $this->include('frontoffice/partials/footer') ?>

<script src="<?= base_url('assets/js/app.js') ?>"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
