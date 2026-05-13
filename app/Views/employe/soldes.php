<?php $this->extend('layout'); ?>

<?php $this->section('content'); ?>

<div class="data-card">
    <div class="data-card-head">
        <h3><i class="bi bi-pie-chart-fill"></i> Soldes de Congés Restants</h3>
    </div>
    <div style="padding: 1.25rem;">
        <?php foreach($soldes ?? [] as $solde): ?>
        <div class="solde-card">
            <div class="solde-header">
                <div>
                    <div class="solde-type"><?= esc($solde['type_nom']) ?></div>
                    <div class="td-muted" style="font-size: 0.75rem; margin-top: 4px;">
                        Année: <?= $solde['annee'] ?? date('Y') ?>
                    </div>
                </div>
                <span class="solde-nums">
                    <strong><?= round($solde['jours_pris'], 1) ?></strong> / 
                    <?= round($solde['jours_total'], 1) ?>
                </span>
            </div>
            <div class="solde-bar">
                <div class="solde-fill <?= $solde['jours_pris'] > ($solde['jours_total'] * 0.8) ? 'danger' : ($solde['jours_pris'] > ($solde['jours_total'] * 0.5) ? 'warn' : '') ?>" 
                     style="width: <?= min(100, ($solde['jours_pris'] / $solde['jours_total']) * 100) ?>%"></div>
            </div>
            <div style="display: flex; justify-content: space-between; margin-top: 0.75rem; font-size: 0.75rem; color: var(--muted);">
                <span><strong><?= round($solde['jours_total'] - $solde['jours_pris'], 1) ?></strong> jour(s) restant(s)</span>
                <span>Utilisés: <?= round($solde['jours_pris'], 1) ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php $this->endSection(); ?>
