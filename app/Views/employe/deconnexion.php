<?= $this->extend('auth/layout') ?>

<?= $this->section('title') ?>Déconnexion<?= $this->endSection() ?>

<?= $this->section('body_class') ?>fo-body auth-page auth-with-topbar<?= $this->endSection() ?>

<?= $this->section('topbar') ?>
<?= $this->include('frontoffice/partials/navbar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="auth-shell">
    <div class="auth-grid">
        <div class="auth-promo">
            <div>
                <div class="auth-badge">Session fermée</div>
                <div class="page-header">
                    <h1>Déconnexion effectuée</h1>
                    <p class="sub">Votre session a bien été supprimée. Vous allez être redirigé vers la page de connexion.</p>
                </div>
            </div>
            <div class="auth-promo-footer">À bientôt dans votre espace employé</div>
        </div>

        <div class="auth-form-panel">
            <div class="stack">
                <p class="auth-link" style="margin: 0;">Vous pouvez revenir à tout moment sur votre espace.</p>
                <a class="btn" href="<?= esc($redirectTo ?? site_url('/connexion')) ?>">Retour à la connexion</a>
            </div>
        </div>
    </div>
</section>
<?php if (! empty($redirectTo)): ?>
<script>
    setTimeout(() => {
        window.location.href = <?= json_encode($redirectTo) ?>;
    }, 2000);
</script>
<?php endif; ?>
<?= $this->endSection() ?>