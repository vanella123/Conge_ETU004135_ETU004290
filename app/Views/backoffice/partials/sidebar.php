<?php
    $activeNav = $activeNav ?? '';
    $navClass = static fn (string $key): string => $activeNav === $key ? 'nav-link is-active' : 'nav-link';
?>

<button class="sidebar-toggle" aria-label="Menu">
    <img src="<?= esc(base_url('assets/icons/menu.svg')) ?>" alt="Menu">
</button>
<div class="sidebar-overlay"></div>

<aside class="sidebar">
    <div class="bo-brand">
        <span class="brand-kicker">Administration</span>
        <h1>Gestion régime</h1>
        <p>Gestion des contenus régime, activité, promo et option.</p>
    </div>

    <nav class="nav-list" aria-label="Navigation admin">
        <a href="<?= base_url('admin/dashboard') ?>" class="<?= $navClass('dashboard') ?>">
            <img class="icon" src="<?= esc(base_url('assets/icons/layout-dashboard.svg')) ?>" alt="">
            <span>Tableau de bord</span>
        </a>
        <a href="<?= base_url('admin/stats-croisees') ?>" class="<?= $navClass('stats') ?>">
            <img class="icon" src="<?= esc(base_url('assets/icons/chart-bar.svg')) ?>" alt="">
            <span>Stats croisées</span>
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
