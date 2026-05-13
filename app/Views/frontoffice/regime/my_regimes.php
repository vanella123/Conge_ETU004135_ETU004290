<?= $this->extend('frontoffice/layout') ?>

<?= $this->section('title') ?>Mes régimes<?= $this->endSection() ?>



<?= $this->section('content') ?>
<section class="stack">
    <div class="hero">
        <div class="page-header">
            <h1>Mes régimes</h1>
            <p class="sub">Historique de vos achats, avec accès rapide à chaque détail et export PDF.</p>
        </div>
    </div>

    <div class="card">
        <?php if (empty($purchases)) : ?>
            <div class="empty">Aucun régime acheté pour le moment.</div>
        <?php else : ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Régime</th>
                            <th>Objectif</th>
                            <th>Variation estimée</th>
                            <th>Durée</th>
                            <th>Prix</th>
                            <th>Date d'achat</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($purchases as $purchase) : ?>
                        <tr>
                            <td><span class="regime-name"><?= esc($purchase['nom_regime']) ?></span></td>
                            <td><?= esc($purchase['objective_label']) ?></td>
                            <td><span class="badge"><?= esc($purchase['variation_label']) ?></span></td>
                            <td><span class="badge badge-muted"><?= esc($purchase['nb_jours']) ?> j</span></td>
                            <td><?= esc(number_format((float) $purchase['montant_paye'], 0, ',', ' ')) ?> Ar</td>
                            <td><?= esc(date('d/m/Y', strtotime((string) $purchase['date_achat']))) ?></td>
                            <td>
                                <div class="table-actions">
                                    <a href="<?= esc(site_url('mes-regimes/' . $purchase['id_commande'])) ?>" class="btn btn-ghost btn-icon" title="Voir le détail">
                                        <img src="<?= esc(base_url('assets/icons/eye.svg')) ?>" alt="Voir">
                                    </a>
                                    <a href="<?= esc(site_url('mes-regimes/' . $purchase['id_commande'] . '/export-pdf')) ?>" class="btn btn-secondary btn-icon" title="Exporter en PDF">
                                        <img src="<?= esc(base_url('assets/icons/file-text.svg')) ?>" alt="Exporter">
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>
<?= $this->endSection() ?>
