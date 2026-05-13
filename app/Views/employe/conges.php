<?php $this->extend('layout'); ?>

<?php $this->section('content'); ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2 style="margin: 0; font-size: 1.2rem;">
        <i class="bi bi-calendar-check"></i> Mes Demandes de Congés
    </h2>
    <a href="<?= base_url('employe/conges/demande') ?>" class="btn-forest">
        <i class="bi bi-plus-circle"></i> Nouvelle Demande
    </a>
</div>

<div class="data-card">
    <table class="tbl">
        <thead>
            <tr>
                <th>Type</th>
                <th>Dates</th>
                <th>Jours</th>
                <th>Statut</th>
                <th>Commentaire RH</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($conges ?? []) > 0): ?>
                <?php foreach($conges as $conge): ?>
                <tr>
                    <td>
                        <span class="type-badge t-annuel">
                            <?= esc($conge['type_nom'] ?? 'Annuel') ?>
                        </span>
                    </td>
                    <td class="td-muted" style="font-size: 0.8rem;">
                        <?= date('d/m/Y', strtotime($conge['date_debut'])) ?> → 
                        <?= date('d/m/Y', strtotime($conge['date_fin'])) ?>
                    </td>
                    <td class="td-mono"><?= $conge['nb_jours'] ?> j.</td>
                    <td>
                        <span class="statut s-<?= strtolower(str_replace('_', '', $conge['status'])) ?>">
                            <?= str_replace('_', ' ', ucfirst($conge['status'])) ?>
                        </span>
                    </td>
                    <td class="td-muted" style="font-size: 0.8rem;">
                        <?php if($conge['commentaire_rh']): ?>
                            <abbr title="<?= esc($conge['commentaire_rh']) ?>" style="cursor: help;">
                                <?= substr(esc($conge['commentaire_rh']), 0, 30) ?>...
                            </abbr>
                        <?php else: ?>
                            —
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="action-btns">
                            <?php if($conge['status'] === 'en_attente'): ?>
                            <a href="<?= base_url('employe/conges/annuler/' . $conge['id']) ?>" 
                               class="btn-sm btn-cancel" 
                               onclick="return confirm('Êtes-vous sûr ?')">
                                <i class="bi bi-trash"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 2rem; color: var(--muted);">
                        Aucune demande. <a href="<?= base_url('employe/conges/demande') ?>" style="color: var(--forest);">Soumettre une demande</a>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php $this->endSection(); ?>
