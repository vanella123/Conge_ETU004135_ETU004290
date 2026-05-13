<?= $this->extend('backoffice/layout') ?>

<?php
    $selectedActivities = is_array($selectedActivities ?? null) ? $selectedActivities : [];
    $selectedActivities = array_map('strval', $selectedActivities);
    $durationRows = $durationRows ?? [];
    $lockedDurationIds = array_map('intval', $lockedDurationIds ?? []);

    if ($durationRows === []) {
        $durationRows = [['id_duree_regime' => 0, 'nb_jours' => '', 'prix' => '']];
    }

    $validationErrors = [];
    if (! empty($validation)) {
        $validationErrors = array_values($validation->getErrors());
    }

    $formErrors = array_values($formErrors ?? []);
    $allErrors = array_values(array_unique(array_merge($validationErrors, $formErrors)));
?>

<?= $this->section('title') ?><?= esc($title ?? 'Formulaire regime') ?><?= $this->endSection() ?>
<?= $this->section('head') ?>
    
<?= $this->endSection() ?>
<?= $this->section('page_title') ?><?= esc($title ?? 'Formulaire regime') ?><?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Gerez le regime, les activites sportives et les lignes duree + prix dans un seul formulaire.<?= $this->endSection() ?>
<?= $this->section('page_actions') ?>
    <a href="<?= base_url('admin/regimes') ?>" class="btn btn-secondary">Retour a la liste</a>
    <?php if (! empty($regime['id_regime'])): ?>
        <a href="<?= base_url('admin/regimes/view/' . $regime['id_regime']) ?>" class="btn btn-secondary">Voir le detail</a>
    <?php endif; ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <?php if ($allErrors !== []): ?>
        <div class="card" class="card-error">
            <h3 class="section-title">Verifications du formulaire</h3>
            <p class="section-subtitle">Certaines informations doivent etre corrigees avant l'enregistrement.</p>
            <ul class="error-list">
                <?php foreach ($allErrors as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="<?= esc($action) ?>" method="post" class="stack">
        <?= csrf_field() ?>

        <div class="card">
            <h3 class="section-title">Informations principales</h3>
            <p class="section-subtitle">Definissez le nom du regime et sa variation mensuelle de poids.</p>

            <div class="grid-2">
                <div class="field">
                    <label for="nom_regime">Nom du regime</label>
                    <input id="nom_regime" type="text" name="nom_regime" value="<?= esc(old('nom_regime', $regime['nom_regime'] ?? '')) ?>" required>
                </div>
                <div class="field">
                    <label for="variation_mensuelle_kg">Variation mensuelle (kg / mois)</label>
                    <input id="variation_mensuelle_kg" type="number" step="0.01" name="variation_mensuelle_kg" value="<?= esc(old('variation_mensuelle_kg', $regime['variation_mensuelle_kg'] ?? '')) ?>" required>
                    <p class="hint">Valeur negative pour une perte de poids, positive pour une prise de poids.</p>
                </div>
            </div>
        </div>

        <div class="card">
            <h3 class="section-title">Composition nutritionnelle</h3>
            <p class="section-subtitle">Les pourcentages viande, poisson et volaille doivent totaliser 100%.</p>

            <div class="grid-3">
                <div class="field">
                    <label for="pourcentage_viande">% viande</label>
                    <input id="pourcentage_viande" type="number" step="0.01" min="0" max="100" name="pourcentage_viande" value="<?= esc(old('pourcentage_viande', $regime['pourcentage_viande'] ?? '0')) ?>" required>
                </div>
                <div class="field">
                    <label for="pourcentage_poisson">% poisson</label>
                    <input id="pourcentage_poisson" type="number" step="0.01" min="0" max="100" name="pourcentage_poisson" value="<?= esc(old('pourcentage_poisson', $regime['pourcentage_poisson'] ?? '0')) ?>" required>
                </div>
                <div class="field">
                    <label for="pourcentage_volaille">% volaille</label>
                    <input id="pourcentage_volaille" type="number" step="0.01" min="0" max="100" name="pourcentage_volaille" value="<?= esc(old('pourcentage_volaille', $regime['pourcentage_volaille'] ?? '0')) ?>" required>
                </div>
            </div>
        </div>

        <div class="card">
            <h3 class="section-title">Activites sportives</h3>
            <p class="section-subtitle">Conservez cette selection intelligente, avec un etat selectionne plus visible en vert.</p>

            <?php if (! empty($activities)): ?>
                <div class="choice-grid">
                    <?php foreach ($activities as $activity): ?>
                        <?php $activityId = (string) $activity['id_activite']; ?>
                        <label class="choice">
                            <input type="checkbox" name="activites[]" value="<?= esc($activityId) ?>" <?= in_array($activityId, $selectedActivities, true) ? 'checked' : '' ?>>
                            <span class="choice-card">
                                <strong class="choice-title"><?= esc($activity['label_activite']) ?></strong>
                                <span class="choice-meta">
                                    <strong><?= esc((string) $activity['nb_par_semaine']) ?> fois par semaine</strong>
                                </span>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="hint">Aucune activite sportive n'est encore disponible.</p>
            <?php endif; ?>
        </div>

        <div class="card">
            <div class="card-header-flex">
                <div>
                    <h3 class="section-title">Durees et prix</h3>
                    <p class="section-subtitle">Ajoutez plusieurs lignes pour configurer toutes les offres du regime.</p>
                </div>
                <button type="button" class="btn btn-secondary" id="add-duration-row">Ajouter une ligne</button>
            </div>

            <div class="table-wrap">
                <table id="duration-table">
                    <thead>
                        <tr>
                            <th>Nb jours</th>
                            <th>Prix (Ar)</th>
                            <th>Etat</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($durationRows as $index => $row): ?>
                            <?php
                                $durationId = (int) ($row['id_duree_regime'] ?? 0);
                                $isLocked = in_array($durationId, $lockedDurationIds, true);
                            ?>
                            <tr>
                                <td>
                                    <input type="hidden" name="durees[<?= $index ?>][id_duree_regime]" value="<?= esc((string) $durationId) ?>">
                                    <div class="field">
                                        <input type="number" min="1" name="durees[<?= $index ?>][nb_jours]" value="<?= esc((string) ($row['nb_jours'] ?? '')) ?>" <?= $isLocked ? 'readonly' : '' ?> required>
                                    </div>
                                </td>
                                <td>
                                    <div class="field">
                                        <input type="number" min="0.01" step="0.01" name="durees[<?= $index ?>][prix]" value="<?= esc((string) ($row['prix'] ?? '')) ?>" <?= $isLocked ? 'readonly' : '' ?> required>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($isLocked): ?>
                                        <div class="duration-status">Deja utilisee</div>
                                    <?php else: ?>
                                        <div class="duration-status success">Libre</div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-danger btn-small remove-duration-row" <?= $isLocked ? 'disabled' : '' ?>>
                                        <?= $isLocked ? 'Suppression bloquee' : 'Retirer' ?>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if ($lockedDurationIds !== []): ?>
                <p class="hint">Les durees deja utilisees dans des commandes ou historisees ne peuvent pas etre supprimees depuis cet ecran.</p>
            <?php endif; ?>
        </div>

        <div class="actions-inline">
            <a href="<?= base_url('admin/regimes') ?>" class="btn btn-secondary">Annuler</a>
            <button type="submit" class="btn btn-primary">Enregistrer le regime</button>
        </div>
    </form>

    <template id="duration-row-template">
        <tr>
            <td>
                <input type="hidden" name="__NAME_ID__" value="0">
                <div class="field">
                    <input type="number" min="1" name="__NAME_DAYS__" value="" required>
                </div>
            </td>
            <td>
                <div class="field">
                    <input type="number" min="0.01" step="0.01" name="__NAME_PRICE__" value="" required>
                </div>
            </td>
            <td>
                <div class="duration-status success">Nouvelle ligne</div>
            </td>
            <td>
                <button type="button" class="btn btn-danger btn-small remove-duration-row">Retirer</button>
            </td>
        </tr>
    </template>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script>
        (function () {
            const tableBody = document.querySelector('#duration-table tbody');
            const addButton = document.getElementById('add-duration-row');
            const template = document.getElementById('duration-row-template');
            let nextRowIndex = tableBody ? tableBody.querySelectorAll('tr').length : 0;

            function updateRemoveButtons() {
                const rows = tableBody.querySelectorAll('tr');
                rows.forEach((row) => {
                    const button = row.querySelector('.remove-duration-row');
                    if (!button) {
                        return;
                    }

                    if (button.disabled) {
                        return;
                    }

                    button.disabled = rows.length === 1;
                });
            }

            function addRow() {
                const index = nextRowIndex;
                nextRowIndex += 1;

                const html = template.innerHTML
                    .replace('__NAME_ID__', 'durees[' + index + '][id_duree_regime]')
                    .replace('__NAME_DAYS__', 'durees[' + index + '][nb_jours]')
                    .replace('__NAME_PRICE__', 'durees[' + index + '][prix]');

                const wrapper = document.createElement('tbody');
                wrapper.innerHTML = html.trim();
                tableBody.appendChild(wrapper.firstElementChild);
                updateRemoveButtons();
            }

            addButton?.addEventListener('click', addRow);

            tableBody?.addEventListener('click', function (event) {
                const button = event.target.closest('.remove-duration-row');
                if (!button || button.disabled) {
                    return;
                }

                const rows = tableBody.querySelectorAll('tr');
                if (rows.length === 1) {
                    return;
                }

                button.closest('tr')?.remove();
                updateRemoveButtons();
            });

            updateRemoveButtons();
        }());
    </script>
<?= $this->endSection() ?>
