-- ============================================================
--  SYSTÈME DE GESTION DES CONGÉS — Base SQLite3
--  Schéma complet : tables + données initiales
-- ============================================================

PRAGMA foreign_keys = ON;
PRAGMA journal_mode = WAL;

-- ------------------------------------------------------------
-- TABLE : departments
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS departments (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    nom        TEXT NOT NULL UNIQUE,
    description TEXT,
    created_at TEXT DEFAULT (datetime('now')),
    updated_at TEXT DEFAULT (datetime('now'))
);

-- ------------------------------------------------------------
-- TABLE : users  (employés, RH, administrateurs)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    nom            TEXT NOT NULL,
    prenom         TEXT NOT NULL,
    email          TEXT NOT NULL UNIQUE,
    password_hash  TEXT NOT NULL,
    role           TEXT NOT NULL CHECK(role IN ('employe','rh','admin')),
    department_id  INTEGER REFERENCES departments(id) ON DELETE SET NULL,
    date_embauche  TEXT,
    actif          INTEGER NOT NULL DEFAULT 1,  -- 1=actif, 0=inactif
    created_at     TEXT DEFAULT (datetime('now')),
    updated_at     TEXT DEFAULT (datetime('now'))
);

CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_role  ON users(role);

-- ------------------------------------------------------------
-- TABLE : leave_types  (types de congé)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS leave_types (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    nom             TEXT NOT NULL UNIQUE,
    jours_annuels   INTEGER NOT NULL DEFAULT 0,
    deductible      INTEGER NOT NULL DEFAULT 1,  -- 1=oui, 0=non
    description     TEXT,
    created_at      TEXT DEFAULT (datetime('now'))
);

-- ------------------------------------------------------------
-- TABLE : leave_balances  (soldes par employé/type/année)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS leave_balances (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id         INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    leave_type_id   INTEGER NOT NULL REFERENCES leave_types(id) ON DELETE CASCADE,
    annee           INTEGER NOT NULL,
    jours_attribues INTEGER NOT NULL DEFAULT 0,
    jours_pris      INTEGER NOT NULL DEFAULT 0,
    -- jours_restants = jours_attribues - jours_pris (calculé, jamais stocké)
    created_at      TEXT DEFAULT (datetime('now')),
    updated_at      TEXT DEFAULT (datetime('now')),
    UNIQUE(user_id, leave_type_id, annee)
);

-- ------------------------------------------------------------
-- TABLE : leave_requests  (demandes de congé)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS leave_requests (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id         INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    leave_type_id   INTEGER NOT NULL REFERENCES leave_types(id),
    date_debut      TEXT NOT NULL,
    date_fin        TEXT NOT NULL,
    nb_jours        INTEGER NOT NULL,
    motif           TEXT,
    statut          TEXT NOT NULL DEFAULT 'en_attente'
                    CHECK(statut IN ('en_attente','approuvee','refusee','annulee')),
    commentaire_rh  TEXT,
    traite_par      INTEGER REFERENCES users(id) ON DELETE SET NULL,
    traite_le       TEXT,
    created_at      TEXT DEFAULT (datetime('now')),
    updated_at      TEXT DEFAULT (datetime('now'))
);

CREATE INDEX IF NOT EXISTS idx_lr_user_id  ON leave_requests(user_id);
CREATE INDEX IF NOT EXISTS idx_lr_statut   ON leave_requests(statut);

-- ------------------------------------------------------------
-- TABLE : sessions  (sessions CI4 avec driver database)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS ci_sessions (
    id         TEXT PRIMARY KEY,
    ip_address TEXT NOT NULL DEFAULT '127.0.0.1',
    timestamp  INTEGER NOT NULL DEFAULT 0,
    data       BLOB NOT NULL DEFAULT ''
);

CREATE INDEX IF NOT EXISTS idx_sessions_timestamp ON ci_sessions(timestamp);

-- ============================================================
--  DONNÉES INITIALES (SEED)
-- ============================================================

-- Départements
INSERT OR IGNORE INTO departments (nom, description) VALUES
    ('Informatique',   'Département technique et développement'),
    ('Ressources Humaines', 'Gestion du personnel'),
    ('Finance',        'Comptabilité et finances'),
    ('Marketing',      'Communication et marketing'),
    ('Direction',      'Direction générale');

-- Types de congé
INSERT OR IGNORE INTO leave_types (nom, jours_annuels, deductible, description) VALUES
    ('Congé annuel',       30, 1, 'Congé payé annuel'),
    ('Congé maladie',      15, 1, 'Arrêt maladie avec justificatif'),
    ('Congé exceptionnel',  5, 1, 'Évènements familiaux'),
    ('Congé maternité',    98, 1, 'Congé maternité légal'),
    ('Permission spéciale', 3, 0, 'Permission non déductible du solde');
