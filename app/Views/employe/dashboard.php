<?php $this->extend('layout'); ?>

<?php $this->section('content'); ?>

<!-- Métriques -->
<div class="metrics">
    <div class="metric">
        <div class="metric-top">
            <div class="metric-icon mi-forest">
                <i class="bi bi-calendar2-check"></i>
            </div>
        </div>
        <div class="metric-val"><?= $conges_total ?? 0 ?></div>
        <div class="metric-label">Congés cette année</div>
    </div>
    
    <div class="metric">
        <div class="metric-top">
            <div class="metric-icon mi-green">
                <i class="bi bi-check-circle"></i>
            </div>
        </div>
        <div class="metric-val"><?= $conges_approuves ?? 0 ?></div>
        <div class="metric-label">Approuvés</div>
    </div>
    
    <div class="metric">
        <div class="metric-top">
            <div class="metric-icon mi-amber">
                <i class="bi bi-clock"></i>
            </div>
        </div>
        <div class="metric-val"><?= $conges_attente ?? 0 ?></div>
        <div class="metric-label">En attente</div>
    </div>
    
    <div class="metric">
        <div class="metric-top">
            <div class="metric-icon mi-red">
                <i class="bi bi-x-circle"></i>
            </div>
        </div>
        <div class="metric-val"><?= $conges_refuses ?? 0 ?></div>
        <div class="metric-label">Refusés</div>
    </div>
</div>

<!-- Soldes -->
<div class="data-card">
    <div class="data-card-head">
        <h3><i class="bi bi-pie-chart"></i> Mes Soldes de Congés</h3>
        <a href="<?= base_url('employe/soldes') ?>" class="btn-secondary">
            <i class="bi bi-arrow-right"></i> Détails
        </a>
    </div>
    <div style="padding: 1.25rem;">
        <?php foreach($soldes ?? [] as $solde): ?>
        <div class="solde-card">
            <div class="solde-header">
                <span class="solde-type"><?= esc($solde['type_nom']) ?></span>
                <span class="solde-nums">
                    <strong><?= round($solde['jours_pris'], 1) ?></strong> / 
                    <?= round($solde['jours_total'], 1) ?>
                </span>
            </div>
            <div class="solde-bar">
                <div class="solde-fill <?= $solde['jours_pris'] > ($solde['jours_total'] * 0.8) ? 'warn' : '' ?>" 
                     style="width: <?= min(100, ($solde['jours_pris'] / $solde['jours_total']) * 100) ?>%"></div>
            </div>
            <div class="solde-label">
                <?= round($solde['jours_total'] - $solde['jours_pris'], 1) ?> jour(s) restant(s)
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Dernières Demandes -->
<div class="data-card">
    <div class="data-card-head">
        <h3><i class="bi bi-inbox"></i> Mes Dernières Demandes</h3>
        <a href="<?= base_url('employe/conges') ?>" class="btn-secondary">
            <i class="bi bi-arrow-right"></i> Toutes
        </a>
    </div>
    <table class="tbl">
        <thead>
            <tr>
                <th>Type</th>
                <th>Dates</th>
                <th>Jours</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($derniers_conges ?? []) > 0): ?>
                <?php foreach($derniers_conges as $conge): ?>
                <tr>
                    <td>
                        <span class="type-badge t-annuel">
                            <?= esc($conge['type_nom']) ?>
                        </span>
                    </td>
                    <td class="td-muted" style="font-size: 0.8rem;">
                        <?= date('d/m/Y', strtotime($conge['date_debut'])) ?> → 
                        <?= date('d/m/Y', strtotime($conge['date_fin'])) ?>
                    </td>
                    <td class="td-mono"><?= $conge['nb_jours'] ?> j.</td>
                    <td>
                        <span class="statut s-<?= strtolower($conge['status']) ?>">
                            <?= ucfirst($conge['status']) ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-btns">
                            <?php if($conge['status'] === 'en_attente'): ?>
                            <a href="<?= base_url('employe/conges/annuler/' . $conge['id']) ?>" 
                               class="btn-sm btn-cancel" 
                               onclick="return confirm('Annuler cette demande ?')">
                                <i class="bi bi-trash"></i> Annuler
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 2rem; color: var(--muted);">
                        Aucune demande pour le moment. 
                        <a href="<?= base_url('employe/conges/demande') ?>" style="color: var(--forest);">
                            Soumettre une demande
                        </a>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Actions Rapides -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1.5rem;">
    <a href="<?= base_url('employe/conges/demande') ?>" class="btn-forest" style="padding: 1rem; text-align: center;">
        <i class="bi bi-plus-circle"></i> Nouvelle Demande
    </a>
    <a href="<?= base_url('employe/conges') ?>" class="btn-secondary" style="padding: 1rem; text-align: center;">
        <i class="bi bi-list-check"></i> Mes Congés
    </a>
</div>

<?php $this->endSection(); ?>
