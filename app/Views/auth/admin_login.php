<?= $this->extend('auth/layout') ?>

<?= $this->section('title') ?>Connexion Admin<?= $this->endSection() ?>

<?= $this->section('body_class') ?>fo-body auth-page auth-with-topbar<?= $this->endSection() ?>

<?= $this->section('topbar') ?>
<?= $this->include('frontoffice/partials/navbar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="auth-shell">
    <div class="auth-grid">
        <div class="auth-promo">
            <div>
                <div class="auth-badge">Admin Panel</div>
                <div class="page-header">
                    <h1>Gestion du Régime</h1>
                    <p class="sub">Accédez au panneau d'administration pour gérer les régimes, utilisateurs, promos et options.</p>
                </div>
            </div>
            <div class="auth-promo-footer">© <?= date('Y') ?> Gestion du Régime. Tous droits réservés.</div>
        </div>

        <div class="auth-form-panel">
            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
            <?php endif; ?>

            <form id="loginForm" action="<?= base_url('/admin/authenticate') ?>" method="post" class="stack" novalidate>
                <?= csrf_field() ?>

                <div>
                    <label for="email">Adresse Email</label>
                    <input type="email" id="email" name="email" placeholder="exemple@email.com" value="<?= esc(old('email', 'admin@gmail.com')) ?>" required autocomplete="email">
                </div>

                <div class="field-wrap">
                    <label for="mot_de_passe">Mot de passe</label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="••••••••" value="<?= esc(old('mot_de_passe', 'admin123')) ?>" required autocomplete="current-password">
                    <button type="button" class="eye-btn" id="togglePassword"><img src="<?= base_url('assets/icons/eye.svg') ?>" alt="Voir"></button>
                </div>

                <button type="submit" class="btn" id="btnSubmit">Se connecter</button>
            </form>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(() => {
    const passwordInput = document.getElementById('mot_de_passe');
    const toggleBtn = document.getElementById('togglePassword');
    if (passwordInput && toggleBtn) {
        toggleBtn.addEventListener('click', () => {
            const hidden = passwordInput.type === 'password';
            passwordInput.type = hidden ? 'text' : 'password';
            toggleBtn.querySelector('img').src = hidden ? '<?= base_url('assets/icons/eye-off.svg') ?>' : '<?= base_url('assets/icons/eye.svg') ?>';
        });
    }

    const form = document.getElementById('loginForm');
    form?.addEventListener('submit', () => {
        const btn = document.getElementById('btnSubmit');
        if (btn) {
            btn.textContent = 'Connexion en cours...';
            btn.disabled = true;
        }
    });

    document.getElementById('email')?.focus();
})();
</script>
<?= $this->endSection() ?>
