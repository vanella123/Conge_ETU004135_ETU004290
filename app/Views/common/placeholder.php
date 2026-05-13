<?php $this->extend('layout'); ?>

<?php $this->section('content'); ?>

<div class="form-section">
    <h3><?= esc($title ?? 'Page') ?></h3>
    <p style="margin: 0; color: var(--muted);">
        <?= esc($message ?? 'Contenu à compléter.') ?>
    </p>
</div>

<?php $this->endSection(); ?>