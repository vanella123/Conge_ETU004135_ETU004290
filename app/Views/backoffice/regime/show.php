<?= $this->extend('backoffice/layout') ?>

<?php
    $variation = (float) ($regime['variation_mensuelle_kg'] ?? 0);
    $compositionColors = [
        'viande' => '#ef4444',
        'poisson' => '#3b82f6',
        'volaille' => '#f59e0b',
    ];

    $pointOnCircle = static function (float $angle, float $radius, float $cx, float $cy): array {
        $radians = deg2rad($angle);

        return [
            'x' => $cx + cos($radians) * $radius,
            'y' => $cy + sin($radians) * $radius,
        ];
    };

    $renderCompositionChart = static function (array $regime, int $size = 250) use ($compositionColors, $pointOnCircle): string {
        $segments = [
            ['label' => 'Viande', 'value' => (float) ($regime['pourcentage_viande'] ?? 0), 'color' => $compositionColors['viande']],
            ['label' => 'Poisson', 'value' => (float) ($regime['pourcentage_poisson'] ?? 0), 'color' => $compositionColors['poisson']],
            ['label' => 'Volaille', 'value' => (float) ($regime['pourcentage_volaille'] ?? 0), 'color' => $compositionColors['volaille']],
        ];

        $cx = $size / 2;
        $cy = $size / 2;
        $radius = ($size / 2) - 5;
        $innerRadius = $radius * 0.56;
        $startAngle = -90.0;
        $svg = '<svg viewBox="0 0 ' . $size . ' ' . $size . '" width="' . $size . '" height="' . $size . '" aria-label="Composition nutritionnelle">';

        foreach ($segments as $segment) {
            if ($segment['value'] <= 0) {
                continue;
            }

            $sweep = ($segment['value'] / 100) * 360;
            $endAngle = $startAngle + $sweep;
            $largeArc = $sweep > 180 ? 1 : 0;

            if ($segment['value'] >= 100) {
                $svg .= '<circle cx="' . $cx . '" cy="' . $cy . '" r="' . $radius . '" fill="' . $segment['color'] . '" data-tooltip="'
                    . esc($segment['label']) . ' : ' . esc(number_format($segment['value'], 0, ',', ' ')) . '%"></circle>';
                // Ajouter le label au centre
                $svg .= '<text x="' . $cx . '" y="' . ($cy + 8) . '" text-anchor="middle" font-size="18" font-weight="bold" fill="#ffffff">'
                    . esc(number_format($segment['value'], 0, ',', ' ')) . '%</text>';
                break;
            }

            $outerStart = $pointOnCircle($startAngle, $radius, $cx, $cy);
            $outerEnd = $pointOnCircle($endAngle, $radius, $cx, $cy);
            $innerStart = $pointOnCircle($startAngle, $innerRadius, $cx, $cy);
            $innerEnd = $pointOnCircle($endAngle, $innerRadius, $cx, $cy);

            $path = sprintf(
                'M %.3F %.3F A %.3F %.3F 0 %d 1 %.3F %.3F L %.3F %.3F A %.3F %.3F 0 %d 0 %.3F %.3F Z',
                $outerStart['x'],
                $outerStart['y'],
                $radius,
                $radius,
                $largeArc,
                $outerEnd['x'],
                $outerEnd['y'],
                $innerEnd['x'],
                $innerEnd['y'],
                $innerRadius,
                $innerRadius,
                $largeArc,
                $innerStart['x'],
                $innerStart['y']
            );

            $svg .= '<path d="' . $path . '" fill="' . $segment['color'] . '" data-tooltip="'
                . esc($segment['label']) . ' : ' . esc(number_format($segment['value'], 0, ',', ' ')) . '%"></path>';

            // Ajouter le label du pourcentage au milieu du segment
            if ($segment['value'] > 8) { // Afficher seulement si le segment est assez grand
                $midAngle = $startAngle + ($sweep / 2);
                $labelRadius = $innerRadius + ($radius - $innerRadius) / 2;
                $labelPoint = $pointOnCircle($midAngle, $labelRadius, $cx, $cy);
                $svg .= '<text x="' . $labelPoint['x'] . '" y="' . ($labelPoint['y'] + 5) . '" text-anchor="middle" font-size="13" font-weight="600" fill="#ffffff" pointer-events="none">'
                    . esc(number_format($segment['value'], 0, ',', ' ')) . '%</text>';
            }

            $startAngle = $endAngle;
        }

        $svg .= '<circle cx="' . $cx . '" cy="' . $cy . '" r="' . ($innerRadius - 1) . '" fill="#ffffff"></circle>';
        $svg .= '</svg>';

        return $svg;
    };

    $points = $estimates ?? [];
    $graphWidth = 640;
    $graphHeight = 280;
    $padLeft = 54;
    $padRight = 24;
    $padTop = 24;
    $padBottom = 58;
    $plotWidth = $graphWidth - $padLeft - $padRight;
    $plotHeight = $graphHeight - $padTop - $padBottom;
    $values = array_map(static fn (array $point): float => (float) ($point['value'] ?? 0), $points);
    $minValue = min(array_merge([0], $values));
    $maxValue = max(array_merge([0], $values));
    if ($minValue === $maxValue) {
        $minValue -= 1;
        $maxValue += 1;
    }
    $range = $maxValue - $minValue;
    $linePoints = [];
    foreach ($points as $index => $point) {
        $days = (float) ($point['days'] ?? 0);
        $x = $padLeft + (($days / 90) * $plotWidth);
        $y = $padTop + (($maxValue - (float) $point['value']) / $range) * $plotHeight;
        $linePoints[] = ['x' => $x, 'y' => $y, 'days' => $days, 'value' => (float) $point['value']];
    }
?>

<?= $this->section('title') ?>Detail du regime<?= $this->endSection() ?>
<?= $this->section('head') ?>
    
<?= $this->endSection() ?>
<?= $this->section('page_title') ?><?= esc($regime['nom_regime'] ?? 'Detail du regime') ?><?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Vue detaillee en lecture seule avec composition graphique, effet sur le poids et offres disponibles.<?= $this->endSection() ?>
<?= $this->section('page_actions') ?>
    <a href="<?= base_url('admin/regimes') ?>" class="btn btn-secondary">Retour a la liste</a>
    <a href="<?= base_url('admin/regimes/edit/' . $regime['id_regime']) ?>" class="btn btn-primary">Modifier</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="hero-badges">
        <span class="badge <?= $variation < 0 ? 'warn' : 'success' ?>">
            Variation: <?= $variation > 0 ? '+' : '' ?><?= esc(number_format($variation, 2, ',', ' ')) ?> kg / mois
        </span>
    </div>

    <div class="card">
        <h3 class="section-title">Nutrition</h3>
        <p class="section-subtitle">La composition est lisible sur un graphe circulaire et chaque couleur correspond a un type d'aliment.</p>

        <div class="detail-grid">
            <div class="composition-chart">
                <?= $renderCompositionChart($regime) ?>
                <span class="composition-tooltip"></span>
            </div>
            <div class="legend-list">
                <div class="legend-row" title="Viande : <?= esc(number_format((float) $regime['pourcentage_viande'], 2, ',', ' ')) ?>%">
                    <span class="legend-name">
                        <span class="legend-dot" style="background:<?= esc($compositionColors['viande']) ?>;"></span>
                        Viande
                    </span>
                    <strong><?= esc(number_format((float) $regime['pourcentage_viande'], 2, ',', ' ')) ?>%</strong>
                </div>
                <div class="legend-row" title="Poisson : <?= esc(number_format((float) $regime['pourcentage_poisson'], 2, ',', ' ')) ?>%">
                    <span class="legend-name">
                        <span class="legend-dot" style="background:<?= esc($compositionColors['poisson']) ?>;"></span>
                        Poisson
                    </span>
                    <strong><?= esc(number_format((float) $regime['pourcentage_poisson'], 2, ',', ' ')) ?>%</strong>
                </div>
                <div class="legend-row" title="Volaille : <?= esc(number_format((float) $regime['pourcentage_volaille'], 2, ',', ' ')) ?>%">
                    <span class="legend-name">
                        <span class="legend-dot" style="background:<?= esc($compositionColors['volaille']) ?>;"></span>
                        Volaille
                    </span>
                    <strong><?= esc(number_format((float) $regime['pourcentage_volaille'], 2, ',', ' ')) ?>%</strong>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <h3 class="section-title">Effet sur le poids</h3>
        <p class="section-subtitle">Projection lineaire de la variation declaree sur 0, 30, 60 et 90 jours.</p>

        <div class="chart-shell">
            <svg viewBox="0 0 <?= $graphWidth ?> <?= $graphHeight ?>" width="<?= $graphWidth ?>" height="<?= $graphHeight ?>" role="img" aria-label="Graphe de variation du poids">
                <?php for ($i = 0; $i <= 4; $i++): ?>
                    <?php
                        $yValue = $minValue + (($range / 4) * $i);
                        $y = $padTop + ($plotHeight - (($yValue - $minValue) / $range) * $plotHeight);
                    ?>
                    <line x1="<?= $padLeft ?>" y1="<?= $y ?>" x2="<?= $graphWidth - $padRight ?>" y2="<?= $y ?>" stroke="#d9e2ec" stroke-dasharray="4 6"></line>
                    <text x="<?= $padLeft - 10 ?>" y="<?= $y + 4 ?>" text-anchor="end" font-size="12" fill="#61758a"><?= esc(number_format($yValue, 1, ',', ' ')) ?></text>
                <?php endfor; ?>

                <line x1="<?= $padLeft ?>" y1="<?= $padTop ?>" x2="<?= $padLeft ?>" y2="<?= $graphHeight - $padBottom ?>" stroke="#94a3b8"></line>
                <line x1="<?= $padLeft ?>" y1="<?= $graphHeight - $padBottom ?>" x2="<?= $graphWidth - $padRight ?>" y2="<?= $graphHeight - $padBottom ?>" stroke="#94a3b8"></line>

                <?php foreach ([0, 30, 60, 90] as $daysMark): ?>
                    <?php $x = $padLeft + (($daysMark / 90) * $plotWidth); ?>
                    <line x1="<?= $x ?>" y1="<?= $graphHeight - $padBottom ?>" x2="<?= $x ?>" y2="<?= $graphHeight - $padBottom + 6 ?>" stroke="#94a3b8"></line>
                    <text x="<?= $x ?>" y="<?= $graphHeight - 24 ?>" text-anchor="middle" font-size="12" fill="#61758a"><?= esc((string) $daysMark) ?></text>
                <?php endforeach; ?>

                <polyline
                    fill="none"
                    stroke="#1f8f6a"
                    stroke-width="4"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    points="<?php foreach ($linePoints as $point): ?><?= $point['x'] ?>,<?= $point['y'] ?> <?php endforeach; ?>"
                ></polyline>

                <?php foreach ($linePoints as $point): ?>
                    <circle cx="<?= $point['x'] ?>" cy="<?= $point['y'] ?>" r="6" fill="#1f8f6a">
                        <title><?= esc((string) $point['days']) ?> jours : <?= $point['value'] > 0 ? '+' : '' ?><?= esc(number_format($point['value'], 2, ',', ' ')) ?> kg</title>
                    </circle>
                <?php endforeach; ?>

                <text x="<?= $padLeft ?>" y="14" font-size="12" fill="#61758a">Kg</text>
                <text x="<?= $graphWidth / 2 ?>" y="<?= $graphHeight - 10 ?>" text-anchor="middle" font-size="12" fill="#61758a">Jours</text>
            </svg>
        </div>

    </div>

    <div class="card">
        <h3 class="section-title">Activites associees</h3>
        <p class="section-subtitle">Liste des sports relies a ce regime.</p>

        <?php if (! empty($regime['activities'])): ?>
            <div class="list-inline">
                <?php foreach ($regime['activities'] as $activity): ?>
                    <span class="pill">
                        <img src="<?= esc(base_url('assets/icons/activity.svg')) ?>" alt="">
                        <strong><?= esc($activity['label_activite']) ?></strong>
                        <span><?= esc((string) $activity['nb_par_semaine']) ?> fois / semaine</span>
                    </span>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="hint">Aucune activite sportive n'est liee a ce regime.</p>
        <?php endif; ?>
    </div>


    <div class="card">
        <h3 class="section-title">Durees disponibles</h3>
        <p class="section-subtitle">Vue commerciale des durees et de leurs prix.</p>

        <?php if (! empty($regime['durations'])): ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nb jours</th>
                            <th>Prix</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($regime['durations'] as $duration): ?>
                            <tr>
                                <td><?= esc((string) $duration['nb_jours']) ?> jours</td>
                                <td><?= esc(number_format((float) $duration['prix'], 0, ',', ' ')) ?> Ar</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="hint">Aucune duree n'est configuree pour ce regime.</p>
        <?php endif; ?>
    </div>
<?= $this->endSection() ?>

