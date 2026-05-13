<?= $this->extend('frontoffice/layout') ?>

<?= $this->section('title') ?>Historique des transactions<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="stack">
    <div class="hero">
        <div class="page-header">
            <h1>Historique des transactions</h1>
            <p class="sub">Retrouvez toutes vos commandes passées, le détail des durées et les montants associés.</p>
        </div>
    </div>

    <div class="card">
        <?php if (empty($transactions)): ?>
            <div class="empty">Aucune transaction trouvée pour le moment.</div>
        <?php else: ?>
            <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date d'achat</th>
                        <th>Régime</th>
                        <th>Durée</th>
                        <th>Prix durée</th>
                        <th>Montant payé</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $transaction): ?>
                        <tr>
                            <td><?= esc($transaction['date_achat']) ?></td>
                            <td><?= esc($transaction['nom_regime'] ?? 'Non défini') ?></td>
                            <td><?= isset($transaction['nb_jours']) ? esc($transaction['nb_jours']) . ' jours' : 'Non défini' ?></td>
                            <td><?= isset($transaction['prix_duree']) ? esc($transaction['prix_duree']) . ' Ar' : 'Non défini' ?></td>
                            <td><?= esc($transaction['montant_paye']) ?> Ar</td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        <?php endif; ?>
    </div>
</section>
<?= $this->endSection() ?>
