<?= $this->extend('frontoffice/landing_layout') ?>

<?= $this->section('title') ?>Le régime intelligent<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- ═══ Hero ═══ -->
<section class="landing-hero">
    <div class="landing-hero-inner">
        <div>
            <h1>Votre <span>régime alimentaire</span> personnalisé</h1>
            <p>Découvrez des régimes adaptés à votre mode de vie et à vos objectifs. Suivi intelligent, résultats concrets.</p>
            <div class="hero-actions">
                <a href="<?= site_url('register') ?>" class="btn">Commencer maintenant</a>
                <a href="<?= site_url('login') ?>" class="btn btn-secondary">Se connecter</a>
            </div>
        </div>
        <img src="<?= base_url('assets/img/hero.png') ?>" alt="Illustration régime" class="hero-img" width="540" height="360">
    </div>
</section>

<!-- ═══ Stats Bar ═══ -->
<section class="stats-bar">
    <div class="stats-bar-inner">
        <div class="stat-item">
            <h3>200+</h3>
            <p>Régimes disponibles</p>
        </div>
        <div class="stat-item">
            <h3>5 000+</h3>
            <p>Utilisateurs actifs</p>
        </div>
        <div class="stat-item">
            <h3>98%</h3>
            <p>Satisfaction client</p>
        </div>
    </div>
</section>

<!-- ═══ Features ═══ -->
<section class="features-section">
    <div class="features-inner">
        <h2>Pourquoi nous choisir ?</h2>
        <p>Des outils modernes pour un suivi efficace de votre alimentation.</p>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon"><img src="<?= base_url('assets/icons/target.svg') ?>" alt=""></div>
                <h3>Objectifs personnalisés</h3>
                <p>Définissez vos objectifs de perte ou de prise de poids et laissez notre algorithme trouver le régime adapté.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><img src="<?= base_url('assets/icons/activity.svg') ?>" alt=""></div>
                <h3>Suivi IMC intelligent</h3>
                <p>Calculez votre IMC automatiquement et suivez votre progression en temps réel avec des recommandations.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon"><img src="<?= base_url('assets/icons/apple.svg') ?>" alt=""></div>
                <h3>Composition nutritionnelle</h3>
                <p>Chaque régime détaille sa répartition viande, poisson et volaille pour une alimentation équilibrée.</p>
            </div>
        </div>
    </div>
</section>

<!-- ═══ How It Works ═══ -->
<section class="how-section">
    <div class="how-inner">
        <h2>Comment ça marche ?</h2>
        <p>Trois étapes simples pour commencer votre transformation.</p>
        <div class="how-steps">
            <div class="how-step">
                <div class="step-number">1</div>
                <h3>Créez votre profil</h3>
                <p>Renseignez vos informations de santé et définissez vos objectifs personnels.</p>
            </div>
            <div class="how-step">
                <div class="step-number">2</div>
                <h3>Choisissez un régime</h3>
                <p>Parcourez notre catalogue de régimes filtrés selon vos besoins et votre budget.</p>
            </div>
            <div class="how-step">
                <div class="step-number">3</div>
                <h3>Suivez vos résultats</h3>
                <p>Consultez votre tableau de bord et ajustez votre plan en fonction de votre progression.</p>
            </div>
        </div>
    </div>
</section>

<!-- ═══ Featured Meals ═══ -->
<section class="features-section">
    <div class="features-inner">
        <h2>Nos types de repas</h2>
        <p>Une variété de repas équilibrés adaptés à chaque régime.</p>
        <div class="features-grid">
            <div class="feature-card">
                <img src="<?= base_url('assets/img/meal1.png') ?>" alt="Repas 1" class="hero-img">
                <h3>Repas légers</h3>
                <p>Salades fraîches et bowls pour une alimentation saine au quotidien.</p>
            </div>
            <div class="feature-card">
                <img src="<?= base_url('assets/img/meal2.png') ?>" alt="Repas 2" class="hero-img">
                <h3>Repas protéinés</h3>
                <p>Viandes et poissons grillés pour maintenir votre masse musculaire.</p>
            </div>
            <div class="feature-card">
                <img src="<?= base_url('assets/img/sport.png') ?>" alt="Sport" class="hero-img">
                <h3>Activité sportive</h3>
                <p>Nos régimes sont combinés avec des activités sportives pour des résultats optimaux.</p>
            </div>
        </div>
    </div>
</section>

<!-- ═══ CTA ═══ -->
<section class="cta-section">
    <div class="cta-inner">
        <h2>Prêt à transformer votre alimentation ?</h2>
        <p>Rejoignez des milliers d'utilisateurs qui ont déjà atteint leurs objectifs.</p>
        <a href="<?= site_url('register') ?>" class="btn">Créer un compte gratuitement</a>
    </div>
</section>

<?= $this->endSection() ?>
