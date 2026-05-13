<?= $this->extend('frontoffice/layout') ?>

<?= $this->section('title') ?>Régimes<?= $this->endSection() ?>

<?= $this->section('head') ?>

<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="stack">
    <div class="hero">
        <div class="page-header">
            <h1>Régimes</h1>
            <p class="sub">Explorez les formules disponibles, filtrez par durée ou objectif, et visualisez rapidement les durées et activités associées.</p>
        </div>
    </div>

    <div class="card">
        <div class="section-title">
            <div>
                <h2>Filtres</h2>
                <p class="sub">Affinez la liste selon votre besoin.</p>
            </div>
        </div>
        <form method="get" class="filters" id="filters">
                <div class="filter-group">
                    <label for="duree">Durée disponible</label>
                    <select name="duree" id="duree">
                        <option value="">Toutes les durées</option>
                        <?php foreach ($dureeOptions as $option) : ?>
                            <option value="<?= esc($option) ?>" <?= ($selectedDuree === $option) ? 'selected' : '' ?>>
                                <?= esc($option) ?> jours
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Objectif</label>
                    <div class="radio-group">
                        <?php foreach ($objectifOptions as $objectif) : ?>
                            <label class="radio-item">
                                <input type="radio" name="objectif" value="<?= esc($objectif['id_objectif']) ?>" <?= ($selectedObjectif === (int) $objectif['id_objectif']) ? 'checked' : '' ?>>
                                <?= esc($objectif['label_objectif']) ?>
                            </label>
                        <?php endforeach; ?>
                        <label class="radio-item">
                            <input type="radio" name="objectif" value="" <?= empty($selectedObjectif) ? 'checked' : '' ?>>
                            Tous
                        </label>
                    </div>
                </div>
        </form>
        <?php if (empty($regimes)) : ?>
            <div class="empty">Aucun régime disponible.</div>
        <?php else : ?>
            <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Variation estimée</th>
                        <th>Composition</th>
                        <th>Durées</th>
                        <th>Nb activités</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="regime-rows">
                    <?php foreach ($regimes as $regime) : ?>
                        <?php $durees = $regimeDurees[$regime['id_regime']] ?? []; ?>
                        <tr>
                            <td>
                                <span class="regime-name"><?= esc($regime['nom_regime']) ?></span>
                            </td>
                            <td><span class="badge"><?= esc($regime['variation_label']) ?></span></td>
                            <td>
                                <div class="composition-wrap">
                                    <div class="composition-chart composition-mini" style="--pie-gradients: <?= esc($regime['composition_gradient'] ?? '#e9eef3 0% 100%') ?>" data-tooltip="<?= esc($regime['composition_tooltip'] ?? '') ?>">
                                        <span class="composition-tooltip"></span>
                                    </div>
                                    <div class="composition-legend-inline">
                                        <?php foreach ($regime['composition_legend'] ?? [] as $legend): ?>
                                            <span class="composition-legend-item">
                                                <span class="legend-dot" style="background: <?= esc($legend['color']) ?>;"></span>
                                                <?= esc($legend['label']) ?> <?= esc($legend['value_label']) ?>%
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if (empty($durees)) : ?>
                                    <span class="badge badge-muted">Aucune</span>
                                <?php else : ?>
                                    <div class="badge-group">
                                        <?php foreach ($durees as $duree) : ?>
                                            <span class="badge badge-muted"><?= esc($duree) ?> j</span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge badge-muted"><?= esc($regime['activity_count']) ?></span></td>
                            <td>
                                <a href="<?= site_url('/regimes/' . $regime['id_regime']) ?>" class="btn btn-ghost btn-icon" title="Voir le détail">
                                    <img src="<?= esc(base_url('assets/icons/eye.svg')) ?>" alt="Voir">
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        <?php endif; ?>
    </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.regimeListData = <?= json_encode([
        'detailBase' => rtrim(site_url('regimes'), '/'),
        'eyeIcon' => base_url('assets/icons/eye.svg'),
    ]) ?>;
</script>
<script src="<?= base_url('assets/js/regime.js') ?>"></script>
    <?= $this->endSection() ?>
