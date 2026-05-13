<?= $this->extend('backoffice/layout') ?>

<?= $this->section('title') ?>Gestion des options<?= $this->endSection() ?>
<?= $this->section('head') ?>
    
<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Options<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Administrez les offres speciales comme Gold, leurs conditions d'acces et leur tarification actuelle.<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="card">
        <h3 class="section-title">Catalogue des options</h3>
        <p class="section-subtitle">Chaque fiche resume les conditions d'achat, le prix et la reduction appliquee.</p>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Option</th>
                        <th>Conditions</th>
                        <th>Prix</th>
                        <th>Reduction</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (! empty($options)): ?>
                        <?php foreach ($options as $option): ?>
                            <tr>
                                <td>
                                    <span class="option-name">
                                        <img src="<?= esc(base_url('assets/icons/crown.svg')) ?>" alt="">
                                        <strong><?= esc($option['nom_option']) ?></strong>
                                    </span>
                                </td>
                                <td><?= esc((string) $option['nb_regimes_achetes']) ?> regime(s) achetes</td>
                                <td><?= esc(number_format((float) $option['prix_unique'], 2, ',', ' ')) ?> Ar</td>
                                <td><span class="badge success">-<?= esc(rtrim(rtrim(number_format((float) $option['reduction_pourcentage'], 2, ',', ' '), '0'), ',')) ?>%</span></td>
                                <td>
                                    <div class="table-actions">
                                        <a href="<?= base_url('admin/options/view/' . $option['id_option']) ?>" class="btn btn-ghost btn-icon" title="Voir le detail">
                                            <img src="<?= esc(base_url('assets/icons/eye.svg')) ?>" alt="Voir">
                                        </a>
                                        <a href="<?= base_url('admin/options/edit/' . $option['id_option']) ?>" class="btn btn-secondary btn-icon" title="Modifier l'option">
                                            <img src="<?= esc(base_url('assets/icons/pencil.svg')) ?>" alt="Modifier">
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">Aucune option n'a encore ete definie.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?= $this->endSection() ?>
