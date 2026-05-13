<?= $this->extend('backoffice/layout') ?>

<?= $this->section('title') ?><?= esc($title ?? 'Option') ?><?= $this->endSection() ?>
<?= $this->section('page_title') ?><?= esc($title ?? 'Option') ?><?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Renseignez ici les regles d'acces et la tarification visible cote client. Chaque mise a jour cree aussi une entree dans l'historique.<?= $this->endSection() ?>
<?= $this->section('page_actions') ?>
    <a href="<?= base_url('admin/options') ?>" class="btn btn-secondary">Retour aux options</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="card">
        <?php if (! empty($validation)): ?>
            <div class="flash error"><?= esc($validation->listErrors()) ?></div>
        <?php endif; ?>

        <form action="<?= esc($action) ?>" method="post" class="stack">
            <?= csrf_field() ?>
            <div class="grid-2">
                <div class="field">
                    <label for="nom_option">Nom de l'option</label>
                    <input id="nom_option" type="text" name="nom_option" value="<?= esc(old('nom_option', $option['nom_option'] ?? 'Gold')) ?>" required>
                </div>
                <div class="field">
                    <label for="date_debut">Date d'effet</label>
                    <input id="date_debut" type="date" name="date_debut" value="<?= esc(old('date_debut', date('Y-m-d'))) ?>">
                </div>
            </div>
            <div class="grid-3">
                <div class="field">
                    <label for="nb_regimes_achetes">Nb de regimes requis</label>
                    <input id="nb_regimes_achetes" type="number" name="nb_regimes_achetes" min="0" value="<?= esc(old('nb_regimes_achetes', $option['nb_regimes_achetes'] ?? 0)) ?>" required>
                </div>
                <div class="field">
                    <label for="prix_unique">Prix unique</label>
                    <input id="prix_unique" type="number" step="0.01" name="prix_unique" value="<?= esc(old('prix_unique', $option['prix_unique'] ?? '')) ?>" required>
                </div>
                <div class="field">
                    <label for="reduction_pourcentage">Reduction (%)</label>
                    <input id="reduction_pourcentage" type="number" step="0.01" name="reduction_pourcentage" value="<?= esc(old('reduction_pourcentage', $option['reduction_pourcentage'] ?? '')) ?>" required>
                </div>
            </div>
            <div class="actions-inline">
                <a href="<?= base_url('admin/options') ?>" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary">Enregistrer</button>
            </div>
        </form>
    </div>
<?= $this->endSection() ?>
