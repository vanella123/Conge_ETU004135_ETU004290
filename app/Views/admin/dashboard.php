<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
</head>
<body>
    <h1>Dashboard Admin</h1>

    <p><a href="<?= site_url('/admin/employes') ?>">Gerer les employes</a></p>
    <p><a href="<?= site_url('/deconnexion') ?>">Deconnexion</a></p>

    <h2>Statistiques</h2>
    <ul>
        <li>Total utilisateurs: <?= (int) ($totalUsers ?? 0) ?></li>
        <li>Total employes: <?= (int) ($totalEmployes ?? 0) ?></li>
        <li>Total RH: <?= (int) ($totalRh ?? 0) ?></li>
    </ul>

    <h3>Demandes de conge</h3>
    <ul>
        <li>En attente: <?= (int) (($demandes['en_attente'] ?? 0)) ?></li>
        <li>Approuvees: <?= (int) (($demandes['approuvee'] ?? 0)) ?></li>
        <li>Refusees: <?= (int) (($demandes['refusee'] ?? 0)) ?></li>
        <li>Annulees: <?= (int) (($demandes['annulee'] ?? 0)) ?></li>
    </ul>
</body>
</html>
