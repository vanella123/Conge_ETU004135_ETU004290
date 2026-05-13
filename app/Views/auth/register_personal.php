<?= $this->extend('auth/layout') ?>

<?= $this->section('title') ?>Inscription - Étape 1<?= $this->endSection() ?>

<?= $this->section('body_class') ?>fo-body auth-page auth-with-topbar<?= $this->endSection() ?>

<?= $this->section('topbar') ?>
<?= $this->include('frontoffice/partials/navbar') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php $p = is_array($personal ?? null) ? $personal : []; ?>
<section class="auth-shell">
    <div class="auth-progress">
        <div class="auth-progress-meta">
            <span>Étape 1/3</span><span>Informations personnelles</span>
        </div>
        <div class="auth-progress-bar"><div class="auth-progress-fill" style="width:33.33%"></div></div>
    </div>
    <div class="auth-grid">
        <div class="auth-promo">
            <h1>Créer un compte</h1>
            <p class="sub">Commencez votre parcours avec des informations claires et sûres.</p>
        </div>
        <div class="auth-form-panel">
            <form action="<?= site_url('/register') ?>" method="post" class="stack" data-ajax-form="true" id="register-personal-form">
                <?= csrf_field() ?>
                <div class="form-feedback" data-form-feedback></div>

                <div class="field-wrap">
                    <label for="nom">Nom complet <span class="required-star">*</span></label>
                    <input type="text" id="nom" name="nom" minlength="2" maxlength="100" placeholder="Ex: Jean Rakoto" value="<?= esc(old('nom', $p['nom'] ?? '')) ?>" required>
                    <span class="field-icon" data-icon="nom"></span>
                    <div class="field-error" data-field-error="nom"></div>
                </div>

                <div class="field-wrap">
                    <label>Genre</label>
                    <div class="radio-group">
                        <?php $genre = old('genre', $p['genre'] ?? 'Autre'); ?>
                        <label class="radio-item"><input type="radio" name="genre" value="Homme" <?= $genre === 'Homme' ? 'checked' : '' ?>>Homme</label>
                        <label class="radio-item"><input type="radio" name="genre" value="Femme" <?= $genre === 'Femme' ? 'checked' : '' ?>>Femme</label>
                        <label class="radio-item"><input type="radio" name="genre" value="Autre" <?= $genre === 'Autre' ? 'checked' : '' ?>>Autre</label>
                    </div>
                    <div class="field-error" data-field-error="genre"></div>
                </div>

                <div class="field-wrap">
                    <label for="date_naissance">Date de naissance</label>
                    <input type="date" id="date_naissance" name="date_naissance" value="<?= esc(old('date_naissance', $p['date_naissance'] ?? '')) ?>">
                    <span class="field-icon" data-icon="date_naissance"></span>
                    <div class="field-error" data-field-error="date_naissance"></div>
                </div>

                <div class="field-wrap">
                    <label for="email">Email <span class="required-star">*</span></label>
                    <input type="email" id="email" name="email" maxlength="120" placeholder="Ex: jean@email.com" value="<?= esc(old('email', $p['email'] ?? '')) ?>" required>
                    <span class="field-icon" data-icon="email"></span>
                    <div class="field-error" data-field-error="email"></div>
                </div>

                <div class="field-wrap">
                    <label for="mot_de_passe">Mot de passe <span class="required-star">*</span></label>
                    <input type="password" id="mot_de_passe" name="mot_de_passe" maxlength="72" placeholder="8+ caractères, 1 majuscule, 1 chiffre" value="<?= esc(old('mot_de_passe', $p['mot_de_passe'] ?? '')) ?>" required>
                    <button type="button" class="eye-btn" id="toggle-password"><img src="<?= base_url('assets/icons/eye.svg') ?>" alt="Voir"></button>
                    <div class="field-hint" id="password-strength">Force : -</div>
                    <div class="field-error" data-field-error="mot_de_passe"></div>
                </div>

                <div class="actions">
                    <button type="submit" class="btn">Continuer</button>
                    <a href="<?= site_url('/login') ?>" class="btn btn-secondary">Déjà un compte</a>
                </div>
            </form>
        </div>
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(() => {
    const form = document.getElementById('register-personal-form');
    if (!form) return;
    const emailInput = form.querySelector('#email');
    const passwordInput = form.querySelector('#mot_de_passe');

    const setFieldState = (name, ok, message = '') => {
        const input = form.querySelector(`[name="${name}"]`);
        const icon = form.querySelector(`[data-icon="${name}"]`);
        const err = form.querySelector(`[data-field-error="${name}"]`);
        if (!input || !err) return;
        if (String(input.value || '').trim() === '') {
            if (icon) icon.className = 'field-icon';
            input.classList.remove('is-invalid','is-valid');
            err.textContent = '';
            return;
        }
        if (icon) icon.className = `field-icon ${ok ? 'ok' : 'err'}`;
        input.classList.toggle('is-invalid', !ok); input.classList.toggle('is-valid', !!ok);
        err.textContent = ok ? '' : message;
    };

    form.querySelector('#nom').addEventListener('blur', (e) => {
        const v = e.target.value.trim();
        setFieldState('nom', v.length >= 2 && v.length <= 100, 'Le nom doit contenir entre 2 et 100 caractères.');
    });

    form.querySelector('#date_naissance').addEventListener('blur', (e) => {
        const v = e.target.value;
        setFieldState('date_naissance', v === '' || /^\d{4}-\d{2}-\d{2}$/.test(v), 'Date invalide.');
    });

    emailInput.addEventListener('blur', async () => {
        const v = emailInput.value.trim();
        if (!v) return setFieldState('email', false, 'Email requis. Exemple: jean@gmail.com');
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) return setFieldState('email', false, 'Format invalide. Exemple: jean@gmail.com');
        try {
            const res = await fetch('<?= site_url('/register/check-email') ?>', {method:'POST', headers:{'X-Requested-With':'XMLHttpRequest'}, body:new URLSearchParams({email:v})});
            const data = await res.json();
            setFieldState('email', !!data.available, data.message || 'Email indisponible.');
        } catch (_) { setFieldState('email', false, 'Vérification impossible. Réessayez.'); }
    });

    const updateStrength = () => {
        const v = passwordInput.value;
        const ok = v.length >= 8 && /[A-Z]/.test(v) && /\d/.test(v);
        const score = [v.length >= 8, /[A-Z]/.test(v), /\d/.test(v)].filter(Boolean).length;
        document.getElementById('password-strength').textContent = `Force : ${['Faible','Moyenne','Bonne','Forte'][score]}`;
        setFieldState('mot_de_passe', ok, 'Le mot de passe doit contenir au moins 8 caractères, une majuscule et un chiffre.');
    };
    passwordInput.addEventListener('input', updateStrength);
    passwordInput.addEventListener('blur', updateStrength);

    const btn = document.getElementById('toggle-password');
    btn.addEventListener('click', () => {
        const hidden = passwordInput.type === 'password';
        passwordInput.type = hidden ? 'text' : 'password';
        btn.querySelector('img').src = hidden ? '<?= base_url('assets/icons/eye-off.svg') ?>' : '<?= base_url('assets/icons/eye.svg') ?>';
    });
})();
</script>
<?= $this->endSection() ?>
