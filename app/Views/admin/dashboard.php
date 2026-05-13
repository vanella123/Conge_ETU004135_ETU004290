<?php $this->extend('layout'); ?>

<?php $this->section('content'); ?>

<div style="margin-bottom: 1.5rem;">
    <h2 style="margin: 0; font-size: 1.2rem;">
        <i class="bi bi-speedometer2"></i> Tableau de Bord Admin
    </h2>
</div>

<div class="metrics">
    <div class="metric">
        <div class="metric-top">
            <div class="metric-icon mi-blue">
                <i class="bi bi-people"></i>
            </div>
        </div>
        <div class="metric-val">0</div>
        <div class="metric-label">Employés</div>
    </div>
    
    <div class="metric">
        <div class="metric-top">
            <div class="metric-icon mi-forest">
                <i class="bi bi-diagram-3"></i>
            </div>
        </div>
        <div class="metric-val">0</div>
        <div class="metric-label">Départements</div>
    </div>
    
    <div class="metric">
        <div class="metric-top">
            <div class="metric-icon mi-amber">
                <i class="bi bi-list-check"></i>
            </div>
        </div>
        <div class="metric-val">0</div>
        <div class="metric-label">Types de Congés</div>
    </div>
    
    <div class="metric">
        <div class="metric-top">
            <div class="metric-icon mi-green">
                <i class="bi bi-graph-up"></i>
            </div>
        </div>
        <div class="metric-val">0</div>
        <div class="metric-label">Demandes Traitées</div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
    <div class="data-card">
        <div class="data-card-head">
            <h3><i class="bi bi-wrench"></i> Administration</h3>
        </div>
        <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 10px;">
            <a href="<?= base_url('admin/employes') ?>" class="btn-forest" style="text-align: center;">
                <i class="bi bi-person-gear"></i> Gestion Employés
            </a>
            <a href="<?= base_url('admin/departements') ?>" class="btn-secondary" style="text-align: center;">
                <i class="bi bi-diagram-3"></i> Départements
            </a>
            <a href="<?= base_url('admin/types-conge') ?>" class="btn-secondary" style="text-align: center;">
                <i class="bi bi-list-check"></i> Types de Congés
            </a>
            <a href="<?= base_url('admin/soldes') ?>" class="btn-secondary" style="text-align: center;">
                <i class="bi bi-pie-chart"></i> Soldes
            </a>
        </div>
    </div>
    
    <div class="data-card">
        <div class="data-card-head">
            <h3><i class="bi bi-eye"></i> Supervision</h3>
        </div>
        <div style="padding: 1.25rem; display: flex; flex-direction: column; gap: 10px;">
            <a href="<?= base_url('admin/demandes') ?>" class="btn-forest" style="text-align: center;">
                <i class="bi bi-inbox"></i> Toutes les Demandes
            </a>
            <a href="<?= base_url('rh/demandes') ?>" class="btn-secondary" style="text-align: center;">
                <i class="bi bi-hourglass-split"></i> En Attente
            </a>
        </div>
    </div>
</div>

<?php $this->endSection(); ?>
