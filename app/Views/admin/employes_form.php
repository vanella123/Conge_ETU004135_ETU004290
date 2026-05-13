<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle ?? 'Formulaire employe') ?></title>
</head>
<body>
    <h1><?= esc($pageTitle ?? 'Formulaire employe') ?></h1>

    <?php if (session()->getFlashdata('error')): ?>
        <p><?= esc(session()->getFlashdata('error')) ?></p>
    <?php endif; ?>

    <?php $errors = session('errors') ?? []; ?>
    <?php if (! empty($errors)): ?>
        <ul>
            <?php foreach ($errors as $e): ?>
                <li><?= esc($e) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <p><a href="<?= site_url('/admin/employes') ?>">Retour a la liste</a></p>

    <form method="post" action="<?= esc($formAction) ?>">
        <?= csrf_field() ?>

        <p>
            <label>Nom</label><br>
            <input type="text" name="nom" value="<?= esc(old('nom', $user['nom'] ?? '')) ?>" required>
        </p>

        <p>
            <label>Prenom</label><br>
            <input type="text" name="prenom" value="<?= esc(old('prenom', $user['prenom'] ?? '')) ?>" required>
        </p>

        <p>
            <label>Email</label><br>
            <input type="email" name="email" value="<?= esc(old('email', $user['email'] ?? '')) ?>" required>
        </p>

        <p>
            <label>Role</label><br>
            <select name="role" required>
                <?php $roleVal = old('role', $user['role'] ?? 'employe'); ?>
                <option value="employe" <?= $roleVal === 'employe' ? 'selected' : '' ?>>Employe</option>
                <option value="rh" <?= $roleVal === 'rh' ? 'selected' : '' ?>>RH</option>
                <option value="admin" <?= $roleVal === 'admin' ? 'selected' : '' ?>>Admin</option>
            </select>
        </p>

        <p>
            <label>Departement</label><br>
            <?php $depVal = old('department_id', $user['department_id'] ?? ''); ?>
            <select name="department_id">
                <option value="">-- Aucun --</option>
                <?php foreach (($departments ?? []) as $d): ?>
                    <option value="<?= (int) $d['id'] ?>" <?= (string) $depVal === (string) $d['id'] ? 'selected' : '' ?>>
                        <?= esc($d['nom']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </p>

        <p>
            <label>Date embauche</label><br>
            <input type="date" name="date_embauche" value="<?= esc(old('date_embauche', $user['date_embauche'] ?? '')) ?>">
        </p>

        <p>
            <label>Mot de passe <?= isset($user) && $user ? '(laisser vide pour ne pas changer)' : '' ?></label><br>
            <input type="password" name="password" <?= isset($user) && $user ? '' : 'required' ?>>
        </p>

        <p>
            <?php $actifVal = old('actif', isset($user) ? (string) ($user['actif'] ?? '1') : '1'); ?>
            <label>
                <input type="checkbox" name="actif" value="1" <?= $actifVal === '1' ? 'checked' : '' ?>> Actif
            </label>
        </p>

        <button type="submit">Enregistrer</button>
    </form>
</body>
</html>
