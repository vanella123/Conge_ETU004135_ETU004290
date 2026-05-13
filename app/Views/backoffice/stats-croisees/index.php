<?= $this->extend('backoffice/layout') ?>

<?= $this->section('title') ?>Statistiques croisées<?= $this->endSection() ?>

<?= $this->section('page_title') ?>Statistiques croisées<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Tableaux croisés pour analyser les relations entre régimes, objectifs et revenus.<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="card">
        <h3 class="section-title">Achats par Régime et Objectif</h3>
        <p class="section-subtitle">Nombre d'achats croisés par régime et objectif utilisateur.</p>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="background: #f1f3f5; border-bottom: 2px solid #dee2e6;">
                        <th style="padding: 10px; text-align: left; font-weight: 600; border-right: 1px solid #dee2e6;">Régime</th>
                        <?php foreach ($objectifs as $objectif): ?>
                            <th style="padding: 10px; text-align: center; font-weight: 600; border-right: 1px solid #dee2e6;">
                                <?= esc(substr($objectif['label_objectif'], 0, 12)) ?>
                            </th>
                        <?php endforeach; ?>
                        <th style="padding: 10px; text-align: center; font-weight: 600;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($regimesObjectifs as $row): ?>
                        <tr style="border-bottom: 1px solid #e9ecef;">
                            <td style="padding: 10px; font-weight: 500; border-right: 1px solid #dee2e6;">
                                <?= esc($row['nom_regime']) ?>
                            </td>
                            <?php $total = 0; ?>
                            <?php foreach ($objectifs as $objectif): ?>
                                <?php $val = $row['obj_' . $objectif['id_objectif']] ?? 0; $total += $val; ?>
                                <td style="padding: 10px; text-align: center; border-right: 1px solid #dee2e6; background: <?= $val > 0 ? '#e7f5f0' : '#fff' ?>;">
                                    <strong><?= $val ?></strong>
                                </td>
                            <?php endforeach; ?>
                            <td style="padding: 10px; text-align: center; background: #f1f3f5; font-weight: 600;">
                                <?= $total ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <h3 class="section-title">Utilisateurs par Objectif</h3>
        <p class="section-subtitle">Répartition des utilisateurs et comptes Gold par objectif.</p>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="background: #f1f3f5; border-bottom: 2px solid #dee2e6;">
                        <th style="padding: 10px; text-align: left; font-weight: 600; border-right: 1px solid #dee2e6;">Objectif</th>
                        <th style="padding: 10px; text-align: center; font-weight: 600; border-right: 1px solid #dee2e6;">Total Utilisateurs</th>
                        <th style="padding: 10px; text-align: center; font-weight: 600; border-right: 1px solid #dee2e6;">Comptes Gold</th>
                        <th style="padding: 10px; text-align: center; font-weight: 600;">% Gold</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($objectifsUtilisateurs as $objectif): ?>
                        <?php $pctGold = $objectif['total'] > 0 ? round(($objectif['gold_count'] / $objectif['total']) * 100) : 0; ?>
                        <tr style="border-bottom: 1px solid #e9ecef;">
                            <td style="padding: 10px; font-weight: 500; border-right: 1px solid #dee2e6;">
                                <?= esc($objectif['label_objectif'] ?? 'N/A') ?>
                            </td>
                            <td style="padding: 10px; text-align: center; border-right: 1px solid #dee2e6;">
                                <strong><?= esc($objectif['total']) ?></strong>
                            </td>
                            <td style="padding: 10px; text-align: center; border-right: 1px solid #dee2e6; background: #ffe7e7;">
                                <strong><?= esc($objectif['gold_count']) ?></strong>
                            </td>
                            <td style="padding: 10px; text-align: center; background: #f1f3f5;">
                                <strong><?= $pctGold ?>%</strong>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <h3 class="section-title">Revenus par Régime</h3>
        <p class="section-subtitle">Chiffre d'affaires généré par chaque régime avec détail des ventes.</p>

        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                <thead>
                    <tr style="background: #f1f3f5; border-bottom: 2px solid #dee2e6;">
                        <th style="padding: 10px; text-align: left; font-weight: 600; border-right: 1px solid #dee2e6;">Régime</th>
                        <th style="padding: 10px; text-align: center; font-weight: 600; border-right: 1px solid #dee2e6;">Nombre de Ventes</th>
                        <th style="padding: 10px; text-align: center; font-weight: 600; border-right: 1px solid #dee2e6;">Revenu Total</th>
                        <th style="padding: 10px; text-align: center; font-weight: 600;">Ticket Moyen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($regimesRevenu as $regime): ?>
                        <?php $ticketMoyen = $regime['ventes'] > 0 ? round($regime['total_revenu'] / $regime['ventes']) : 0; ?>
                        <tr style="border-bottom: 1px solid #e9ecef;">
                            <td style="padding: 10px; font-weight: 500; border-right: 1px solid #dee2e6;">
                                <?= esc($regime['nom_regime']) ?>
                            </td>
                            <td style="padding: 10px; text-align: center; border-right: 1px solid #dee2e6;">
                                <strong><?= esc($regime['ventes']) ?></strong>
                            </td>
                            <td style="padding: 10px; text-align: center; border-right: 1px solid #dee2e6; background: #e7f7ff;">
                                <strong><?= number_format($regime['total_revenu'], 0, ',', ' ') ?> Ar</strong>
                            </td>
                            <td style="padding: 10px; text-align: center; background: #f1f3f5;">
                                <strong><?= number_format($ticketMoyen, 0, ',', ' ') ?> Ar</strong>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

<?= $this->endSection() ?>
