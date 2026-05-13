<?= $this->extend('backoffice/layout') ?>

<?= $this->section('title') ?>Details de l'utilisateur<?= $this->endSection() ?>
<?= $this->section('page_title') ?><?= esc($user['nom']) ?><?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Informations detaillees et historique des regimes achetes.<?= $this->endSection() ?>

<?= $this->section('page_actions') ?>
    <a href="<?= base_url('admin/utilisateurs') ?>" class="btn btn-secondary">
        <img class="icon" src="<?= esc(base_url('assets/icons/arrow-left.svg')) ?>" alt="">
        Retour a la liste
    </a>
<?= $this->endSection() ?>

<?= $this->section('head') ?>
    
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="grid-2">
        <div class="card">
            <h3 class="section-title"><img class="icon" src="<?= esc(base_url('assets/icons/user-round.svg')) ?>" alt=""> Informations generales</h3>
            <p class="section-subtitle">Apercu des caracteristiques de l'utilisateur.</p>
            
            <div class="detail-card">
                <div class="detail-row">
                    <span class="detail-label"><strong>Email</strong></span>
                    <span class="detail-value"><?= esc($user['email']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><strong>Genre</strong></span>
                    <span class="detail-value"><?= esc($user['genre']) ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><strong>Date de naissance</strong></span>
                    <span class="detail-value"><?= esc($user['date_naissance'] ?? 'Non renseigne') ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><strong>Status Gold</strong></span>
                    <span class="detail-value">
                        <?php if ($user['is_gold']): ?>
                            <span class="badge success">Oui</span>
                        <?php else: ?>
                            <span class="badge neutral">Non</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><strong>Solde</strong></span>
                    <span class="detail-value"><?= number_format((float)$user['argent'], 0, ',', ' ') ?> Ar</span>
                </div>
            </div>
        </div>

        <div class="card">
            <h3 class="section-title"><img class="icon" src="<?= esc(base_url('assets/icons/activity.svg')) ?>" alt=""> Caracteristiques physiques</h3>
            <p class="section-subtitle">Donnees de poids et de taille utilisees pour les regimes.</p>

            <div class="detail-card">
                <div class="detail-row">
                    <span class="detail-label"><strong>Taille</strong></span>
                    <span class="detail-value"><?= esc($user['taille_cm'] ?? '0') ?> cm</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><strong>Poids actuel</strong></span>
                    <span class="detail-value"><?= esc($user['poids_kg'] ?? '0') ?> kg</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><strong>Poids objectif</strong></span>
                    <span class="detail-value"><?= esc($user['poids_objectif'] ?? 'Non defini') ?> <?= $user['poids_objectif'] ? 'kg' : '' ?></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label"><strong>IMC Actuel</strong></span>
                    <span class="detail-value"><?= $imc !== null ? number_format((float) $imc, 2, ',', ' ') : 'N/A' ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <h3 class="section-title"><img class="icon" src="<?= esc(base_url('assets/icons/apple.svg')) ?>" alt=""> Regimes Achetes</h3>
        <p class="section-subtitle">Historique des commandes de l'utilisateur.</p>

        <?php if (!empty($commandes)): ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Date d'Achat</th>
                            <th>Regime</th>
                            <th>Duree (jours)</th>
                            <th>Prix Paye</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($commandes as $commande): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($commande['date_achat'])) ?></td>
                                <td><strong><?= esc($commande['nom_regime']) ?></strong></td>
                                <td><?= esc($commande['nb_jours']) ?></td>
                                <td><?= number_format((float)$commande['montant_paye'], 0, ',', ' ') ?> Ar</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="hint">Cet utilisateur n'a achete aucun regime pour le moment.</p>
        <?php endif; ?>
    </div>
<?= $this->endSection() ?>
