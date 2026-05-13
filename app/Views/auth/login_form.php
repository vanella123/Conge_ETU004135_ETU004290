<div class="auth-page geo-bg">
  <div class="auth-split">
    <div class="auth-left">
      <div>
        <h1 class="auth-left-brand">TechMada RH
          <span>Gestion des congés</span>
        </h1>
        <p class="auth-left-text">Saisissez vos identifiants pour accéder à votre espace. Rôles : employe, rh, admin.</p>
      </div>
      <div class="auth-roles">
        <div class="role-pill"><i class="bi bi-person-circle"></i><div class="role-pill-name">Employé</div><div class="role-pill-cred">role=employe</div></div>
        <div class="role-pill"><i class="bi bi-person-badge"></i><div class="role-pill-name">Responsable RH</div><div class="role-pill-cred">role=rh</div></div>
        <div class="role-pill"><i class="bi bi-shield-lock"></i><div class="role-pill-name">Administrateur</div><div class="role-pill-cred">role=admin</div></div>
      </div>
    </div>

    <div class="auth-right">
      <h2 class="auth-title">Connexion</h2>
      <p class="auth-sub">Entrez votre email et mot de passe</p>

      <?php if (session()->getFlashdata('error')): ?>
        <div class="flash flash-error"><?= esc(session()->getFlashdata('error')) ?></div>
      <?php endif; ?>

      <?php if (isset($errors) && is_array($errors)): ?>
        <div class="flash flash-error">
          <?php foreach ($errors as $e): ?>
            <div><?= esc($e) ?></div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <form action="/login" method="post">
        <?= csrf_field() ?>
        <div class="f-group">
          <label class="f-label">Email</label>
          <input type="email" name="email" class="f-input" value="<?= esc(old('email')) ?>" />
        </div>
        <div class="f-group">
          <label class="f-label">Mot de passe</label>
          <input type="password" name="password" class="f-input" />
        </div>
        <div class="form-actions">
          <button class="btn-primary" type="submit">Se connecter</button>
        </div>
      </form>
      <div class="auth-footer">Besoin d'aide ? Contactez l'administrateur.</div>
    </div>
  </div>
</div>
