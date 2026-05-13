<?= $this->extend('backoffice/layout') ?>

<?= $this->section('title') ?>Gestion des codes promo<?= $this->endSection() ?>

<?= $this->section('page_title') ?>Codes promo<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Gardez la liste des codes claire, filtrez rapidement leur etat et laissez la consommation se faire via la validation admin.<?= $this->endSection() ?>
<?= $this->section('page_actions') ?>
    <a href="<?= base_url('admin/promos/validate') ?>" class="btn btn-secondary">Demandes a valider</a>
    <a href="<?= base_url('admin/promos/create') ?>" class="btn btn-primary">Nouveau code</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="card">
        <h3 class="section-title">Filtres</h3>
        <p class="section-subtitle">Affinez la liste par montant et etat d'utilisation.</p>

        <form method="get" action="<?= base_url('admin/promos') ?>" class="stack">
            <div class="filters-grid">
                <div class="field">
                    <label>Montant (Ar)</label>
                    <div class="filter-pair">
                        <input type="number" step="0.01" name="montant_min" value="<?= esc($filters['montant_min'] ?? '') ?>" placeholder="Min">
                        <input type="number" step="0.01" name="montant_max" value="<?= esc($filters['montant_max'] ?? '') ?>" placeholder="Max">
                    </div>
                </div>
                <div class="field">
                    <label>Etat</label>
                    <div class="radio-row">
                        <?php $etat = $filters['etat'] ?? 'tous'; ?>
                        <label class="radio-chip">
                            <input type="radio" name="etat" value="tous" <?= $etat === 'tous' ? 'checked' : '' ?>>
                            Tout
                        </label>
                        <label class="radio-chip">
                            <input type="radio" name="etat" value="disponible" <?= $etat === 'disponible' ? 'checked' : '' ?>>
                            Disponible
                        </label>
                        <label class="radio-chip">
                            <input type="radio" name="etat" value="utilise" <?= $etat === 'utilise' ? 'checked' : '' ?>>
                            Utilise
                        </label>
                    </div>
                </div>
            </div>

            <div class="actions-inline">
                <button type="submit" class="btn btn-primary">Filtrer</button>
                <a href="<?= base_url('admin/promos') ?>" class="btn btn-secondary">Reinitialiser</a>
            </div>
        </form>
    </div>

    <div class="card">
        <h3 class="section-title">Liste des codes promo</h3>
        <p class="section-subtitle"><?= count($promos ?? []) ?> resultat(s).</p>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Code</th>
                        <th>Montant</th>
                        <th>Etat</th>
                        <th>Utilise par</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (! empty($promos)): ?>
                        <?php foreach ($promos as $promo): ?>
                            <tr>
                                <td><span class="code-pill"><?= esc($promo['code']) ?></span></td>
                                <td><?= esc(number_format((float) $promo['montant'], 2, ',', ' ')) ?> Ar</td>
                                <td>
                                    <?php if ((int) ($promo['deja_utilise'] ?? 0) === 1): ?>
                                        <span class="badge warn">Utilise</span>
                                    <?php else: ?>
                                        <span class="badge success">Disponible</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc((string) ($promo['utilisateur_nom'] ?? '')) ?></td>
                                <td>
                                    <div class="table-actions">
                                        <a href="<?= base_url('admin/promos/edit/' . $promo['id_code']) ?>" class="btn btn-secondary btn-icon icon-action" title="Modifier le code promo">
                                            <img src="<?= esc(base_url('assets/icons/pencil.svg')) ?>" alt="Modifier">
                                        </a>
                                        <form action="<?= base_url('admin/promos/delete/' . $promo['id_code']) ?>" method="post">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-danger btn-icon icon-action" title="Supprimer le code promo" data-confirm-message="Supprimer le code promo &quot;<?= esc($promo['code']) ?>&quot; ?">
                                                <img src="<?= esc(base_url('assets/icons/trash-2.svg')) ?>" alt="Supprimer">
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">Aucun code promo ne correspond aux filtres actuels.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?= $this->endSection() ?>
