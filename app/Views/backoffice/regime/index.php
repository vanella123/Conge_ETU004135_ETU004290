<?= $this->extend('backoffice/layout') ?>

<?php
    $regimeDurations = $regimeDurations ?? [];
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

    $renderCompositionChart = static function (array $regime, int $size = 92) use ($compositionColors, $pointOnCircle): string {
        $segments = [
            ['label' => 'Viande', 'value' => (float) ($regime['pourcentage_viande'] ?? 0), 'color' => $compositionColors['viande']],
            ['label' => 'Poisson', 'value' => (float) ($regime['pourcentage_poisson'] ?? 0), 'color' => $compositionColors['poisson']],
            ['label' => 'Volaille', 'value' => (float) ($regime['pourcentage_volaille'] ?? 0), 'color' => $compositionColors['volaille']],
        ];

        $cx = $size / 2;
        $cy = $size / 2;
        $radius = ($size / 2) - 3;
        $innerRadius = $radius * 0.54;
        $startAngle = -90.0;
        $svg = '<svg viewBox="0 0 ' . $size . ' ' . $size . '" width="' . $size . '" height="' . $size . '" aria-label="Composition du regime">';

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

            $startAngle = $endAngle;
        }

        $svg .= '<circle cx="' . $cx . '" cy="' . $cy . '" r="' . ($innerRadius - 1) . '" fill="#ffffff"></circle>';
        $svg .= '</svg>';

        return $svg;
    };
?>

<?= $this->section('title') ?>Gestion des regimes<?= $this->endSection() ?>
<?= $this->section('head') ?>
    
<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Bibliotheque des regimes<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Vue admin en francais avec composition visuelle, badges de duree et actions rapides.<?= $this->endSection() ?>
<?= $this->section('page_actions') ?>
    <a href="<?= base_url('admin/regimes/create') ?>" class="btn btn-primary">
        <img class="icon" src="<?= esc(base_url('assets/icons/plus.svg')) ?>" alt="">
        <span>Nouveau regime</span>
    </a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="card">
        <h3 class="section-title">Filtres</h3>
        <p class="section-subtitle">Affinez la liste selon le nom, la variation, les durees et les prix disponibles.</p>

        <form method="get" action="<?= base_url('admin/regimes') ?>" class="stack">
            <div class="filters-grid">
                <div class="field">
                    <label for="nom_regime">Nom du regime</label>
                    <input id="nom_regime" type="text" name="nom_regime" value="<?= esc($filters['nom_regime'] ?? '') ?>" placeholder="Rechercher un regime">
                </div>
                <div class="field">
                    <label>Variation mensuelle (kg / mois)</label>
                    <div class="filter-pair">
                        <input id="variation_min" type="number" step="0.01" name="variation_min" value="<?= esc($filters['variation_min'] ?? '') ?>" placeholder="Min">
                        <input id="variation_max" type="number" step="0.01" name="variation_max" value="<?= esc($filters['variation_max'] ?? '') ?>" placeholder="Max">
                    </div>
                </div>
                <div class="field">
                    <label>Durees disponibles (jours)</label>
                    <div class="filter-pair">
                        <input id="duree_min" type="number" name="duree_min" value="<?= esc($filters['duree_min'] ?? '') ?>" placeholder="Min">
                        <input id="duree_max" type="number" name="duree_max" value="<?= esc($filters['duree_max'] ?? '') ?>" placeholder="Max">
                    </div>
                </div>
                <div class="field">
                    <label>Prix disponibles (Ar)</label>
                    <div class="filter-pair">
                        <input id="prix_min" type="number" step="0.01" name="prix_min" value="<?= esc($filters['prix_min'] ?? '') ?>" placeholder="Min">
                        <input id="prix_max" type="number" step="0.01" name="prix_max" value="<?= esc($filters['prix_max'] ?? '') ?>" placeholder="Max">
                    </div>
                </div>
            </div>

            <div class="actions-inline">
                <button type="submit" class="btn btn-primary">Filtrer</button>
                <a href="<?= base_url('admin/regimes') ?>" class="btn btn-secondary">Reinitialiser</a>
            </div>
        </form>
    </div>

    <div class="card">
        <div class="card-header-flex">
            <div>
                <h3 class="section-title">Liste des regimes</h3>
                <p class="section-subtitle"><?= count($regimes ?? []) ?> resultat(s)</p>
            </div>
        </div>

        <div class="composition-legend">
            <span class="legend-chip" title="Viande">
                <span class="legend-dot" style="background:<?= esc($compositionColors['viande']) ?>;"></span>
                Viande
            </span>
            <span class="legend-chip" title="Poisson">
                <span class="legend-dot" style="background:<?= esc($compositionColors['poisson']) ?>;"></span>
                Poisson
            </span>
            <span class="legend-chip" title="Volaille">
                <span class="legend-dot" style="background:<?= esc($compositionColors['volaille']) ?>;"></span>
                Volaille
            </span>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nom du regime</th>
                        <th>Variation mensuelle</th>
                        <th>Composition</th>
                        <th>Nb d'activites liees</th>
                        <th>Durees disponibles</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (! empty($regimes)): ?>
                        <?php foreach ($regimes as $regime): ?>
                            <?php
                                $variation = (float) ($regime['variation_mensuelle_kg'] ?? 0);
                                $durations = $regimeDurations[$regime['id_regime']] ?? [];
                            ?>
                            <tr>
                                <td>
                                    <strong><?= esc($regime['nom_regime']) ?></strong>
                                </td>
                                <td>
                                    <span class="badge <?= $variation < 0 ? 'warn' : 'success' ?>">
                                        <?= $variation > 0 ? '+' : '' ?><?= esc(number_format($variation, 2, ',', ' ')) ?> kg / mois
                                    </span>
                                </td>
                                <td>
                                    <div class="composition-cell">
                                        <div class="composition-chart">
                                            <?= $renderCompositionChart($regime) ?>
                                            <span class="composition-tooltip"></span>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge neutral"><?= esc((string) ($regime['nb_activites'] ?? 0)) ?></span></td>
                                <td>
                                    <?php if ($durations !== []): ?>
                                        <div class="duration-badges">
                                            <?php foreach ($durations as $duration): ?>
                                                <span class="badge neutral"><?= esc((string) $duration) ?> j</span>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <span class="badge neutral">Aucune duree</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="<?= base_url('admin/regimes/view/' . $regime['id_regime']) ?>" class="btn btn-ghost btn-icon icon-action" title="Voir le detail">
                                            <img src="<?= esc(base_url('assets/icons/eye.svg')) ?>" alt="Voir">
                                        </a>
                                        <a href="<?= base_url('admin/regimes/edit/' . $regime['id_regime']) ?>" class="btn btn-secondary btn-icon icon-action" title="Modifier le regime">
                                            <img src="<?= esc(base_url('assets/icons/pencil.svg')) ?>" alt="Modifier">
                                        </a>
                                        <form action="<?= esc(base_url('admin/regimes/delete/' . $regime['id_regime'])) ?>" method="post">
                                            <?= csrf_field() ?>
                                            <button
                                                type="submit"
                                                class="btn btn-danger btn-icon icon-action"
                                                title="Supprimer le regime"
                                                data-confirm-message="Supprimer le regime &quot;<?= esc($regime['nom_regime']) ?>&quot; ?"
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
                            <td colspan="6">Aucun regime ne correspond aux filtres actuels.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?= $this->endSection() ?>

