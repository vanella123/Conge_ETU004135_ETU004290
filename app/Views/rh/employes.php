<?php $this->extend('layout'); ?>

<?php $this->section('content'); ?>

<div style="margin-bottom: 1.5rem;">
    <h2 style="margin: 0; font-size: 1.2rem;">
        <i class="bi bi-people"></i> Gestion des Employés
    </h2>
</div>

<div class="data-card">
    <div class="data-card-head">
        <h3>Liste des Employés</h3>
        <a href="<?= base_url('rh/employes/ajouter') ?>" class="btn-forest">
            <i class="bi bi-plus-circle"></i> Ajouter
        </a>
    </div>
    <table class="tbl">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Département</th>
                <th>Rôle</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($employes ?? []) > 0): ?>
                <?php foreach($employes as $employe): ?>
                <tr>
                    <td class="td-name"><?= esc($employe['nom']) ?></td>
                    <td class="td-muted"><?= esc($employe['email']) ?></td>
                    <td><?= esc($employe['departement'] ?? '—') ?></td>
                    <td>
                        <span class="type-badge" style="background: var(--info-bg); color: var(--info);">
                            <?= ucfirst($employe['role']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="<?= $employe['actif'] ? 'statut s-approuvee' : 'statut s-annulee' ?>">
                            <?= $employe['actif'] ? 'Actif' : 'Inactif' ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-btns">
                            <a href="<?= base_url('rh/employes/edit/' . $employe['id']) ?>" class="btn-sm btn-edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 2rem; color: var(--muted);">
                        Aucun employé.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php $this->endSection(); ?>
