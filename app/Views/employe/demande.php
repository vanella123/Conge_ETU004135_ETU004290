<?php $this->extend('layout'); ?>

<?php $this->section('content'); ?>

<div style="margin-bottom: 1.5rem;">
    <h2 style="margin: 0; font-size: 1.2rem;">
        <i class="bi bi-plus-circle"></i> Nouvelle Demande de Congés
    </h2>
</div>

<form method="post" action="<?= base_url('employe/conges/demande') ?>">
    <?= csrf_field() ?>
    
    <div class="form-section">
        <h3>Informations de la Demande</h3>
        
        <div class="form-grid-2">
            <!-- Type de congé -->
            <div class="f-group">
                <label class="f-label" for="type_conge_id">Type de Congé <span style="color: var(--danger);">*</span></label>
                <select id="type_conge_id" name="type_conge_id" class="f-select" required>
                    <option value="">-- Sélectionner --</option>
                    <?php foreach($types_conges ?? [] as $type): ?>
                        <option value="<?= $type['id'] ?>" <?= old('type_conge_id') == $type['id'] ? 'selected' : '' ?>>
                            <?= esc($type['libelle']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if(isset($errors['type_conge_id'])): ?>
                    <div class="f-error"><?= $errors['type_conge_id'] ?></div>
                <?php endif; ?>
            </div>
            
            <!-- Motif (optionnel) -->
            <div class="f-group">
                <label class="f-label" for="motif">Motif (optionnel)</label>
                <input type="text" id="motif" name="motif" class="f-input" 
                       value="<?= old('motif') ?>"
                       placeholder="Décrivez brièvement">
            </div>
        </div>
        
        <div class="form-grid-2">
            <!-- Date de début -->
            <div class="f-group">
                <label class="f-label" for="date_debut">Date de Début <span style="color: var(--danger);">*</span></label>
                <input type="date" id="date_debut" name="date_debut" class="f-input" 
                       value="<?= old('date_debut') ?>"
                       required>
                <?php if(isset($errors['date_debut'])): ?>
                    <div class="f-error"><?= $errors['date_debut'] ?></div>
                <?php endif; ?>
            </div>
            
            <!-- Date de fin -->
            <div class="f-group">
                <label class="f-label" for="date_fin">Date de Fin <span style="color: var(--danger);">*</span></label>
                <input type="date" id="date_fin" name="date_fin" class="f-input" 
                       value="<?= old('date_fin') ?>"
                       required>
                <?php if(isset($errors['date_fin'])): ?>
                    <div class="f-error"><?= $errors['date_fin'] ?></div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Jours estimés -->
        <div class="f-computed">
            <div class="f-computed-num" id="nb_jours_display">0</div>
            <div class="f-computed-label">Nombre de jours</div>
        </div>
        <div class="f-hint">
            Calculé automatiquement (jours ouvrables uniquement)
        </div>
    </div>
    
    <!-- Commentaire -->
    <div class="form-section">
        <h3>Informations Additionnelles</h3>
        <div class="f-group">
            <label class="f-label" for="commentaire">Commentaire (optionnel)</label>
            <textarea id="commentaire" name="commentaire" class="f-textarea" 
                      placeholder="Toute information supplémentaire..."><?= old('commentaire') ?></textarea>
        </div>
    </div>
    
    <!-- Actions -->
    <div class="form-actions">
        <button type="submit" class="btn-forest">
            <i class="bi bi-check-circle"></i> Soumettre
        </button>
        <a href="<?= base_url('employe/conges') ?>" class="btn-secondary">
            <i class="bi bi-x-circle"></i> Annuler
        </a>
    </div>
</form>

<script>
document.getElementById('date_debut').addEventListener('change', calculerJours);
document.getElementById('date_fin').addEventListener('change', calculerJours);

function calculerJours() {
    const debut = document.getElementById('date_debut').value;
    const fin = document.getElementById('date_fin').value;
    
    if(!debut || !fin) return;
    
    const dateDebut = new Date(debut);
    const dateFin = new Date(fin);
    
    let jours = 0;
    let current = new Date(dateDebut);
    
    while(current <= dateFin) {
        const jour = current.getDay();
        if(jour !== 0 && jour !== 6) { // Exclure samedi et dimanche
            jours++;
        }
        current.setDate(current.getDate() + 1);
    }
    
    document.getElementById('nb_jours_display').textContent = jours;
}
</script>

<?php $this->endSection(); ?>
