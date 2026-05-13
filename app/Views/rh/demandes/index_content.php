<div class="app-wrap">
  <aside class="sidebar">
    <div class="sidebar-brand">
      <div class="sidebar-logo-icon"><i class="bi bi-person-check"></i></div>
      <div class="sidebar-brand-name">TechMada RH<span>Espace responsable</span></div>
    </div>
    <div class="sidebar-section">Menu</div>
    <ul class="sidebar-nav">
      <li><a href="/rh/dashboard"><i class="bi bi-grid-1x2"></i> Tableau de bord</a></li>
      <li><a href="/rh/demandes" class="active"><i class="bi bi-inbox"></i> Demandes à traiter <span class="nav-badge alert"><?= esc($stats['en_attente'] ?? 0) ?></span></a></li>
      <li><a href="/rh/soldes"><i class="bi bi-people"></i> Soldes employés</a></li>
    </ul>
    <div class="sidebar-user">
      <div class="s-user-row">
        <div class="avatar av-blue"><?php echo strtoupper(substr(session('user_prenom') ?? 'RH',0,1) . substr(session('user_nom') ?? '',0,1)) ?></div>
        <div><div class="user-name"><?php echo esc(session('user_prenom') . ' ' . session('user_nom')) ?></div><div class="user-role">Responsable RH</div></div>
      </div>
    </div>
  </aside>

  <div class="main">
    <div class="topbar">
      <div>
        <div class="topbar-title">Demandes à traiter</div>
        <div class="topbar-breadcrumb"><a href="/rh/dashboard">Accueil</a> <i class="bi bi-chevron-right"></i> Demandes</div>
      </div>
    </div>

    <div class="content">

      <?php if (session()->getFlashdata('success')): ?>
        <div class="flash flash-success"><?= esc(session()->getFlashdata('success')) ?></div>
      <?php endif; ?>

      <div class="data-card">
        <div class="data-card-head"><h3>Toutes les demandes</h3></div>
        <table class="tbl">
          <thead>
            <tr><th>Employé</th><th>Type</th><th>Période</th><th>Durée</th><th>Statut</th><th>Actions</th></tr>
          </thead>
          <tbody>
            <?php foreach ($demandes as $d): ?>
              <tr>
                <td><?= esc($d['emp_prenom'] . ' ' . $d['emp_nom']) ?> <div class="td-muted" style="font-size:.75rem"><?= esc($d['dept_nom']) ?></div></td>
                <td><span class="type-badge"><?= esc($d['type_nom']) ?></span></td>
                <td class="td-muted"><?= esc($d['date_debut']) ?> → <?= esc($d['date_fin']) ?></td>
                <td class="td-mono"><?= esc($d['nb_jours']) ?> j</td>
                <td><span class="statut s-<?= esc($d['statut']) ?>"><?= esc($d['statut']) ?></span></td>
                <td>
                  <div class="action-btns">
                    <a href="/rh/demandes/<?= $d['id'] ?>" class="btn-sm btn-view"><i class="bi bi-eye"></i> Voir</a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    </div>
    <div class="footer-app"><i class="bi bi-c-circle"></i> <?= date('Y') ?> <span>TechMada RH</span></div>
  </div>
</div>
