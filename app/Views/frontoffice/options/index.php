<?= $this->extend('frontoffice/layout') ?>

<?= $this->section('title') ?>Options<?= $this->endSection() ?>


<?= $this->section('content') ?>
<section class="stack gold-shell">
    <div class="hero">
        <div class="page-header">
            <h1>Option Gold</h1>
            <p class="sub">Debloquez une reduction permanente sur vos achats de regimes une fois les conditions remplies.</p>
        </div>
    </div>

    <?php if ($gold === null): ?>
        <div class="card">
            <h2>Gold indisponible</h2>
            <p class="sub">Aucune offre Gold n'est actuellement configuree par l'administration.</p>
        </div>
    <?php else: ?>
        <?php
            $isGold = ! empty($user['is_gold']);
            $locked = ! $isGold && ! $canUnlock;
            $insufficientBalance = ! $isGold && $canUnlock && ! $hasEnoughBalance;
        ?>
        <div class="gold-card<?= $locked ? ' is-locked' : '' ?>">
            <div class="gold-top">
                <div>
                    <div class="gold-badge">
                        <img src="<?= esc(base_url('assets/icons/crown.svg')) ?>" alt="">
                        <span><?= esc($gold['nom_option']) ?></span>
                    </div>
                    <h2 class="gold-heading">Accedez a Gold en une seule fois</h2>
                    <p class="sub">Une fois achetee, l'option Gold active votre reduction sur tous les prochains achats de regimes.</p>
                </div>
                <span class="badge <?= $isGold ? 'success' : 'neutral' ?>"><?= $isGold ? 'Deja active' : 'Option premium' ?></span>
            </div>

            <div class="gold-grid">
                <div class="gold-spec">
                    <div class="gold-spec-label">Prix unique</div>
                    <div class="gold-spec-value"><?= esc(number_format((float) $gold['prix_unique'], 2, ',', ' ')) ?> Ar</div>
                </div>
                <div class="gold-spec">
                    <div class="gold-spec-label">Reduction appliquee</div>
                    <div class="gold-spec-value">-<?= esc(rtrim(rtrim(number_format((float) $gold['reduction_pourcentage'], 2, ',', ' '), '0'), ',')) ?>%</div>
                </div>
                <div class="gold-spec">
                    <div class="gold-spec-label">Regimes necessaires</div>
                    <div class="gold-spec-value"><?= esc((string) $gold['nb_regimes_achetes']) ?></div>
                </div>
            </div>

            <?php if ($isGold): ?>
                <div class="gold-note success">Votre compte possede deja Gold. Les reductions sont maintenant appliquees automatiquement.</div>
            <?php elseif ($locked): ?>
                <div class="gold-note">
                    Vous devez acheter encore <?= esc((string) $remainingPurchases) ?> regime(s) pour pouvoir acceder au Gold.
                    Vous en avez deja achete <?= esc((string) $purchaseCount) ?> sur <?= esc((string) $requiredPurchases) ?>.
                </div>
            <?php elseif ($insufficientBalance): ?>
                <div class="gold-note">Vous avez debloque l'acces a Gold, mais votre solde actuel ne suffit pas encore pour l'acheter.</div>
            <?php else: ?>
                <div class="gold-note success">Toutes les conditions sont remplies. Vous pouvez maintenant acheter Gold.</div>
            <?php endif; ?>

            <form action="<?= site_url('options/gold/buy/' . $gold['id_option']) ?>" method="post">
                <?= csrf_field() ?>
                <div class="actions">
                    <button type="submit" class="btn" <?= ($locked || $insufficientBalance || $isGold) ? 'disabled' : '' ?>>Acheter Gold</button>
                    <a href="<?= site_url('regimes') ?>" class="btn btn-secondary">Voir les regimes</a>
                </div>
            </form>
        </div>
    <?php endif; ?>
</section>
<?= $this->endSection() ?>
