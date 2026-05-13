<?= $this->extend('backoffice/layout') ?>

<?= $this->section('title') ?>Demandes de codes promo<?= $this->endSection() ?>
<?= $this->section('page_title') ?>Demandes a valider<?= $this->endSection() ?>
<?= $this->section('page_subtitle') ?>Confirmez ou refusez les demandes clients et verifiez si le code existe deja.<?= $this->endSection() ?>
<?= $this->section('page_actions') ?>
    <a href="<?= base_url('admin/promos') ?>" class="btn btn-secondary">Retour aux codes promo</a>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
    <div class="card">
        <h3 class="section-title">Synthese</h3>
        <p class="section-subtitle">Vue rapide des demandes en attente.</p>

        <div class="stats-grid">
            <div class="stat-box stat-a">
                <div class="stat-icon"><img src="<?= esc(base_url('assets/icons/badge-check.svg')) ?>" alt=""></div>
                <h4>Demandes en attente</h4>
                <p><?= esc((string) ($stats['en_attente'] ?? 0)) ?></p>
            </div>
            <div class="stat-box stat-b">
                <div class="stat-icon"><img src="<?= esc(base_url('assets/icons/ticket-check.svg')) ?>" alt=""></div>
                <h4>Codes existants</h4>
                <p><?= esc((string) ($stats['codes_existants'] ?? 0)) ?></p>
            </div>
            <div class="stat-box stat-c">
                <div class="stat-icon"><img src="<?= esc(base_url('assets/icons/ticket-x.svg')) ?>" alt=""></div>
                <h4>Codes introuvables</h4>
                <p><?= esc((string) ($stats['codes_inexistants'] ?? 0)) ?></p>
            </div>
        </div>
    </div>

    <div class="card">
        <h3 class="section-title">Demandes en attente</h3>
        <p class="section-subtitle"><?= count($demandes ?? []) ?> demande(s).</p>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Client</th>
                        <th>Code saisi</th>
                        <th>Etat du code</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (! empty($demandes)): ?>
                        <?php foreach ($demandes as $demande): ?>
                            <tr>
                                <td>
                                    <div class="promo-client">
                                        <strong><?= esc((string) ($demande['utilisateur_nom'] ?? '')) ?></strong>
                                        <span><?= esc((string) ($demande['utilisateur_email'] ?? '')) ?></span>
                                    </div>
                                </td>
                                <td><span class="code-pill"><?= esc((string) ($demande['code_saisi'] ?? '')) ?></span></td>
                                <td>
                                    <?php if (! empty($demande['code_existe'])): ?>
                                        <span class="badge success" title="Ce code existe dans la liste des codes promo.">Code trouve</span>
                                    <?php else: ?>
                                        <span class="badge badge-danger" title="Aucun code ne correspond a cette saisie.">Code introuvable</span>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc((string) ($demande['date_demande'] ?? '')) ?></td>
                                <td>
                                    <div class="table-actions">
                                        <?php if (! empty($demande['code_existe'])): ?>
                                            <form action="<?= base_url('admin/promos/validate/approve/' . $demande['id_demande_code_promo']) ?>" method="post">
                                                <?= csrf_field() ?>
                                                <button type="submit" class="btn btn-primary btn-icon" title="Accepter la demande" data-confirm-message="Accepter cette demande ?">
                                                    <img src="<?= esc(base_url('assets/icons/check.svg')) ?>" alt="Accepter">
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <form action="<?= base_url('admin/promos/validate/reject/' . $demande['id_demande_code_promo']) ?>" method="post">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-danger btn-icon" title="Supprimer la demande" data-confirm-message="Supprimer cette demande ?">
                                                <img src="<?= esc(base_url('assets/icons/x.svg')) ?>" alt="Supprimer">
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5">Aucune demande en attente pour le moment.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
<?= $this->endSection() ?>
