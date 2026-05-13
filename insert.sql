
-- ============================================================
--  UTILISATEURS PAR DÉFAUT (mot de passe : Password123!)
--  hash bcrypt généré avec cost=12
-- ============================================================

-- Admin
INSERT OR IGNORE INTO users (nom, prenom, email, password_hash, role, department_id, date_embauche) VALUES
    ('Rakoto',  'Admin',  'admin@conges.mg',
     '$2y$12$KIx9p0ViwxRhfMGDpvz1.OUTjZbLGFVXMrZJxWEF0hNwZ8gBNkJ0u',
     'admin', 5, '2020-01-01');

-- Responsable RH
INSERT OR IGNORE INTO users (nom, prenom, email, password_hash, role, department_id, date_embauche) VALUES
    ('Rabe',    'Marie',  'rh@conges.mg',
     '$2y$12$KIx9p0ViwxRhfMGDpvz1.OUTjZbLGFVXMrZJxWEF0hNwZ8gBNkJ0u',
     'rh', 2, '2021-03-15');

-- Employés
INSERT OR IGNORE INTO users (nom, prenom, email, password_hash, role, department_id, date_embauche) VALUES
    ('Razafy',  'Jean',   'jean@conges.mg',
     '$2y$12$KIx9p0ViwxRhfMGDpvz1.OUTjZbLGFVXMrZJxWEF0hNwZ8gBNkJ0u',
     'employe', 1, '2022-06-01'),
    ('Rasoafy', 'Hanta',  'hanta@conges.mg',
     '$2y$12$KIx9p0ViwxRhfMGDpvz1.OUTjZbLGFVXMrZJxWEF0hNwZ8gBNkJ0u',
     'employe', 3, '2023-01-10');

-- Soldes initiaux 2025 pour Jean
INSERT OR IGNORE INTO leave_balances (user_id, leave_type_id, annee, jours_attribues, jours_pris)
SELECT u.id, lt.id, 2025, lt.jours_annuels, 0
FROM users u, leave_types lt
WHERE u.email = 'jean@conges.mg';

-- Soldes initiaux 2025 pour Hanta
INSERT OR IGNORE INTO leave_balances (user_id, leave_type_id, annee, jours_attribues, jours_pris)
SELECT u.id, lt.id, 2025, lt.jours_annuels, 0
FROM users u, leave_types lt
WHERE u.email = 'hanta@conges.mg';