<?= $this->extend('backoffice/layout') ?>

<?= $this->section('title') ?>Gestion des activites<?= $this->endSection() ?>
<?= $this->section('head') ?>
    
<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Activites sportives<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Pilotez les activites sportives avec une liste filtree, des actions rapides et une page detail par activite.<?= $this->endSection() ?>
<?= $this->section('page_actions') ?>
    <a href="<?= base_url('admin/activites/create') ?>" class="btn btn-primary">
        <img class="icon" src="<?= esc(base_url('assets/icons/plus.svg')) ?>" alt="">
        <span>Nouvelle activite</span>
    </a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="activity-hero">
        <h3>Catalogue des activites sportives</h3>
        <p>Retrouvez ici toutes les activites disponibles, leur frequence hebdomadaire et le nombre de regimes qui les utilisent deja.</p>
    </div>

    <div class="card">
        <h3 class="section-title">Filtres</h3>
        <p class="section-subtitle">Affinez la liste par nom et par frequence hebdomadaire.</p>

        <form method="get" action="<?= base_url('admin/activites') ?>" class="stack">
            <div class="filter-layout">
                <div class="field">
                    <label for="label_activite">Nom de l'activite</label>
                    <input id="label_activite" type="text" name="label_activite" value="<?= esc($filters['label_activite'] ?? '') ?>" placeholder="Ex: Cyclisme">
                </div>
                <div class="field">
                    <label>Frequence par semaine</label>
                    <div class="filter-pair">
                        <input id="frequence_min" type="number" min="1" name="frequence_min" value="<?= esc($filters['frequence_min'] ?? '') ?>" placeholder="Min">
                        <input id="frequence_max" type="number" min="1" name="frequence_max" value="<?= esc($filters['frequence_max'] ?? '') ?>" placeholder="Max">
                    </div>
                </div>
            </div>

            <div class="actions-inline">
                <button type="submit" class="btn btn-primary">Filtrer</button>
                <a href="<?= base_url('admin/activites') ?>" class="btn btn-secondary">Reinitialiser</a>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header-flex">
            <div>
                <h3 class="section-title">Liste des activites</h3>
                <p class="section-subtitle"><?= count($activites ?? []) ?> activite(s) trouvee(s)</p>
            </div>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Activite</th>
                        <th>Frequence</th>
                        <th>Regimes lies</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (! empty($activites)): ?>
                        <?php foreach ($activites as $activite): ?>
                            <tr>
                                <td>
                                    <div class="activity-name">
                                        <strong><?= esc($activite['label_activite']) ?></strong>
                                    </div>
                                </td>
                                <td>
                                    <span class="activity-pill"><?= esc((string) $activite['nb_par_semaine']) ?> fois / semaine</span>
                                </td>
                                <td>
                                    <span class="badge neutral"><?= esc((string) ($activite['nb_regimes'] ?? 0)) ?> regime(s)</span>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="<?= base_url('admin/activites/view/' . $activite['id_activite']) ?>" class="btn btn-ghost btn-icon" title="Voir le detail">
                                            <img src="<?= esc(base_url('assets/icons/eye.svg')) ?>" alt="Voir">
                                        </a>
                                        <a href="<?= base_url('admin/activites/edit/' . $activite['id_activite']) ?>" class="btn btn-secondary btn-icon" title="Modifier l'activite">
                                            <img src="<?= esc(base_url('assets/icons/pencil.svg')) ?>" alt="Modifier">
                                        </a>
                                        <form action="<?= esc(base_url('admin/activites/delete/' . $activite['id_activite'])) ?>" method="post">
                                            <?= csrf_field() ?>
                                            <button
                                                type="submit"
                                                class="btn btn-danger btn-icon"
                                                title="Supprimer l'activite"
                                                data-confirm-message="Supprimer l'activite &quot;<?= esc($activite['label_activite']) ?>&quot; ?"
                                            >
                                                <img src="<?= esc(base_url('assets/icons/trash-2.svg')) ?>" alt="Supprimer">
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">Aucune activite sportive ne correspond aux filtres actuels.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?= $this->endSection() ?>
