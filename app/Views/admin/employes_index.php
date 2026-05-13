<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRUD Employes</title>
</head>
<body>
    <h1>CRUD Employes</h1>

    <?php if (session()->getFlashdata('success')): ?>
        <p><?= esc(session()->getFlashdata('success')) ?></p>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <p><?= esc(session()->getFlashdata('error')) ?></p>
    <?php endif; ?>

    <p>
        <a href="<?= site_url('/admin/dashboard') ?>">Dashboard</a> |
        <a href="<?= site_url('/admin/employes/nouveau') ?>">Nouvel employe</a> |
        <a href="<?= site_url('/deconnexion') ?>">Deconnexion</a>
    </p>

    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
        <tr>
            <th>ID</th>
            <th>Nom</th>
            <th>Prenom</th>
            <th>Email</th>
            <th>Role</th>
            <th>Departement</th>
            <th>Actif</th>
            <th>Actions</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach (($users ?? []) as $u): ?>
            <tr>
                <td><?= (int) $u['id'] ?></td>
                <td><?= esc($u['nom']) ?></td>
                <td><?= esc($u['prenom']) ?></td>
                <td><?= esc($u['email']) ?></td>
                <td><?= esc($u['role']) ?></td>
                <td><?= esc($u['department_nom'] ?? '-') ?></td>
                <td><?= (int) ($u['actif'] ?? 0) === 1 ? 'Oui' : 'Non' ?></td>
                <td>
                    <a href="<?= site_url('/admin/employes/' . $u['id']) ?>">Modifier</a>
                    <?php if ((int) ($u['actif'] ?? 0) === 1): ?>
                        | <a href="<?= site_url('/admin/employes/' . $u['id'] . '/desactiver') ?>">Desactiver</a>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
