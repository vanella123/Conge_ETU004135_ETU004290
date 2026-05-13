<?= $this->extend('backoffice/layout') ?>

<?= $this->section('title') ?>Parametres IMC<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Parametres IMC<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Consultez les differents intervalles d'IMC (Indice de Masse Corporelle).<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="card">
        <h3 class="section-title">Liste des intervalles IMC</h3>
        <p class="section-subtitle">Ces intervalles servent de base pour formuler le diagnostic de poids.</p>

        <?php if (!empty($imcs)): ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nom (Label)</th>
                            <th>Minimum</th>
                            <th>Maximum</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($imcs as $imc): ?>
                            <tr>
                                <td>
                                    <strong><?= esc($imc['label_imc']) ?></strong>
                                </td>
                                <td><?= esc($imc['imc_min']) ?></td>
                                <td><?= esc($imc['imc_max']) ?></td>
                                <td>
                                    <div class="table-actions">
                                        <a href="<?= base_url('admin/imc/edit/' . esc($imc['id_imc'])) ?>" class="btn btn-secondary btn-icon" title="Modifier">
                                            <img src="<?= esc(base_url('assets/icons/pencil.svg')) ?>" alt="Edit">
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="hint">Aucun parametre IMC trouve.</p>
        <?php endif; ?>
    </div>
<?= $this->endSection() ?>
