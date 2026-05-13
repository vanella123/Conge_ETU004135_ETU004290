<?php $this->extend('layout'); ?>

<?php $this->section('content'); ?>

<div class="form-section">
    <h3>Mon Profil</h3>

    <form method="post" action="<?= base_url('employe/profil') ?>">
        <?= csrf_field() ?>

        <div class="form-grid-2">
            <div class="f-group">
                <label class="f-label">Nom</label>
                <input class="f-input" type="text" name="nom" value="<?= esc(old('nom', $user['nom'] ?? '')) ?>" required>
            </div>
            <div class="f-group">
                <label class="f-label">Prénom</label>
                <input class="f-input" type="text" name="prenom" value="<?= esc(old('prenom', $user['prenom'] ?? '')) ?>" required>
            </div>
        </div>

        <div class="form-grid-2">
            <div class="f-group">
                <label class="f-label">Email</label>
                <input class="f-input" type="email" name="email" value="<?= esc(old('email', $user['email'] ?? '')) ?>" required>
            </div>
            <div class="f-group">
                <label class="f-label">Nouveau mot de passe</label>
                <input class="f-input" type="password" name="password" placeholder="Laisser vide pour conserver">
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn-forest">Enregistrer</button>
        </div>
    </form>
</div>

<?php $this->endSection(); ?>