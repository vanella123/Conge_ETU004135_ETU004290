<?= $this->extend('frontoffice/layout') ?>

<?= $this->section('title') ?><?= esc($regime['nom_regime']) ?> - Détails<?= $this->endSection() ?>

<?= $this->section('head') ?>

<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $isGold = ! empty($user['is_gold']);
    $discountPercent = (float) ($discountPercent ?? 0);
    $formatPrice = static function (float $price): string {
        return number_format($price, 0, ',', ' ');
    };
    $getFinalPrice = static function (float $price, float $discountPercent): float {
        return $discountPercent > 0 ? round($price * (1 - ($discountPercent / 100)), 2) : $price;
    };
?>
<section class="stack">
    <div class="page-header">
        <h1><?= esc($regime['nom_regime']) ?> <span class="badge"><?= esc($regime['variation_label']) ?></span></h1>
        <p class="sub">Détails complets du régime, composition et compatibilité avec votre objectif.</p>
    </div>

        <div class="card">
            <div class="grid">
                <div>
                    <div class="kv-title">Variation de poids</div>
                    <div class="kv-value"><?= esc($regime['variation_label']) ?></div>
                </div>
                <div>
                    <div class="kv-title">Objectif</div>
                    <div class="kv-value"><?= esc($objectiveLabel) ?></div>
                </div>
                <div>
                    <div class="kv-title">Composition</div>
                    <div class="composition-block">
                            <div class="composition-chart composition-large" style="--pie-gradients: <?= esc($regime['composition_gradient'] ?? '#e9eef3 0% 100%') ?>" data-tooltip="<?= esc($regime['composition_tooltip'] ?? '') ?>">
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
                </div>
            </div>
        </div>

        <div class="card">
            <h2>Activités recommandées</h2>
            <?php if (empty($activites)) : ?>
                <div class="empty">Aucune activité associée à ce régime.</div>
            <?php else : ?>
                <table>
                    <thead>
                        <tr>
                            <th>Activité</th>
                            <th>Fréquence</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activites as $activite) : ?>
                            <tr>
                                <td>
                                    <span class="activity-name">
                                        <img class="icon" src="<?= esc(base_url('assets/icons/activity.svg')) ?>" alt="">
                                        <strong><?= esc($activite['label_activite']) ?></strong>
                                    </span>
                                </td>
                                <td><?= esc($activite['nb_par_semaine']) ?>x/semaine</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="card">
            <h2>Commander</h2>
            <?php if (empty($durees)) : ?>
                <div class="empty">Aucune durée disponible pour ce régime.</div>
            <?php else : ?>
                <form id="commande-form" method="post" action="<?= esc(site_url('regimes/purchase/' . $regime['id_regime'])) ?>" data-ajax-form="true">
                    <?php foreach ($durees as $index => $duree) : ?>
                        <?php $prixFinal = $getFinalPrice((float) $duree['prix'], $discountPercent); ?>
                        <label class="option-card">
                            <div class="option-header">
                                <input
                                    type="radio"
                                    name="id_duree_regime"
                                    value="<?= esc($duree['id_duree_regime']) ?>"
                                    data-days="<?= esc($duree['nb_jours']) ?>"
                                    data-price="<?= esc($prixFinal) ?>"
                                    <?= $index === 0 ? 'checked' : '' ?>
                                >
                                <?= esc($duree['nb_jours']) ?> jours
                            </div>
                            <div class="option-meta">
                                → <?= esc($formatPrice($prixFinal)) ?> Ar
                                <?php if ($isGold): ?>
                                    <span class="price-old">
                                        <?= esc($formatPrice((float) $duree['prix'])) ?> Ar
                                    </span>
                                    <span class="badge badge-success">-<?= esc(rtrim(rtrim(number_format($discountPercent, 2, ',', ' '), '0'), ',')) ?>%</span>
                                <?php endif; ?>
                            </div>
                            <div class="option-meta">→ Résultat estimé: <span class="estimate" data-days="<?= esc($duree['nb_jours']) ?>"></span></div>
                            <?php if (! empty($duree['objective_status'])): ?>
                                <div class="option-meta">
                                    <span class="badge badge-<?= esc($duree['objective_status']['tone']) ?>">
                                        <?= esc($duree['objective_status']['label']) ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </label>
                    <?php endforeach; ?>
                    <div class="field-error" data-field-error="id_duree_regime"></div>
                    <div class="form-feedback" data-form-feedback></div>
                    <div>
                        <button class="btn" type="submit" data-confirm-message="Confirmer cet achat de régime ?">Commander</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
</section>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    window.regimeData = <?= json_encode([
        'variationMonthly' => (float) ($regime['variation_mensuelle_kg'] ?? 0),
    ]) ?>;
</script>
<script src="<?= base_url('assets/js/regime.js') ?>"></script>
<?= $this->endSection() ?>
