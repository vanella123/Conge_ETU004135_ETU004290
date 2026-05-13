-- =========================================================
-- Base de données : Regime_DB
-- =========================================================

DROP DATABASE IF EXISTS Regime_DB;
CREATE DATABASE Regime_DB;
USE Regime_DB;

-- =========================================================
-- TABLE OBJECTIF
-- =========================================================

CREATE TABLE objectif (
    id_objectif INT AUTO_INCREMENT PRIMARY KEY,
    label_objectif VARCHAR(100) NOT NULL
);

-- =========================================================
-- TABLE UTILISATEUR
-- =========================================================
CREATE TABLE utilisateur (
    id_utilisateur INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    genre ENUM('Homme', 'Femme', 'Autre') NOT NULL DEFAULT 'Autre' ,
    taille_cm DECIMAL(5,2),
    poids_kg DECIMAL(5,2),
    poids_objectif DECIMAL(5,2) NULL, 
    date_naissance DATE,
    id_objectif INT,
    is_gold BOOLEAN DEFAULT FALSE,
    argent DECIMAL(10,2) DEFAULT 0,
    role_utilisateur ENUM('admin', 'client') DEFAULT 'client',

    FOREIGN KEY (id_objectif)
        REFERENCES objectif(id_objectif)
);

-- =========================================================
-- TABLE REGIME
-- =========================================================

CREATE TABLE regime (
    id_regime INT AUTO_INCREMENT PRIMARY KEY,
    nom_regime VARCHAR(100) NOT NULL,
    variation_mensuelle_kg DECIMAL(5,2) NOT NULL,
    pourcentage_viande DECIMAL(5,2) DEFAULT 0,
    pourcentage_poisson DECIMAL(5,2) DEFAULT 0,
    pourcentage_volaille DECIMAL(5,2) DEFAULT 0
);

-- =========================================================
-- TABLE ACTIVITE SPORTIVE
-- =========================================================

CREATE TABLE activite_sportive (
    id_activite INT AUTO_INCREMENT PRIMARY KEY,
    label_activite VARCHAR(100) NOT NULL,
    nb_par_semaine INT NOT NULL
);

-- =========================================================
-- TABLE REGIME_ACTIVITE
-- =========================================================

CREATE TABLE regime_activite (
    id_regime_activite INT AUTO_INCREMENT PRIMARY KEY,
    id_regime INT NOT NULL,
    id_activite INT NOT NULL,

    FOREIGN KEY (id_regime)
        REFERENCES regime(id_regime),

    FOREIGN KEY (id_activite)
        REFERENCES activite_sportive(id_activite)
);

-- =========================================================
-- TABLE DUREE_REGIME
-- =========================================================

CREATE TABLE duree_regime (
    id_duree_regime INT AUTO_INCREMENT PRIMARY KEY,
    id_regime INT NOT NULL,
    nb_jours INT NOT NULL,
    prix DECIMAL(10,2) NOT NULL,

    FOREIGN KEY (id_regime)
        REFERENCES regime(id_regime)
);

-- =========================================================
-- TABLE DUREE_REGIME_PRIX
-- =========================================================

CREATE TABLE duree_regime_prix (
    id_duree_regime_prix INT AUTO_INCREMENT PRIMARY KEY,
    id_duree_regime INT NOT NULL,
    prix DECIMAL(10,2) NOT NULL,
    date_prix DATE NOT NULL,

    FOREIGN KEY (id_duree_regime)
        REFERENCES duree_regime(id_duree_regime)
);

-- =========================================================
-- TABLE COMMANDE
-- =========================================================

CREATE TABLE commande (
    id_commande INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    id_regime INT NOT NULL,
    id_duree_regime INT NOT NULL,
    date_achat DATETIME DEFAULT CURRENT_TIMESTAMP,
    montant_paye DECIMAL(10,2) NOT NULL,

    FOREIGN KEY (id_utilisateur)
        REFERENCES utilisateur(id_utilisateur),

    FOREIGN KEY (id_regime)
        REFERENCES regime(id_regime),

    FOREIGN KEY (id_duree_regime)
        REFERENCES duree_regime(id_duree_regime)
);

-- =========================================================
-- TABLE CODE_PROMO
-- =========================================================

CREATE TABLE code_promo (
    id_code INT AUTO_INCREMENT PRIMARY KEY,
    montant DECIMAL(10,2) NOT NULL,
    code VARCHAR(50) UNIQUE NOT NULL,
    deja_utilise BOOLEAN DEFAULT FALSE,
    id_utilisateur_utilisation INT NULL,

    FOREIGN KEY (id_utilisateur_utilisation)
        REFERENCES utilisateur(id_utilisateur)
);

-- =========================================================
-- TABLE DEMANDE_CODE_PROMO
-- =========================================================

CREATE TABLE demande_code_promo (
    id_demande_code_promo INT AUTO_INCREMENT PRIMARY KEY,
    id_utilisateur INT NOT NULL,
    code_saisi VARCHAR(50) NOT NULL,
    statut VARCHAR(20) NOT NULL DEFAULT 'en_attente',
    id_admin_traitement INT NULL,
    note_admin VARCHAR(255) NULL,
    date_demande DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    date_traitement DATETIME NULL,

    FOREIGN KEY (id_utilisateur)
        REFERENCES utilisateur(id_utilisateur),

    FOREIGN KEY (id_admin_traitement)
        REFERENCES utilisateur(id_utilisateur)
);

-- =========================================================
-- TABLE OPTION
-- =========================================================

CREATE TABLE option (
    id_option INT AUTO_INCREMENT PRIMARY KEY,
    nom_option VARCHAR(50) UNIQUE NOT NULL,
    nb_regimes_achetes INT NOT NULL,
    prix_unique DECIMAL(10,2) NOT NULL,
    reduction_pourcentage DECIMAL(5,2) NOT NULL
);

-- =========================================================
-- TABLE OPTION_HISTORIQUE
-- =========================================================

CREATE TABLE option_historique (
    id_option_historique INT AUTO_INCREMENT PRIMARY KEY,
    id_option INT NOT NULL,
    prix DECIMAL(10,2) NOT NULL,
    reduction_pourcentage DECIMAL(5,2) NOT NULL,
    nb_regimes_achetes INT NOT NULL,
    date_debut DATE NOT NULL,

    FOREIGN KEY (id_option)
        REFERENCES option(id_option)
);
-- =========================================================
-- TABLE IMC
-- =========================================================

CREATE TABLE imc (
    id_imc INT AUTO_INCREMENT PRIMARY KEY,
    label_imc VARCHAR(50) NOT NULL,
    imc_min DECIMAL(5,2) NOT NULL,
    imc_max DECIMAL(5,2) NOT NULL
);

-- =========================================================
-- INSERTIONS STANDARD OMS
-- =========================================================

INSERT INTO imc (label_imc, imc_min, imc_max) VALUES
('Insuffisance pondérale sévère', 0.00, 16.49),
('Insuffisance pondérale modérée', 16.50, 18.49),
('Poids normal', 18.50, 24.99),
('Surpoids', 25.00, 29.99),
('Obésité modérée', 30.00, 34.99),
('Obésité sévère', 35.00, 39.99),
('Obésité morbide', 40.00, 99.99);

-- =========================================================
-- INSERTION OBJECTIFS
-- =========================================================

INSERT INTO objectif(label_objectif) VALUES
('Perte de poids'),
('Prise de masse'),
('Atteindre IMC ideal');

-- =========================================================
-- INSERTION UTILISATEURS
-- =========================================================

/*

nom/mdp

Jean/jean123
Marie/marie123
Lucas/lucas123
Sofia/sofia123
Admin/admin123

php -r "echo password_hash('lucas123', PASSWORD_DEFAULT) . PHP_EOL;"

*/
INSERT INTO utilisateur(
    nom,
    email,
    mot_de_passe,
    genre,
    taille_cm,
    poids_kg,
    poids_objectif,
    date_naissance,
    id_objectif,
    is_gold,
    argent,
    role_utilisateur
) VALUES
(
    'Jean Rakoto',
    'jean@gmail.com',
    '$2y$10$f3jDT.THIYmuaS.InxodN.IoTiPTs67tBuSpCqVya7t3Tv7hcOMam',
    'Homme',
    175,
    72,
    65,
    '1998-05-12',
    1,
    FALSE,
    100000,
    'client'
),
(
    'Marie Ranaivo',
    'marie@gmail.com',
    '$2y$10$jTZZiHEExlm8jldPFPV7T.06wzSvfYxZmtAXh14z5ICd8nSOh4jQa',
    'Femme',
    165,
    58,
    62,
    '2000-08-10',
    2,
    FALSE,
    30000,
    'client'
),
(
    'Lucas Andry',
    'lucas@gmail.com',
    '$2y$10$pyOvnFPzfqAg/6wgwwt4QOELVmWisvqS7NcSwB5Lx41bG1RIMmW2m',
    'Homme',
    180,
    98,
    75,
    '1997-11-22',
    3,
    FALSE,
    70000,
    'client'
),
(
    'Sofia Noro',
    'sofia@gmail.com',
    "$2y$10$Zo8RQVDZGrtaeTjIiOzIFugi7jBkzhTPAuucF8zvD6KK5uk9EzoCW",
    'Femme',
    170,
    65,
    60,
    '2001-01-17',
    1,
    FALSE,
    50000,
    'client'
),
(
    'Admin Principal',
    'admin@gmail.com',
    '$2y$10$0uS9UTGA/AHNvzDk1NBeH.7WsvAy0fomcXXp75j2PF7CsML8ywOQC',
    'Homme',
    178,
    75,
    NULL,
    '1995-03-15',
    NULL,
    TRUE,
    100000,
    'admin'
);
-- =========================================================
-- INSERTION REGIMES
-- =========================================================

INSERT INTO regime(
    nom_regime,
    variation_mensuelle_kg,
    pourcentage_viande,
    pourcentage_poisson,
    pourcentage_volaille
) VALUES
('Regime Keto', -5, 60, 20, 20),
('Regime Mediterraneen', -2, 30, 40, 30),
('Regime Hyperproteine', 4, 50, 20, 30),
('Regime Vegetarien', -3, 10, 10, 80),
('Regime Fitness', 1, 40, 30, 30);

-- =========================================================
-- INSERTION ACTIVITES SPORTIVES
-- =========================================================

INSERT INTO activite_sportive(
    label_activite,
    nb_par_semaine
) VALUES
('Musculation', 4),
('Running', 3),
('Natation', 2),
('Yoga', 5),
('Cyclisme', 3);

-- =========================================================
-- INSERTION REGIME_ACTIVITE
-- =========================================================

INSERT INTO regime_activite(
    id_regime,
    id_activite
) VALUES
(1,1),
(1,2),
(2,4),
(3,1),
(4,4),
(5,2);

-- =========================================================
-- INSERTION DUREE_REGIME
-- =========================================================

INSERT INTO duree_regime(
    id_regime,
    nb_jours,
    prix
) VALUES
(1,30,50000),
(1,60,90000),
(2,30,45000),
(3,30,70000),
(4,30,40000),
(5,90,120000);

-- =========================================================
-- INSERTION DUREE_REGIME_PRIX (HISTORIQUE DES PRIX)
-- =========================================================

INSERT INTO duree_regime_prix(
    id_duree_regime,
    prix,
    date_prix
) VALUES
(1,50000,'2026-01-01'),
(2,90000,'2026-01-01'),
(3,45000,'2026-01-01'),
(4,70000,'2026-01-01'),
(5,40000,'2026-01-01'),
(6,120000,'2026-01-01');

-- =========================================================
-- INSERTION CODES PROMO
-- =========================================================

INSERT INTO code_promo(
    montant,
    code,
    deja_utilise,
    id_utilisateur_utilisation
) VALUES
(5000,'CODE001',FALSE,NULL),
(10000,'CODE002',FALSE,NULL),
(7000,'CODE003',FALSE,NULL),
(15000,'CODE004',FALSE,NULL),
(20000,'CODE005',FALSE,NULL),
(2500,'CODE006',FALSE,NULL),
(3500,'CODE007',FALSE,NULL),
(4000,'CODE008',FALSE,NULL),
(4500,'CODE009',FALSE,NULL),
(6000,'CODE010',FALSE,NULL),
(8000,'CODE011',FALSE,NULL),
(9000,'CODE012',FALSE,NULL),
(3000,'CODE013',FALSE,NULL),
(11000,'CODE014',FALSE,NULL),
(130000,'CODE015',FALSE,NULL);
-- =========================================================
-- INSERTION OPTIONS
-- =========================================================

INSERT INTO option(
    nom_option,
    nb_regimes_achetes,
    prix_unique,
    reduction_pourcentage
) VALUES
('Gold', 3, 20000, 15);

-- =========================================================
-- INSERTION OPTION HISTORIQUE
-- =========================================================

INSERT INTO option_historique(
    id_option,
    prix,
    reduction_pourcentage,
    nb_regimes_achetes,
    date_debut
) VALUES
(1, 20000, 15, 3, '2026-01-01');

-- =========================================================
-- VIEWS
-- =========================================================

-- =========================================================
-- VIEW : UTILISATEURS + OBJECTIFS
-- =========================================================

CREATE VIEW v_utilisateur_objectif AS
SELECT
    u.id_utilisateur,
    u.nom,
    u.email,
    u.genre,
    u.taille_cm,
    u.poids_kg,
    u.date_naissance,
    o.label_objectif,
    u.is_gold,
    u.argent,
    u.role_utilisateur
FROM utilisateur u
JOIN objectif o
ON u.id_objectif = o.id_objectif;

-- =========================================================
-- VIEW : DETAILS REGIME
-- =========================================================

CREATE VIEW v_regime_details AS
SELECT
    r.id_regime,
    r.nom_regime,
    r.variation_mensuelle_kg,
    r.pourcentage_viande,
    r.pourcentage_poisson,
    r.pourcentage_volaille,
    a.label_activite,
    a.nb_par_semaine
FROM regime r
JOIN regime_activite ra
ON r.id_regime = ra.id_regime
JOIN activite_sportive a
ON ra.id_activite = a.id_activite;

-- =========================================================
-- VIEW : COMMANDES COMPLETES
-- =========================================================

CREATE VIEW v_commande_complete AS
SELECT
    c.id_commande,
    u.nom,
    r.nom_regime,
    d.nb_jours,
    c.date_achat,
    c.montant_paye
FROM commande c
JOIN utilisateur u
ON c.id_utilisateur = u.id_utilisateur
JOIN regime r
ON c.id_regime = r.id_regime
JOIN duree_regime d
ON c.id_duree_regime = d.id_duree_regime;

-- =========================================================
-- VIEW : CODES PROMO
-- =========================================================

CREATE VIEW v_code_promo AS
SELECT
    cp.id_code,
    cp.code,
    cp.montant,
    cp.deja_utilise,
    u.nom AS utilise_par
FROM code_promo cp
LEFT JOIN utilisateur u
ON cp.id_utilisateur_utilisation = u.id_utilisateur;

-- =========================================================
-- VIEW : PRIX REGIME
-- =========================================================

CREATE VIEW v_regime_prix AS
SELECT
    r.nom_regime,
    d.nb_jours,
    d.prix
FROM regime r
JOIN duree_regime d
ON r.id_regime = d.id_regime;

-- =========================================================
-- VIEW : REGIMES ACHETES
-- =========================================================

CREATE VIEW v_commande_regime AS
SELECT
    c.id_commande,
    c.id_utilisateur,
    c.id_regime,
    c.id_duree_regime,
    c.date_achat,
    c.montant_paye,
    r.nom_regime,
    r.variation_mensuelle_kg,
    r.pourcentage_viande,
    r.pourcentage_poisson,
    r.pourcentage_volaille,
    d.nb_jours,
    d.prix
FROM commande c
JOIN regime r
ON c.id_regime = r.id_regime
JOIN duree_regime d
ON c.id_duree_regime = d.id_duree_regime;

CREATE VIEW v_regime_duree AS
SELECT
    r.id_regime,
    r.nom_regime,
    r.variation_mensuelle_kg,
    r.pourcentage_viande,
    r.pourcentage_poisson,
    r.pourcentage_volaille,
    d.nb_jours,
    d.prix
FROM regime r
JOIN duree_regime d
ON r.id_regime = d.id_regime;

