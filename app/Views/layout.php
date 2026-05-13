<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'TechMada RH — Gestion des Congés') ?></title>
    
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600;700&family=DM+Sans:wght@300;400;500&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="<?= base_url('assets/css/variables.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/auth.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/layout.css') ?>">
    <link rel="stylesheet" href="<?= base_url('assets/css/components.css') ?>">
    
    <style>
        /* Ajustements spécifiques */
        body { margin: 0; padding: 0; }
        a { text-decoration: none; }
        button { cursor: pointer; }
    </style>
</head>
<body>
    <div class="app-wrap">
        <!-- SIDEBAR -->
        <?php if(session()->has('user_id')): ?>
        <aside class="sidebar">
            <!-- Brand -->
            <div class="sidebar-brand">
                <div class="sidebar-logo-icon">
                    <i class="bi bi-calendar-heart"></i>
                </div>
                <div class="sidebar-brand-name">
                    TechMada RH
                    <span>Gestion Congés</span>
                </div>
            </div>
            
            <!-- Navigation -->
            <nav>
                <!-- Section: Général -->
                <div class="sidebar-section">Général</div>
                <ul class="sidebar-nav">
                    <li>
                        <a href="<?= base_url('employe/dashboard') ?>" 
                           class="<?= uri_string() == 'employe/dashboard' ? 'active' : '' ?>">
                            <i class="bi bi-house-door"></i>
                            Accueil
                        </a>
                    </li>
                </ul>
                
                <!-- Section: Employé -->
                <?php if(in_array(session('user_role'), ['employe', 'rh', 'admin'])): ?>
                <div class="sidebar-section">Employé</div>
                <ul class="sidebar-nav">
                    <li>
                        <a href="<?= base_url('employe/conges') ?>"
                           class="<?= str_starts_with(uri_string(), 'employe/conges') ? 'active' : '' ?>">
                            <i class="bi bi-calendar-check"></i>
                            Mes Congés
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('employe/soldes') ?>"
                           class="<?= uri_string() == 'employe/soldes' ? 'active' : '' ?>">
                            <i class="bi bi-pie-chart"></i>
                            Soldes
                        </a>
                    </li>
                </ul>
                <?php endif; ?>
                
                <!-- Section: RH -->
                <?php if(in_array(session('user_role'), ['rh', 'admin'])): ?>
                <div class="sidebar-section">RH</div>
                <ul class="sidebar-nav">
                    <li>
                        <a href="<?= base_url('rh/demandes') ?>"
                           class="<?= uri_string() == 'rh/demandes' ? 'active' : '' ?>">
                            <i class="bi bi-inbox"></i>
                            Demandes en Attente
                            <?php 
                            // Vous pouvez ajouter un badge de comptage ici
                            // <span class="nav-badge alert">5</span>
                            ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('rh/employes') ?>"
                           class="<?= uri_string() == 'rh/employes' ? 'active' : '' ?>">
                            <i class="bi bi-people"></i>
                            Employés
                        </a>
                    </li>
                </ul>
                <?php endif; ?>
                
                <!-- Section: Admin -->
                <?php if(session('user_role') === 'admin'): ?>
                <div class="sidebar-section">Administration</div>
                <ul class="sidebar-nav">
                    <li>
                        <a href="<?= base_url('admin/employes') ?>"
                           class="<?= uri_string() == 'admin/employes' ? 'active' : '' ?>">
                            <i class="bi bi-person-gear"></i>
                            Gestion Employés
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('admin/departements') ?>"
                           class="<?= uri_string() == 'admin/departements' ? 'active' : '' ?>">
                            <i class="bi bi-diagram-3"></i>
                            Départements
                        </a>
                    </li>
                    <li>
                        <a href="<?= base_url('admin/types-conges') ?>"
                           class="<?= uri_string() == 'admin/types-conges' ? 'active' : '' ?>">
                            <i class="bi bi-list-check"></i>
                            Types de Congés
                        </a>
                    </li>
                </ul>
                <?php endif; ?>
            </nav>
            
            <!-- User Info -->
            <div class="sidebar-user">
                <div class="s-user-row" onclick="document.location='<?= base_url('logout') ?>'">
                    <div class="avatar av-green">
                        <?= strtoupper(substr(session('user_name'), 0, 2)) ?>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div class="user-name"><?= esc(session('user_name')) ?></div>
                        <div class="user-role"><?= esc(session('user_role')) ?></div>
                    </div>
                </div>
            </div>
        </aside>
        <?php endif; ?>
        
        <!-- MAIN AREA -->
        <main class="main">
            <?php if(session()->has('user_id')): ?>
            <!-- TOPBAR -->
            <div class="topbar">
                <h1 class="topbar-title"><?= esc($title ?? 'TechMada RH') ?></h1>
                <div class="topbar-actions">
                    <a href="<?= base_url('logout') ?>" class="icon-btn" title="Déconnexion">
                        <i class="bi bi-box-arrow-right"></i>
                    </a>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- CONTENT -->
            <div class="content">
                <!-- Flash Messages -->
                <?php if(session()->has('success')): ?>
                    <div class="flash flash-success">
                        <i class="bi bi-check-circle"></i>
                        <?= session('success') ?>
                    </div>
                <?php endif; ?>
                
                <?php if(session()->has('error')): ?>
                    <div class="flash flash-error">
                        <i class="bi bi-exclamation-circle"></i>
                        <?= session('error') ?>
                    </div>
                <?php endif; ?>
                
                <?php if(session()->has('warning')): ?>
                    <div class="flash flash-warn">
                        <i class="bi bi-exclamation-triangle"></i>
                        <?= session('warning') ?>
                    </div>
                <?php endif; ?>
                
                <!-- Page Content -->
                <?= $this->renderSection('content') ?>
            </div>
        </main>
    </div>
    
    <script>
        // Logout confirmation
        function confirmLogout() {
            if(confirm('Êtes-vous sûr de vouloir vous déconnecter ?')) {
                window.location.href = '<?= base_url('logout') ?>';
            }
        }
    </script>
</body>
</html>
