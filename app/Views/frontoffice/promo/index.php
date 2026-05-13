<?= $this->extend('frontoffice/layout') ?>

<?php
    $statusStyles = [
        'en_attente' => ['label' => 'En attente', 'bg' => 'var(--warning-bg)', 'color' => 'var(--warning-text)'],
        'accepte' => ['label' => 'Accepte', 'bg' => 'var(--success-bg)', 'color' => 'var(--success-text)'],
        'refuse' => ['label' => 'Refuse', 'bg' => 'var(--error-bg)', 'color' => 'var(--error-text)'],
    ];
?>

<?= $this->section('title') ?>Code promo<?= $this->endSection() ?>
<?= $this->section('head') ?>
    
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<section class="stack">
    <div class="card promo-card">
        <div class="promo-header">
            <div>
                <h2>Code promo</h2>
                <p class="sub">Votre demande passe d'abord par validation admin.</p>
            </div>
        </div>

        <div class="promo-balance">
            Solde actuel : <strong><?= esc(number_format((float) $argent, 2, ',', ' ')) ?> Ar</strong>
        </div>

        <form method="POST" action="<?= site_url('/promo') ?>" class="stack" data-ajax-form="true">
            <?= csrf_field() ?>
            <div class="form-feedback" data-form-feedback></div>
            <div>
                <label for="code_promo">Code promo</label>
                <input type="text" id="code_promo" name="code_promo" minlength="3" maxlength="30" value="<?= esc(old('code_promo')) ?>" required>
                <div class="field-error" data-field-error="code_promo"></div>
            </div>
            <div class="actions">
                <button type="submit" class="btn">Envoyer la demande</button>
                <a href="<?= site_url('/dashboard') ?>" class="btn btn-secondary">Retour au dashboard</a>
            </div>
        </form>
    </div>

    <?php if (! empty($demandes)): ?>
        <div class="card">
            <div class="section-title">
                <div>
                    <h2>Mes demandes recentes</h2>
                    <p class="sub">Suivez l'etat des codes deja envoyes a l'administration.</p>
                </div>
            </div>

            <div class="history-list">
                <?php foreach ($demandes as $demande): ?>
                    <?php $status = $statusStyles[$demande['statut'] ?? 'en_attente'] ?? $statusStyles['en_attente']; ?>
                    <div class="history-item">
                        <div>
                            <strong><?= esc($demande['code_saisi'] ?? '') ?></strong>
                            <div class="history-meta">
                                Envoyee le <?= esc((string) ($demande['date_demande'] ?? '')) ?>
                            </div>
                        </div>
                        <span class="history-status" style="background: <?= esc($status['bg']) ?>; color: <?= esc($status['color']) ?>;">
                            <?= esc($status['label']) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</section>
<?= $this->endSection() ?>
