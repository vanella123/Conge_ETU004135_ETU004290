<?= $this->extend('frontoffice/layout') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>
<?= $this->section('head') ?>

<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="stack">
    <div class="hero">
        <div class="page-header">
            <h1>Dashboard</h1>
            <p class="sub">Tableau de bord personnel.</p>
        </div>
    </div>

    <div class="section-title">
        <div>
            <h2>Vue d’ensemble</h2>
            <p class="sub">Les informations importantes en un coup d’œil.</p>
        </div>
    </div>

    <div class="metric-grid">
        <div class="metric-card">
            <div class="metric-label metric-with-icon"><img src="<?= esc(base_url('assets/icons/activity.svg')) ?>" alt="">IMC</div>
            <div class="metric-value"><?= session()->get('imc') !== null ? esc((string) session()->get('imc')) : 'N/A' ?></div>
        </div>
        <div class="metric-card">
            <div class="metric-label metric-with-icon"><img src="<?= esc(base_url('assets/icons/target.svg')) ?>" alt="">Objectif</div>
            <div class="metric-value small"><?= esc((string) (session()->get('objectif_label') ?? 'N/A')) ?></div>
        </div>
        <div class="metric-card">
            <div class="metric-label metric-with-icon"><img src="<?= esc(base_url('assets/icons/wallet.svg')) ?>" alt="">Solde</div>
            <div class="metric-value"><?= esc((string) (session()->get('argent') ?? 0)) ?> Ar</div>
        </div>
        <div class="metric-card">
            <div class="metric-label metric-with-icon"><img src="<?= esc(base_url('assets/icons/crown.svg')) ?>" alt="">Statut</div>
            <div class="metric-value"><?= session()->get('is_gold') ? '<span class="badge badge-success">Gold</span>' : '<span class="badge">Standard</span>' ?></div>
        </div>
        <div class="metric-card">
            <div class="metric-label metric-with-icon"><img src="<?= esc(base_url('assets/icons/apple.svg')) ?>" alt="">Vos régimes</div>
            <div class="metric-value"><?= esc((string) ($regimesCount ?? 0)) ?></div>
        </div>
    </div>

    <div class="card">
        <div class="section-title">
            <div>
                <h2>Transactions</h2>
                <p class="sub">Consultez l'historique de vos achats.</p>
            </div>
        </div>
        <div class="actions">
            <a href="<?= site_url('/transactions') ?>" class="btn btn-secondary">Voir mes transactions</a>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
