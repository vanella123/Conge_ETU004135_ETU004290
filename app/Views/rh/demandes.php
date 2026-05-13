<?php $this->extend('layout'); ?>

<?php $this->section('content'); ?>

<div style="margin-bottom: 1.5rem;">
    <h2 style="margin: 0; font-size: 1.2rem;">
        <i class="bi bi-inbox"></i> Demandes en Attente
    </h2>
</div>

<div class="metrics">
    <div class="metric">
        <div class="metric-top">
            <div class="metric-icon mi-amber">
                <i class="bi bi-clock"></i>
            </div>
        </div>
        <div class="metric-val"><?= count($demandes_attente ?? []) ?></div>
        <div class="metric-label">En attente</div>
    </div>
    
    <div class="metric">
        <div class="metric-top">
            <div class="metric-icon mi-green">
                <i class="bi bi-check-circle"></i>
            </div>
        </div>
        <div class="metric-val"><?= $approuvees_total ?? 0 ?></div>
        <div class="metric-label">Approuvées</div>
    </div>
    
    <div class="metric">
        <div class="metric-top">
            <div class="metric-icon mi-red">
                <i class="bi bi-x-circle"></i>
            </div>
        </div>
        <div class="metric-val"><?= $refusees_total ?? 0 ?></div>
        <div class="metric-label">Refusées</div>
    </div>
</div>

<div class="data-card">
    <div class="data-card-head">
        <h3><i class="bi bi-list-check"></i> Demandes à Traiter</h3>
    </div>
    <table class="tbl">
        <thead>
            <tr>
                <th>Employé</th>
                <th>Type</th>
                <th>Période</th>
                <th>Jours</th>
                <th>Demandé le</th>
                <th>Motif</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(count($demandes_attente ?? []) > 0): ?>
                <?php foreach($demandes_attente as $demande): ?>
                <tr>
                    <td class="td-name">
                        <span class="avatar av-green">
                            <?= strtoupper(substr($demande['emp_nom'] ?? '', 0, 2)) ?>
                        </span>
                        <span style="margin-left: 8px;"><?= esc(($demande['emp_nom'] ?? '') . ' ' . ($demande['emp_prenom'] ?? '')) ?></span>
                    </td>
                    <td>
                        <span class="type-badge t-annuel">
                            <?= esc($demande['type_nom'] ?? 'Annuel') ?>
                        </span>
                    </td>
                    <td class="td-muted" style="font-size: 0.8rem;">
                        <?= date('d/m', strtotime($demande['date_debut'])) ?> → 
                        <?= date('d/m/Y', strtotime($demande['date_fin'])) ?>
                    </td>
                    <td class="td-mono"><?= $demande['nb_jours'] ?> j.</td>
                    <td class="td-muted" style="font-size: 0.8rem;">
                        <?= date('d/m/Y H:i', strtotime($demande['created_at'])) ?>
                    </td>
                    <td class="td-muted" style="font-size: 0.8rem; max-width: 150px; overflow: hidden; text-overflow: ellipsis;">
                        <?= $demande['motif'] ? esc(substr($demande['motif'], 0, 30)) : '—' ?>
                    </td>
                    <td>
                        <div class="action-btns">
                            <button type="button" class="btn-sm btn-approve" 
                                    onclick="approuverDemande(<?= $demande['id'] ?>, '<?= esc(($demande['emp_nom'] ?? '') . ' ' . ($demande['emp_prenom'] ?? '')) ?>')">
                                <i class="bi bi-check-circle"></i>
                            </button>
                            <button type="button" class="btn-sm btn-refuse" 
                                    onclick="refuserDemande(<?= $demande['id'] ?>, '<?= esc(($demande['emp_nom'] ?? '') . ' ' . ($demande['emp_prenom'] ?? '')) ?>')">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 2rem; color: var(--muted);">
                        <i class="bi bi-check-circle" style="font-size: 2rem; opacity: 0.5;"></i>
                        <p>Aucune demande en attente. Bravo !</p>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal Approuver -->
<div id="modalApprouver" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 100; align-items: center; justify-content: center;">
    <div style="background: var(--white); border-radius: 12px; padding: 2rem; max-width: 400px; width: 90%;">
        <h3 style="margin: 0 0 1rem; color: var(--ink);">
            <i class="bi bi-check-circle" style="color: var(--success);"></i> Approuver la demande
        </h3>
        <p id="modalApprouverTexte" style="color: var(--muted); margin: 0 0 1.5rem;"></p>
        <form method="post" action="<?= base_url('rh/demandes/approuver') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="demande_id" id="approuverDemandeId">
            
            <div class="f-group">
                <label class="f-label">Commentaire (optionnel)</label>
                <textarea name="commentaire" class="f-textarea" placeholder="Commentaire pour l'employé..."></textarea>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 1.5rem;">
                <button type="submit" class="btn-forest" style="flex: 1;">
                    <i class="bi bi-check"></i> Approuver
                </button>
                <button type="button" class="btn-secondary" style="flex: 1;" onclick="fermerModals()">
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Refuser -->
<div id="modalRefuser" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 100; align-items: center; justify-content: center;">
    <div style="background: var(--white); border-radius: 12px; padding: 2rem; max-width: 400px; width: 90%;">
        <h3 style="margin: 0 0 1rem; color: var(--danger);">
            <i class="bi bi-x-circle"></i> Refuser la demande
        </h3>
        <p id="modalRefuserTexte" style="color: var(--muted); margin: 0 0 1.5rem;"></p>
        <form method="post" action="<?= base_url('rh/demandes/refuser') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="demande_id" id="refuserDemandeId">
            
            <div class="f-group">
                <label class="f-label">Motif du refus <span style="color: var(--danger);">*</span></label>
                <textarea name="motif_refus" class="f-textarea" placeholder="Expliquez le motif du refus..." required></textarea>
            </div>
            
            <div style="display: flex; gap: 10px; margin-top: 1.5rem;">
                <button type="submit" class="btn-refuse" style="flex: 1;">
                    <i class="bi bi-x"></i> Refuser
                </button>
                <button type="button" class="btn-secondary" style="flex: 1;" onclick="fermerModals()">
                    Annuler
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function approuverDemande(id, nom) {
    document.getElementById('approuverDemandeId').value = id;
    document.getElementById('modalApprouverTexte').textContent = 'Approuver la demande de congé de ' + nom + ' ?';
    document.getElementById('modalApprouver').style.display = 'flex';
}

function refuserDemande(id, nom) {
    document.getElementById('refuserDemandeId').value = id;
    document.getElementById('modalRefuserTexte').textContent = 'Refuser la demande de congé de ' + nom + ' ?';
    document.getElementById('modalRefuser').style.display = 'flex';
}

function fermerModals() {
    document.getElementById('modalApprouver').style.display = 'none';
    document.getElementById('modalRefuser').style.display = 'none';
}
</script>

<?php $this->endSection(); ?>
