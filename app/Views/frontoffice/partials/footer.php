<?php $isLoggedIn = (bool) session()->get('is_logged_in'); ?>

<footer class="fo-footer">
    <div class="fo-footer-inner">
        <div>
            <h4>Projet Régime</h4>
            <p>Votre partenaire santé et nutrition depuis 2026.</p>
        </div>
        <div>
            <h4>Liens utiles</h4>
            <?php if ($isLoggedIn): ?>
                <a href="<?= esc(site_url('dashboard')) ?>">Dashboard</a>
                <a href="<?= esc(site_url('profile')) ?>">Profil</a>
                <a href="<?= esc(site_url('regimes')) ?>">Régimes</a>
                <a href="<?= esc(site_url('mes-regimes')) ?>">Mes régimes</a>
                <a href="<?= esc(site_url('options')) ?>">Options</a>
                <a href="<?= esc(site_url('promo')) ?>">Code promo</a>
                <a href="<?= esc(site_url('transactions')) ?>">Transactions</a>
                <a class="link-danger" href="<?= esc(site_url('logout')) ?>">Déconnexion</a>
            <?php else: ?>
                <a href="<?= esc(site_url('/')) ?>">Accueil</a>
                <a href="<?= esc(site_url('login')) ?>">Connexion</a>
                <a href="<?= esc(site_url('register')) ?>">Inscription</a>
                <a href="<?= esc(site_url('admin/login')) ?>">Admin</a>
            <?php endif; ?>
        </div>
        <div>
            <h4>Contact</h4>
            <p>contact@projet-regime.mg</p>
        </div>
    </div>
    <div class="fo-footer-bottom">
        <p>© <?= date('Y') ?> Projet Régime. Tous droits réservés.</p>
    </div>
</footer>
