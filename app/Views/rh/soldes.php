<?php $this->extend('layout'); ?>

<?php $this->section('content'); ?>

<div class="data-card">
    <div class="data-card-head">
        <h3>Soldes RH - <?= esc($annee ?? date('Y')) ?></h3>
    </div>
    <table class="tbl">
        <thead>
            <tr>
                <th>Employé</th>
                <th>Département</th>
                <th>Type</th>
                <th>Attribués</th>
                <th>Pris</th>
                <th>Restants</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($soldes ?? [] as $solde): ?>
                <tr>
                    <td><?= esc(($solde['emp_nom'] ?? '') . ' ' . ($solde['emp_prenom'] ?? '')) ?></td>
                    <td><?= esc($solde['dept_nom'] ?? '—') ?></td>
                    <td><?= esc($solde['type_nom'] ?? '—') ?></td>
                    <td class="td-mono"><?= esc($solde['jours_attribues'] ?? 0) ?></td>
                    <td class="td-mono"><?= esc($solde['jours_pris'] ?? 0) ?></td>
                    <td class="td-mono"><?= esc($solde['jours_restants'] ?? 0) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php $this->endSection(); ?>