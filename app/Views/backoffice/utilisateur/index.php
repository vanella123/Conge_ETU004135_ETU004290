<?= $this->extend('backoffice/layout') ?>

<?= $this->section('title') ?>Gestion des utilisateurs<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Utilisateurs<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Liste complete des utilisateurs inscrits (hors administrateurs).<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="card">
        <h3 class="section-title">Liste des utilisateurs</h3>
        <p class="section-subtitle">Visualisez les informations de base des utilisateurs.</p>

        <?php if (!empty($utilisateurs)): ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Genre</th>
                            <th>Status Gold</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($utilisateurs as $user): ?>
                            <tr>
                                <td>
                                    <strong><?= esc($user['nom']) ?></strong>
                                </td>
                                <td><?= esc($user['email']) ?></td>
                                <td><?= esc($user['genre']) ?></td>
                                <td>
                                    <?php if ($user['is_gold']): ?>
                                        <span class="badge success">Gold</span>
                                    <?php else: ?>
                                        <span class="badge neutral">Standard</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="table-actions">
                                        <a href="<?= base_url('admin/utilisateurs/view/' . esc($user['id_utilisateur'])) ?>" class="btn btn-secondary btn-icon" title="Voir les details">
                                            <img src="<?= esc(base_url('assets/icons/eye.svg')) ?>" alt="Voir">
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="hint">Aucun utilisateur trouve.</p>
        <?php endif; ?>
    </div>
<?= $this->endSection() ?>
