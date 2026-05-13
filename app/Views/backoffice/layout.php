<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->renderSection('title') ?: 'Administration régime' ?></title>
    <link rel="stylesheet" href="<?= base_url('assets/css/variables.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/global.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/backoffice.css') ?>">
    <?= $this->renderSection('head') ?>
</head>
<body class="bo-body">
<?php
    $activeNav = $activeNav ?? '';
    $navClass = static fn (string $key): string => $activeNav === $key ? 'nav-link is-active' : 'nav-link';
?>
    <div class="admin-shell">
        <aside class="sidebar" id="admin-sidebar">
            <div class="brand">
                <span class="brand-kicker">Administration</span>
                <h1>Gestion régime</h1>
                <p>Gestion simple des contenus régime, activité, promo et option.</p>
            </div>

            <nav class="nav-list" aria-label="Navigation admin">
                <a href="<?= base_url('admin/dashboard') ?>" class="<?= $navClass('dashboard') ?>">
                    <img class="icon" src="<?= esc(base_url('assets/icons/layout-dashboard.svg')) ?>" alt="">
                    <span>Tableau de bord</span>
                </a>
                <a href="<?= base_url('admin/utilisateurs') ?>" class="<?= $navClass('utilisateurs') ?>">
                    <img class="icon" src="<?= esc(base_url('assets/icons/users.svg')) ?>" alt="">
                    <span>Utilisateurs</span>
                </a>
                <a href="<?= base_url('admin/regimes') ?>" class="<?= $navClass('regimes') ?>">
                    <img class="icon" src="<?= esc(base_url('assets/icons/apple.svg')) ?>" alt="">
                    <span>Régimes</span>
                </a>
                <a href="<?= base_url('admin/activites') ?>" class="<?= $navClass('activites') ?>">
                    <img class="icon" src="<?= esc(base_url('assets/icons/activity.svg')) ?>" alt="">
                    <span>Activités</span>
                </a>
                <a href="<?= base_url('admin/promos') ?>" class="<?= $navClass('promos') ?>">
                    <img class="icon" src="<?= esc(base_url('assets/icons/ticket-percent.svg')) ?>" alt="">
                    <span>Promos</span>
                </a>
                <a href="<?= base_url('admin/options') ?>" class="<?= $navClass('options') ?>">
                    <img class="icon" src="<?= esc(base_url('assets/icons/crown.svg')) ?>" alt="">
                    <span>Options</span>
                </a>
                <a href="<?= base_url('admin/imc') ?>" class="<?= $navClass('imc') ?>">
                    <img class="icon" src="<?= esc(base_url('assets/icons/scale.svg')) ?>" alt="">
                    <span>Paramètres IMC</span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <a href="<?= base_url('admin/logout') ?>" class="nav-link nav-link-danger" data-confirm-message="Voulez-vous vraiment vous déconnecter ?">
                    <img class="icon" src="<?= esc(base_url('assets/icons/log-out.svg')) ?>" alt="">
                    <span>Déconnexion</span>
                </a>
            </div>
        </aside>

        <main class="content">
            <div class="page-head">
                <div>
                    <h2><?= $this->renderSection('page_title') ?: 'Page admin' ?></h2>
                    <p><?= $this->renderSection('page_subtitle') ?></p>
                </div>
                <div class="actions-inline"><?= $this->renderSection('page_actions') ?></div>
            </div>

            <div class="flash-stack">
                <?php if (session()->getFlashdata('success')): ?>
                    <div class="flash success"><?= esc(session()->getFlashdata('success')) ?></div>
                <?php endif; ?>
                <?php if (session()->getFlashdata('error')): ?>
                    <div class="flash error"><?= esc(session()->getFlashdata('error')) ?></div>
                <?php endif; ?>
            </div>

            <?= $this->renderSection('content') ?>
        </main>
    </div>

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
