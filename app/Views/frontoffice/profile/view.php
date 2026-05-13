<?= $this->extend('frontoffice/layout') ?>

<?= $this->section('title') ?>Mon Profil<?= $this->endSection() ?>


<?= $this->section('content') ?>
<section class="stack profile-normal">
    <div class="hero">
        <div class="page-header">
            <h1>Mon profil</h1>
            <p class="sub">Consultez vos informations personnelles et santé, puis accédez rapidement à vos actions principales.</p>
        </div>
        <div class="hero-actions">
            <a href="<?= site_url('/profile/edit') ?>" class="btn btn-ghost btn-icon" title="Modifier le profil">
                <img src="<?= esc(base_url('assets/icons/pencil.svg')) ?>" alt="Modifier">
            </a>
            <a href="<?= site_url('/mes-regimes') ?>" class="btn btn-secondary">Mes régimes</a>
            <a href="<?= site_url('/dashboard') ?>" class="btn btn-secondary">Dashboard</a>
        </div>
    </div>

    <div class="card">
        <div class="section-title">
            <div>
                <h2>Informations personnelles</h2>
                <p class="sub">Vos données d’identité principales.</p>
            </div>
        </div>
        <div class="metric-grid">
            <div class="metric-card"><div class="metric-label icon"><img src="<?= esc(base_url('assets/icons/user-round.svg')) ?>" alt="">Nom</div><div class="metric-value small"><?= esc($user['nom']) ?></div></div>
            <div class="metric-card"><div class="metric-label icon"><img src="<?= esc(base_url('assets/icons/mail.svg')) ?>" alt="">Email</div><div class="metric-value small"><?= esc($user['email']) ?></div></div>
            <div class="metric-card"><div class="metric-label icon"><img src="<?= esc(base_url('assets/icons/venus-and-mars.svg')) ?>" alt="">Genre</div><div class="metric-value small metric-genre"><?= esc($user['genre']) ?></div></div>
            <div class="metric-card"><div class="metric-label icon"><img src="<?= esc(base_url('assets/icons/calendar-days.svg')) ?>" alt="">Date de naissance</div><div class="metric-value small"><?= esc($user['date_naissance'] ?? 'Non renseignée') ?></div></div>
        </div>
    </div>

    <div class="card">
        <div class="section-title">
            <div>
                <h2>Informations santé</h2>
                <p class="sub">Les indicateurs liés à votre parcours.</p>
            </div>
        </div>
        <div class="health-cards">
            <div class="health-card">
                <div class="health-row"><span class="health-label">Taille</span><span class="health-value"><?= esc($user['taille_cm']) ?> cm</span></div>
                <div class="health-row"><span class="health-label">Poids actuel</span><span class="health-value"><?= esc($user['poids_kg']) ?> kg</span></div>
                <div class="health-row"><span class="health-label">IMC</span><span class="health-value"><?= $imc !== null ? number_format($imc, 2, ',', ' ') : 'Non calculable' ?></span></div>
            </div>
            <div class="health-card">
                <div class="health-row"><span class="health-label">Objectif</span><span class="health-value"><?= $objectif !== null ? esc($objectif['label_objectif']) : 'Non défini' ?></span></div>
                <div class="health-row"><span class="health-label">Poids objectif</span><span class="health-value"><?= isset($user['poids_objectif']) && $user['poids_objectif'] !== null ? esc($user['poids_objectif']) . ' kg' : 'Non défini' ?></span></div>
                <div class="health-row"><span class="health-label">Statut</span><span class="health-value"><?= $user['is_gold'] ? '<span class="badge badge-success">Gold</span>' : '<span class="badge">Standard</span>' ?></span></div>
            </div>
            <div class="health-card">
                <div class="health-row"><span class="health-label">Solde actuel</span><span class="health-value"><?= esc((string) ($user['argent'] ?? 0)) ?> Ar</span></div>
            </div>
        </div>
    </div>
</section>
<?= $this->endSection() ?>
