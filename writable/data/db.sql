-- ============================================================
-- Schema de la base de donnees SQLite
-- Projet : Caisse de supermarche - TD SI-IHM CodeIgniter Promo 18
-- ============================================================

PRAGMA foreign_keys = ON;

-- ---------- Table des utilisateurs (caissiers) ----------
CREATE TABLE utilisateur (
    id_utilisateur INTEGER PRIMARY KEY AUTOINCREMENT,
    login          TEXT NOT NULL UNIQUE,
    mot_de_passe   TEXT NOT NULL,
    nom            TEXT
);

-- ---------- Table des caisses ----------
CREATE TABLE caisse (
    id_caisse INTEGER PRIMARY KEY AUTOINCREMENT,
    numero    TEXT NOT NULL,
    libelle   TEXT
);

-- ---------- Table des produits ----------
CREATE TABLE produit (
    id_produit      INTEGER PRIMARY KEY AUTOINCREMENT,
    designation     TEXT NOT NULL,
    prix            REAL NOT NULL,
    quantite_stock  INTEGER NOT NULL DEFAULT 0
);

-- ---------- Table des achats (un achat = un ticket / un client) ----------
CREATE TABLE achat (
    id_achat        INTEGER PRIMARY KEY AUTOINCREMENT,
    id_caisse       INTEGER NOT NULL,
    id_utilisateur  INTEGER,
    date_achat      TEXT NOT NULL DEFAULT (datetime('now')),
    statut          TEXT NOT NULL DEFAULT 'en_cours', -- 'en_cours' ou 'cloture'
    montant_total   REAL NOT NULL DEFAULT 0,
    FOREIGN KEY (id_caisse) REFERENCES caisse(id_caisse),
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur)
);

-- ---------- Table des lignes d'achat (detail des produits achetes) ----------
CREATE TABLE ligne_achat (
    id_ligne       INTEGER PRIMARY KEY AUTOINCREMENT,
    id_achat       INTEGER NOT NULL,
    id_produit     INTEGER NOT NULL,
    quantite       INTEGER NOT NULL,
    prix_unitaire  REAL NOT NULL,   -- copie du prix au moment de l'achat
    montant        REAL NOT NULL,   -- quantite * prix_unitaire
    FOREIGN KEY (id_achat) REFERENCES achat(id_achat),
    FOREIGN KEY (id_produit) REFERENCES produit(id_produit)
);

-- ============================================================
-- Donnees de test demandees par le sujet
-- ============================================================

-- 2 caisses
INSERT INTO caisse (numero, libelle) VALUES ('1', 'Caisse 1');
INSERT INTO caisse (numero, libelle) VALUES ('2', 'Caisse 2');

-- 5 produits
INSERT INTO produit (designation, prix, quantite_stock) VALUES ('Biscuit', 1000, 50);
INSERT INTO produit (designation, prix, quantite_stock) VALUES ('Pain', 400, 100);
INSERT INTO produit (designation, prix, quantite_stock) VALUES ('Lait', 1200, 30);
INSERT INTO produit (designation, prix, quantite_stock) VALUES ('Riz (1kg)', 2500, 40);
INSERT INTO produit (designation, prix, quantite_stock) VALUES ('Savon', 800, 60);

-- 1 utilisateur de test pour l'ecran de login
-- (mot de passe en clair ici pour simplifier le TD ; en vrai, utiliser password_hash() en PHP)
INSERT INTO utilisateur (login, mot_de_passe, nom) VALUES ('admin', 'admin123', 'Caissier Test');