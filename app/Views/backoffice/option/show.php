<?= $this->extend('backoffice/layout') ?>

<?= $this->section('title') ?>Detail de l'option<?= $this->endSection() ?>

<?= $this->section('page_title') ?><?= esc($option['nom_option'] ?? 'Option') ?><?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Consultez les regles actives de l'offre, son historique d'evolution et les utilisateurs deja rattaches a Gold.<?= $this->endSection() ?>
<?= $this->section('page_actions') ?>
    <a href="<?= base_url('admin/options') ?>" class="btn btn-secondary">Retour aux options</a>
    <a href="<?= base_url('admin/options/edit/' . $option['id_option']) ?>" class="btn btn-primary">Modifier</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="hero-badges">
        <span class="badge success">Reduction: -<?= esc(rtrim(rtrim(number_format((float) $option['reduction_pourcentage'], 2, ',', ' '), '0'), ',')) ?>%</span>
    </div>

    <div class="card">
        <h3 class="section-title">Specs actuelles</h3>
        <p class="section-subtitle">Ces valeurs sont celles qui seront visibles et appliquees cote frontoffice.</p>

        <div class="metric-grid">
            <div class="metric-card">
                <div class="metric-label"><img src="<?= esc(base_url('assets/icons/wallet.svg')) ?>" alt="">Prix</div>
                <div class="metric-value">
                    <?= esc(number_format((float) $option['prix_unique'], 2, ',', ' ')) ?> Ar
                </div>
            </div>
            <div class="metric-card">
                <div class="metric-label"><img src="<?= esc(base_url('assets/icons/apple.svg')) ?>" alt="">Regimes requis</div>
                <div class="metric-value"><?= esc((string) $option['nb_regimes_achetes']) ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-label"><img src="<?= esc(base_url('assets/icons/users.svg')) ?>" alt="">Membres Gold</div>
                <div class="metric-value"><?= esc((string) count($goldMembers ?? [])) ?></div>
            </div>
        </div>
    </div>

    <div class="card">
        <h3 class="section-title">Utilisateurs inclus</h3>
        <p class="section-subtitle">Cette liste permet de voir qui possede deja Gold et quel est leur niveau d'activite.</p>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Genre</th>
                        <th>Solde</th>
                        <th>Nb commandes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (! empty($goldMembers)): ?>
                        <?php foreach ($goldMembers as $member): ?>
                            <tr>
                                <td><strong><?= esc($member['nom']) ?></strong></td>
                                <td><?= esc($member['email']) ?></td>
                                <td><?= esc((string) $member['genre']) ?></td>
                                <td><?= esc(number_format((float) $member['argent'], 2, ',', ' ')) ?> Ar</td>
                                <td><?= esc((string) $member['nb_commandes']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">Aucun utilisateur n'est encore Gold.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <h3 class="section-title">Historique des specs</h3>
        <p class="section-subtitle">Chaque creation ou modification garde une trace des conditions Gold a la date d'effet choisie.</p>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date d'effet</th>
                        <th>Prix</th>
                        <th>Reduction</th>
                        <th>Regimes requis</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (! empty($historique)): ?>
                        <?php foreach ($historique as $entry): ?>
                            <tr>
                                <td><?= esc((string) $entry['date_debut']) ?></td>
                                <td><?= esc(number_format((float) $entry['prix'], 2, ',', ' ')) ?> Ar</td>
                                <td>-<?= esc(rtrim(rtrim(number_format((float) $entry['reduction_pourcentage'], 2, ',', ' '), '0'), ',')) ?>%</td>
                                <td><?= esc((string) $entry['nb_regimes_achetes']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4">Aucun historique pour cette option.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?= $this->endSection() ?>
