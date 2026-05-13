<?php $this->extend('layout'); ?>

<?php $this->section('content'); ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
    <h2 style="margin: 0; font-size: 1.2rem;">
        <i class="bi bi-person-gear"></i> Gestion des Employés
    </h2>
    <a href="<?= base_url('admin/employes/ajouter') ?>" class="btn-forest">
        <i class="bi bi-plus-circle"></i> Ajouter
    </a>
</div>

<div class="data-card">
    <table class="tbl">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Email</th>
                <th>Rôle</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($employes ?? []) > 0): ?>
                <?php foreach($employes as $emp): ?>
                <tr>
                    <td class="td-name"><?= esc($emp['nom'] . ' ' . ($emp['prenom'] ?? '')) ?></td>
                    <td class="td-muted"><?= esc($emp['email']) ?></td>
                    <td>
                        <span class="type-badge" style="background: var(--info-bg); color: var(--info);">
                            <?= ucfirst($emp['role']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="<?= $emp['actif'] ? 'statut s-approuvee' : 'statut s-annulee' ?>">
                            <?= $emp['actif'] ? 'Actif' : 'Inactif' ?>
                        </span>
                    </td>
                    <td>
                        <div class="action-btns">
                            <a href="<?= base_url('admin/employes/edit/' . $emp['id']) ?>" class="btn-sm btn-edit">
                                <i class="bi bi-pencil"></i> Éditer
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" style="text-align: center; padding: 2rem; color: var(--muted);">
                        Aucun employé.
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php $this->endSection(); ?>
