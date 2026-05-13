<?= $this->extend('backoffice/layout') ?>

<?php
    $recentUsers = $recentUsers ?? [];
    $pieData = $pieData ?? [];
    $trendLabels = $trendLabels ?? [];
    $trendValues = $trendValues ?? [];
?>

<?= $this->section('title') ?>Tableau de bord<?= $this->endSection() ?>
<?= $this->section('head') ?>
    
<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Tableau de bord<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Vue d'ensemble rapide de l'activite de la plateforme et des objectifs utilisateurs.<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="dashboard-hero">
        <h3>Bienvenue, <?= esc((string) session()->get('admin_name')) ?></h3>
        <p>Voici un apercu clair de la plateforme. Cette page reprend le meme layout final que le module Regime pour garder une navigation stable dans tout le backoffice.</p>
    </div>

    <div class="stats-grid">
        <div class="stat-box stat-e">
            <div class="stat-icon"><img src="<?= esc(base_url('assets/icons/wallet.svg')) ?>" alt=""></div>
            <h4>Chiffre d'affaire</h4>
            <p><?= number_format((float)$chiffreAffaire, 0, ',', ' ') ?> Ar</p>
        </div>
        <div class="stat-box stat-c">
            <div class="stat-icon"><img src="<?= esc(base_url('assets/icons/shopping-bag.svg')) ?>" alt=""></div>
            <h4>Ventes</h4>
            <p><?= esc((string) $salesCount) ?></p>
        </div>
        <div class="stat-box stat-a">
            <div class="stat-icon"><img src="<?= esc(base_url('assets/icons/users.svg')) ?>" alt=""></div>
            <h4>Utilisateurs</h4>
            <p><?= esc((string) $usersCount) ?></p>
        </div>
        <div class="stat-box stat-b">
            <div class="stat-icon"><img src="<?= esc(base_url('assets/icons/crown.svg')) ?>" alt=""></div>
            <h4>Comptes Gold</h4>
            <p><?= esc((string) $goldCount) ?></p>
        </div>
        <div class="stat-box stat-f">
            <div class="stat-icon"><img src="<?= esc(base_url('assets/icons/apple.svg')) ?>" alt=""></div>
            <h4>Nombre de regimes</h4>
            <p><?= esc((string) $regimesCount) ?></p>
        </div>
    </div>

    <div class="dashboard-blocks">
        <div class="grid-2">
            <div class="card">
                <h3 class="section-title">Repartition des objectifs</h3>
                <p class="section-subtitle">Graphique circulaire des objectifs choisis par les utilisateurs.</p>
                <div class="pie-chart-container" id="objective-pie">
                    <div class="pie-chart"></div>
                    <div class="pie-legend"></div>
                </div>
            </div>

            <div class="card">
                <h3 class="section-title">Tendance du chiffre d'affaires</h3>
                <p class="section-subtitle">Evolution sur les 6 derniers mois.</p>
                <div class="trend-chart">
                    <canvas id="revenue-trend"></canvas>
                </div>
            </div>
        </div>

        <div class="card">
            <h3 class="section-title">Derniers utilisateurs</h3>
            <p class="section-subtitle">Les cinq inscriptions les plus recentes.</p>

            <?php if ($recentUsers !== []): ?>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Email</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentUsers as $user): ?>
                                <tr>
                                    <td><?= esc($user['nom'] ?? '') ?></td>
                                    <td><?= esc($user['email'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="hint">Aucun utilisateur recent.</p>
            <?php endif; ?>
        </div>
    </div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script src="<?= base_url('assets/js/charts.js') ?>"></script>
    <script>
        (function () {
            var pieData = <?= json_encode($pieData) ?>;
            var trendLabels = <?= json_encode($trendLabels) ?>;
            var trendValues = <?= json_encode($trendValues) ?>;

            renderPieChart({
                containerId: 'objective-pie',
                data: pieData
            });

            renderTrendChart({
                canvasId: 'revenue-trend',
                labels: trendLabels,
                values: trendValues,
                color: '#1f8f6a'
            });
        }());
    </script>
<?= $this->endSection() ?>
