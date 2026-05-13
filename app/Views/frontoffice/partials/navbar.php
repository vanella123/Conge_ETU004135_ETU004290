<?php
    $isLoggedIn = (bool) session()->get('is_logged_in');
    $path = trim(uri_string(), '/');
    $isActive = static function (array $prefixes) use ($path): bool {
        foreach ($prefixes as $prefix) {
            if ($prefix === '' && $path === '') return true;
            if ($prefix !== '' && ($path === $prefix || str_starts_with($path, $prefix . '/'))) return true;
        }
        return false;
    };
?>

<header class="topbar">
    <div class="topbar-inner">
        <a class="fo-brand" href="<?= esc(site_url($isLoggedIn ? 'dashboard' : '/')) ?>">PROJET RÉGIME</a>

        <button class="mobile-menu-btn" aria-label="Menu">
            <img src="<?= esc(base_url('assets/icons/menu.svg')) ?>" alt="Menu">
        </button>

        <nav class="nav">
            <?php if ($isLoggedIn): ?>
                <a class="<?= $isActive(['dashboard']) ? 'active' : '' ?>" href="<?= esc(site_url('dashboard')) ?>">Dashboard</a>
                <a class="<?= $isActive(['profile']) ? 'active' : '' ?>" href="<?= esc(site_url('profile')) ?>">Profil</a>
                <a class="<?= $isActive(['regimes']) ? 'active' : '' ?>" href="<?= esc(site_url('regimes')) ?>">Régimes</a>
                <a class="<?= $isActive(['mes-regimes']) ? 'active' : '' ?>" href="<?= esc(site_url('mes-regimes')) ?>">Mes régimes</a>
                <a class="<?= $isActive(['options']) ? 'active' : '' ?>" href="<?= esc(site_url('options')) ?>">Options</a>
                <a class="<?= $isActive(['promo']) ? 'active' : '' ?>" href="<?= esc(site_url('promo')) ?>">Code promo</a>
                <a class="<?= $isActive(['transactions']) ? 'active' : '' ?>" href="<?= esc(site_url('transactions')) ?>">Transactions</a>
                <a class="link-danger" href="<?= esc(site_url('logout')) ?>" data-confirm-message="Voulez-vous vraiment vous déconnecter ?">Déconnexion</a>
            <?php else: ?>
                <a class="<?= $isActive(['']) ? 'active' : '' ?>" href="<?= esc(site_url('/')) ?>">Accueil</a>
                <a class="<?= $isActive(['login']) ? 'active' : '' ?>" href="<?= esc(site_url('login')) ?>">Connexion</a>
                <a class="<?= $isActive(['register']) ? 'active' : '' ?>" href="<?= esc(site_url('register')) ?>">Inscription</a>
                <a class="<?= $isActive(['admin', 'admin/login']) ? 'active' : '' ?>" href="<?= esc(site_url('admin/login')) ?>">Admin</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
