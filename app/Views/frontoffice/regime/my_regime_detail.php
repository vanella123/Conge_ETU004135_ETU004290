<?= $this->extend('frontoffice/layout') ?>

<?= $this->section('title') ?><?= esc($purchase['nom_regime']) ?> - Mon régime<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="stack">
    <div class="hero">
        <div class="page-header">
            <h1><?= esc($purchase['nom_regime']) ?></h1>
            <p class="sub">Détails complets de votre achat, du programme et de ses activités associées.</p>
        </div>
        <div class="hero-actions">
            <a class="btn" href="<?= esc(site_url('mes-regimes/' . $purchase['id_commande'] . '/export-pdf')) ?>">Exporter PDF</a>
            <a class="btn btn-secondary" href="<?= esc(site_url('mes-regimes')) ?>">Retour</a>
        </div>
    </div>

    <div class="card">
        <div class="section-title">
            <div>
                <h2>Résumé de l’achat</h2>
                <p class="sub">Durée, montant et date d'achat.</p>
            </div>
        </div>
        <div class="metric-grid">
            <div class="metric-card"><div class="metric-label">Durée choisie</div><div class="metric-value"><?= esc($purchase['nb_jours']) ?> jours</div></div>
            <div class="metric-card"><div class="metric-label">Montant payé</div><div class="metric-value"><?= esc(number_format((float) $purchase['montant_paye'], 0, ',', ' ')) ?> Ar</div></div>
            <div class="metric-card"><div class="metric-label">Date d'achat</div><div class="metric-value small"><?= esc(date('d/m/Y', strtotime((string) $purchase['date_achat']))) ?></div></div>
        </div>
    </div>

    <div class="card">
        <div class="section-title">
            <div>
                <h2>Objectif et composition</h2>
                <p class="sub">Objectif choisi, répartition nutritionnelle et tendance estimée.</p>
            </div>
        </div>
        <div class="grid">
            <div>
                <div class="kv-title">Objectif</div>
                <div class="kv-value"><?= esc($purchase['objective_label']) ?></div>
            </div>
            <div>
                <div class="kv-title">Composition</div>
                <div class="composition-block">
                    <div class="composition-chart composition-large" style="--pie-gradients: <?= esc($purchase['composition_gradient'] ?? '#e9eef3 0% 100%') ?>" data-tooltip="<?= esc($purchase['composition_tooltip'] ?? '') ?>">
                        <span class="composition-tooltip"></span>
                    </div>
                    <div class="composition-legend-inline">
                        <?php foreach ($purchase['composition_legend'] ?? [] as $legend): ?>
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
        <div class="section-title">
            <div>
                <h2>Tendance du poids</h2>
                <p class="sub">Projection sur 0, 30, 60 et 90 jours.</p>
            </div>
        </div>
        <?php $graph = $weightGraph ?? []; ?>
        <div class="trend-chart">
            <svg viewBox="0 0 <?= esc((string) ($graph['width'] ?? 640)) ?> <?= esc((string) ($graph['height'] ?? 280)) ?>" width="<?= esc((string) ($graph['width'] ?? 640)) ?>" height="<?= esc((string) ($graph['height'] ?? 280)) ?>" role="img" aria-label="Graphe de variation du poids">
                <?php
                    $padLeft = $graph['padLeft'] ?? 54;
                    $padRight = $graph['padRight'] ?? 24;
                    $padTop = $graph['padTop'] ?? 24;
                    $padBottom = $graph['padBottom'] ?? 58;
                    $plotHeight = $graph['plotHeight'] ?? 0;
                    $plotWidth = $graph['plotWidth'] ?? 0;
                    $minValue = $graph['minValue'] ?? 0;
                    $range = $graph['range'] ?? 1;
                ?>
                <?php for ($i = 0; $i <= 4; $i++): ?>
                    <?php
                        $yValue = $minValue + (($range / 4) * $i);
                        $y = $padTop + ($plotHeight - (($yValue - $minValue) / $range) * $plotHeight);
                    ?>
                    <line x1="<?= $padLeft ?>" y1="<?= $y ?>" x2="<?= ($graph['width'] ?? 640) - $padRight ?>" y2="<?= $y ?>" stroke="#d9e2ec" stroke-dasharray="4 6"></line>
                    <text x="<?= $padLeft - 10 ?>" y="<?= $y + 4 ?>" text-anchor="end" font-size="12" fill="#61758a"><?= esc(number_format($yValue, 1, ',', ' ')) ?></text>
                <?php endfor; ?>

                <line x1="<?= $padLeft ?>" y1="<?= $padTop ?>" x2="<?= $padLeft ?>" y2="<?= ($graph['height'] ?? 280) - $padBottom ?>" stroke="#94a3b8"></line>
                <line x1="<?= $padLeft ?>" y1="<?= ($graph['height'] ?? 280) - $padBottom ?>" x2="<?= ($graph['width'] ?? 640) - $padRight ?>" y2="<?= ($graph['height'] ?? 280) - $padBottom ?>" stroke="#94a3b8"></line>

                <?php foreach ([0, 30, 60, 90] as $daysMark): ?>
                    <?php $x = $padLeft + (($daysMark / 90) * $plotWidth); ?>
                    <line x1="<?= $x ?>" y1="<?= ($graph['height'] ?? 280) - $padBottom ?>" x2="<?= $x ?>" y2="<?= ($graph['height'] ?? 280) - $padBottom + 6 ?>" stroke="#94a3b8"></line>
                    <text x="<?= $x ?>" y="<?= ($graph['height'] ?? 280) - 24 ?>" text-anchor="middle" font-size="12" fill="#61758a"><?= esc((string) $daysMark) ?></text>
                <?php endforeach; ?>

                <polyline
                    fill="none"
                    stroke="#1f8f6a"
                    stroke-width="4"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    points="<?php foreach (($graph['linePoints'] ?? []) as $point): ?><?= $point['x'] ?>,<?= $point['y'] ?> <?php endforeach; ?>"
                ></polyline>

                <?php foreach (($graph['linePoints'] ?? []) as $point): ?>
                    <circle cx="<?= $point['x'] ?>" cy="<?= $point['y'] ?>" r="6" fill="#1f8f6a">
                        <title><?= esc((string) $point['days']) ?> jours : <?= $point['value'] > 0 ? '+' : '' ?><?= esc(number_format($point['value'], 2, ',', ' ')) ?> kg</title>
                    </circle>
                <?php endforeach; ?>

                <text x="<?= $padLeft ?>" y="14" font-size="12" fill="#61758a">Kg</text>
                <text x="<?= ($graph['width'] ?? 640) / 2 ?>" y="<?= ($graph['height'] ?? 280) - 10 ?>" text-anchor="middle" font-size="12" fill="#61758a">Jours</text>
            </svg>
        </div>
    </div>

    <div class="card">
        <div class="section-title">
            <div>
                <h2>Activités recommandées</h2>
                <p class="sub">Les activités les plus cohérentes avec ce régime.</p>
            </div>
        </div>
        <?php if (empty($activites)) : ?>
            <div class="empty">Aucune activité associée à ce régime.</div>
        <?php else : ?>
            <div class="table-wrap">
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
                                <td><?= esc($activite['label_activite']) ?></td>
                                <td><?= esc($activite['nb_par_semaine']) ?>x/semaine</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>
<?= $this->endSection() ?>
