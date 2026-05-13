<?= $this->extend('frontoffice/layout') ?>

<?= $this->section('title') ?>Acheter un régime<?= $this->endSection() ?>

<?= $this->section('head') ?>

<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
    $formatPrice = static function (float $price): string {
        return number_format($price, 0, ',', ' ');
    };
?>
<section class="stack">
    <div class="hero">
        <div class="page-header">
            <h1>Acheter un régime</h1>
            <p class="sub">Sélectionnez une durée pour le régime <?= esc($regime['nom_regime']) ?> et validez une seule fois votre achat.</p>
        </div>
    </div>

    <div class="card stack">
        <div class="metric-grid">
            <div class="metric-card">
                <div class="metric-label">Régime</div>
                <div class="metric-value small"><?= esc($regime['nom_regime']) ?></div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Variation poids</div>
                <div class="metric-value"><?= esc($regime['variation_poids']) ?> kg</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Mode d'accès</div>
                <div class="metric-value"><?= $isGold ? 'Gold' : 'Standard' ?></div>
                <div class="sub">Réduction : <?= esc((string) $discountPercent) ?>%</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Solde disponible</div>
                <div class="metric-value"><?= esc((string) $argent) ?> Ar</div>
            </div>
        </div>

        <?php if (empty($durees)): ?>
            <div class="empty">Aucune durée disponible pour ce régime.</div>
        <?php else: ?>
            <form method="POST" action="<?= site_url('/regimes/purchase/' . $regime['id_regime']) ?>" class="stack" data-ajax-form="true">
                <?= csrf_field() ?>
                <div class="form-feedback" data-form-feedback></div>
                <div class="box">
                    <label for="id_duree_regime">Durée du régime</label>
                    <select id="id_duree_regime" name="id_duree_regime" required>
                        <option value="">-- Choisir une durée --</option>
                        <?php foreach ($durees as $duree): ?>
                            <option value="<?= esc($duree['id_duree_regime']) ?>" <?= old('id_duree_regime') === (string) $duree['id_duree_regime'] ? 'selected' : '' ?>>
                                <?= esc($duree['nb_jours']) ?> jours - <?= esc($formatPrice((float) $duree['prix_final'])) ?> Ar
                                <?php if ($isGold): ?>
                                    (au lieu de <?= esc($formatPrice((float) $duree['prix'])) ?> Ar)
                                <?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="field-error" data-field-error="id_duree_regime"></div>
                </div>

                <div class="actions">
                    <button type="submit" class="btn" data-confirm-message="Voulez-vous confirmer cet achat ?">Confirmer l'achat</button>
                    <a href="<?= site_url('/regimes') ?>" class="btn btn-secondary">Retour aux régimes</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</section>
<?= $this->endSection() ?>
