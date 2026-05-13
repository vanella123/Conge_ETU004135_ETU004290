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

<main class="shell stack">
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <?= $this->renderSection('content') ?>
</main>

<?= $this->include('frontoffice/partials/footer') ?>

<div class="confirm-modal" id="confirm-modal" aria-hidden="true">
    <div class="confirm-card" role="dialog" aria-modal="true" aria-labelledby="confirm-title">
        <div class="confirm-head">
            <img src="<?= esc(base_url('assets/icons/shield-alert.svg')) ?>" alt="">
            <strong id="confirm-title">Confirmation</strong>
        </div>
        <div class="confirm-body" id="confirm-message">Confirmer cette action ?</div>
        <div class="confirm-actions">
            <button type="button" class="btn btn-secondary" id="confirm-cancel">Annuler</button>
            <button type="button" class="btn" id="confirm-ok">Confirmer</button>
        </div>
    </div>
</div>

<script src="<?= base_url('assets/js/app.js') ?>"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
