<?= $this->extend('frontoffice/layout') ?>

<?= $this->section('title') ?>Modifier mon profil<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php $validationErrors = session()->getFlashdata('errors') ?? []; ?>
<section class="stack">
    <div class="hero"><h1>Modifier mon profil</h1><p class="sub">Mettez a jour vos informations avec verification locale claire.</p></div>

    <form method="POST" action="<?= site_url('/profile/update') ?>" class="stack" data-ajax-form="true" id="profile-edit-form">
        <?= csrf_field() ?>
        <div class="form-feedback" data-form-feedback></div>

        <div class="card stack">
            <div>
                <label for="nom">Nom *</label>
                <input type="text" id="nom" name="nom" minlength="3" maxlength="80" value="<?= old('nom', esc($user['nom'])) ?>" required>
                <div class="field-error" data-field-error="nom"><?php if (isset($validationErrors['nom'])): ?><?= esc($validationErrors['nom']) ?><?php endif; ?></div>
            </div>
            <div class="grid">
                <div>
                    <label for="genre">Genre</label>
                    <select id="genre" name="genre">
                        <option value="Autre" <?= old('genre', $user['genre']) === 'Autre' ? 'selected' : '' ?>>Autre</option>
                        <option value="Homme" <?= old('genre', $user['genre']) === 'Homme' ? 'selected' : '' ?>>Homme</option>
                        <option value="Femme" <?= old('genre', $user['genre']) === 'Femme' ? 'selected' : '' ?>>Femme</option>
                    </select>
                    <div class="field-error" data-field-error="genre"><?php if (isset($validationErrors['genre'])): ?><?= esc($validationErrors['genre']) ?><?php endif; ?></div>
                </div>
                <div>
                    <label for="date_naissance">Date de naissance</label>
                    <input type="date" id="date_naissance" name="date_naissance" value="<?= old('date_naissance', esc($user['date_naissance'])) ?>">
                    <div class="field-error" data-field-error="date_naissance"><?php if (isset($validationErrors['date_naissance'])): ?><?= esc($validationErrors['date_naissance']) ?><?php endif; ?></div>
                </div>
            </div>
        </div>

        <div class="card stack">
            <div class="grid">
                <div>
                    <label for="taille_cm">Taille (cm) *</label>
                    <input type="number" step="0.01" min="50" max="260" id="taille_cm" name="taille_cm" value="<?= old('taille_cm', esc($user['taille_cm'])) ?>" required>
                    <div class="field-error" data-field-error="taille_cm"><?php if (isset($validationErrors['taille_cm'])): ?><?= esc($validationErrors['taille_cm']) ?><?php endif; ?></div>
                </div>
                <div>
                    <label for="poids_kg">Poids actuel (kg) *</label>
                    <input type="number" step="0.01" min="20" max="350" id="poids_kg" name="poids_kg" value="<?= old('poids_kg', esc($user['poids_kg'])) ?>" required>
                    <div class="field-error" data-field-error="poids_kg"><?php if (isset($validationErrors['poids_kg'])): ?><?= esc($validationErrors['poids_kg']) ?><?php endif; ?></div>
                </div>
            </div>

            <div>
                <label>Objectif</label>
                <div class="radio-group" id="objectif-group">
                    <?php foreach ($objectifs as $objectif): ?>
                        <label class="radio-item">
                            <input type="radio" name="id_objectif" value="<?= $objectif['id_objectif'] ?>" <?= old('id_objectif', (string) $user['id_objectif']) === (string) $objectif['id_objectif'] ? 'checked' : '' ?>>
                            <?= esc($objectif['label_objectif']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <div class="field-error" data-field-error="id_objectif"><?php if (isset($validationErrors['id_objectif'])): ?><?= esc($validationErrors['id_objectif']) ?><?php endif; ?></div>
            </div>

            <div id="poids-objectif-wrap">
                <label for="poids_objectif">Poids cible (kg)</label>
                <input type="number" step="0.01" min="20" max="350" id="poids_objectif" name="poids_objectif" value="<?= old('poids_objectif', esc($user['poids_objectif'] ?? '')) ?>">
                <div class="field-error" data-field-error="poids_objectif"><?php if (isset($validationErrors['poids_objectif'])): ?><?= esc($validationErrors['poids_objectif']) ?><?php endif; ?></div>
            </div>
        </div>

        <div class="actions">
            <button type="submit" class="btn">Enregistrer</button>
            <a href="<?= site_url('/profile') ?>" class="btn btn-secondary">Annuler</a>
        </div>
    </form>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(() => {
    const form = document.getElementById('profile-edit-form');
    if (!form) return;
    const poids = form.querySelector('#poids_kg');
    const objectifWrap = form.querySelector('#poids-objectif-wrap');
    const poidsObjectif = form.querySelector('#poids_objectif');

    const selectedLabel = () => {
        const checked = form.querySelector('input[name="id_objectif"]:checked');
        return checked ? checked.closest('label').innerText.toLowerCase() : '';
    };
    const toggleTarget = () => {
        const label = selectedLabel();
        const needs = label.includes('perte') || label.includes('prise');
        objectifWrap.style.display = needs ? 'block' : 'none';
        poidsObjectif.required = needs;
        if (!needs) poidsObjectif.value = '';
    };

    form.querySelectorAll('input[name="id_objectif"]').forEach((r) => r.addEventListener('change', toggleTarget));
    poidsObjectif.addEventListener('blur', () => {
        if (!poidsObjectif.required) return;
        const p = parseFloat(poids.value || '0');
        const o = parseFloat(poidsObjectif.value || '0');
        const label = selectedLabel();
        const err = form.querySelector('[data-field-error="poids_objectif"]');
        if (!o || o <= 0) return err.textContent = 'Poids cible requis.';
        if (label.includes('perte') && o >= p) return err.textContent = 'Le poids cible doit etre inferieur au poids actuel.';
        if (label.includes('prise') && o <= p) return err.textContent = 'Le poids cible doit etre superieur au poids actuel.';
        err.textContent = '';
    });

    toggleTarget();
})();
</script>
<?= $this->endSection() ?>
