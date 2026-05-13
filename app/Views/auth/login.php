<?= $this->extend('auth/layout') ?>

<?= $this->section('title') ?>Connexion<?= $this->endSection() ?>

<?= $this->section('body_class') ?>fo-body auth-page auth-with-topbar<?= $this->endSection() ?>

<?= $this->section('topbar') ?>
<?= $this->include('frontoffice/partials/navbar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="auth-shell">
    <div class="auth-grid">
        <div class="auth-promo">
            <div>
                <div class="auth-badge">Espace membre</div>
                <div class="page-header">
                    <h1>Connexion</h1>
                    <p class="sub">Accédez à votre espace personnel, vos régimes et votre suivi en quelques secondes.</p>
                </div>
            </div>
            <div class="auth-promo-footer">Connexion sécurisée · expérience mobile propre · accès rapide</div>
        </div>

        <div class="auth-form-panel">
            <form action="<?= site_url('/login') ?>" method="post" class="stack" data-ajax-form="true">
                <?= csrf_field() ?>
                <div class="form-feedback" data-form-feedback></div>

                <div>
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" maxlength="120" autocomplete="email" placeholder="Ex: jean@gmail.com" value="<?= esc(old('email', 'jean@gmail.com')) ?>" required>
                    <div class="field-error" data-field-error="email"></div>
                </div>

                <div class="field-wrap">
                    <label for="mot_de_passe">Mot de passe</label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" autocomplete="current-password" value="<?= esc(old('mot_de_passe', 'jean123')) ?>" required>
                    <button type="button" class="eye-btn" id="toggle-login-password"><img src="<?= base_url('assets/icons/eye.svg') ?>" alt="Voir"></button>
                    <div class="field-error" data-field-error="mot_de_passe"></div>
                </div>

                <button type="submit" class="btn">Se connecter</button>
            </form>

            <p class="auth-link">Pas encore de compte ? <a href="<?= site_url('/register') ?>">Créer un compte</a></p>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(() => {
  const input = document.getElementById('mot_de_passe');
  const btn = document.getElementById('toggle-login-password');
  if (!input || !btn) return;
  btn.addEventListener('click', () => {
    const hidden = input.type === 'password';
    input.type = hidden ? 'text' : 'password';
    btn.querySelector('img').src = hidden ? '<?= base_url('assets/icons/eye-off.svg') ?>' : '<?= base_url('assets/icons/eye.svg') ?>';
  });
})();
</script>
<?= $this->endSection() ?>
