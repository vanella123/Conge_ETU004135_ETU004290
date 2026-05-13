<?php $this->extend('layout'); ?>

<?php $this->section('content'); ?>

<div style="margin-bottom: 1.5rem;">
    <h2 style="margin: 0; font-size: 1.2rem;">
        <i class="bi bi-speedometer2"></i> Tableau de Bord RH
    </h2>
</div>

<div class="metrics">
    <div class="metric">
        <div class="metric-top">
            <div class="metric-icon mi-amber">
                <i class="bi bi-hourglass-split"></i>
            </div>
        </div>
        <div class="metric-val"><?= $demandes_attente ?? 0 ?></div>
        <div class="metric-label">En attente</div>
    </div>
    
    <div class="metric">
        <div class="metric-top">
            <div class="metric-icon mi-green">
                <i class="bi bi-check-circle"></i>
            </div>
        </div>
        <div class="metric-val"><?= $approuvees_total ?? 0 ?></div>
        <div class="metric-label">Approuvées</div>
    </div>
    
    <div class="metric">
        <div class="metric-top">
            <div class="metric-icon mi-red">
                <i class="bi bi-x-circle"></i>
            </div>
        </div>
        <div class="metric-val"><?= $refusees_total ?? 0 ?></div>
        <div class="metric-label">Refusées</div>
    </div>
    
    <div class="metric">
        <div class="metric-top">
            <div class="metric-icon mi-blue">
                <i class="bi bi-people"></i>
            </div>
        </div>
        <div class="metric-val"><?= $employes_total ?? 0 ?></div>
        <div class="metric-label">Employés</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
    <div class="data-card">
        <div class="data-card-head">
            <h3><i class="bi bi-inbox"></i> Accès Rapides</h3>
        </div>
        <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 10px;">
            <a href="<?= base_url('rh/demandes') ?>" class="btn-forest" style="text-align: center;">
                <i class="bi bi-inbox"></i> Demandes en Attente
            </a>
            <a href="<?= base_url('rh/employes') ?>" class="btn-secondary" style="text-align: center;">
                <i class="bi bi-people"></i> Gestion Employés
            </a>
            <a href="<?= base_url('rh/soldes') ?>" class="btn-secondary" style="text-align: center;">
                <i class="bi bi-pie-chart"></i> Soldes
            </a>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
