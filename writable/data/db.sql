PRAGMA foreign_keys = ON;

-- ---------- Table des utilisateurs (caissiers) ----------
CREATE TABLE utilisateur (
    id_utilisateur INTEGER PRIMARY KEY AUTOINCREMENT,
    mot_de_passe TEXT NOT NULL,
    nom TEXT
);

CREATE TABLE caisse (
    id_caisse INTEGER PRIMARY KEY AUTOINCREMENT,
    numero    TEXT NOT NULL,
    libelle   TEXT
);

CREATE TABLE produit (
    id_produit      INTEGER PRIMARY KEY AUTOINCREMENT,
    designation     TEXT NOT NULL,
    prix            REAL NOT NULL,
    quantite_stock  INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE achat (
    id_achat        INTEGER PRIMARY KEY AUTOINCREMENT,
    id_caisse       INTEGER NOT NULL,
    id_utilisateur  INTEGER,
    date_achat      TEXT NOT NULL DEFAULT (datetime('now')),
    statut TEXT NOT NULL DEFAULT 'en_cours',
    FOREIGN KEY (id_caisse) REFERENCES caisse(id_caisse),
    FOREIGN KEY (id_utilisateur) REFERENCES utilisateur(id_utilisateur)
);

CREATE TABLE achat_details (
    id_ligne       INTEGER PRIMARY KEY AUTOINCREMENT,
    id_achat       INTEGER NOT NULL,
    id_produit     INTEGER NOT NULL,
    quantite       INTEGER NOT NULL,
    prix_unitaire  REAL NOT NULL,
    FOREIGN KEY (id_achat) REFERENCES achat(id_achat),
    FOREIGN KEY (id_produit) REFERENCES produit(id_produit)
);

INSERT INTO caisse (numero, libelle) VALUES ('1', 'Caisse 1');
INSERT INTO caisse (numero, libelle) VALUES ('2', 'Caisse 2');

INSERT INTO produit (designation, prix, quantite_stock) VALUES ('Biscuit', 1000, 50);
INSERT INTO produit (designation, prix, quantite_stock) VALUES ('Pain', 400, 100);
INSERT INTO produit (designation, prix, quantite_stock) VALUES ('Lait', 1200, 30);
INSERT INTO produit (designation, prix, quantite_stock) VALUES ('Riz (1kg)', 2500, 40);
INSERT INTO produit (designation, prix, quantite_stock) VALUES ('Savon', 800, 60);

INSERT INTO utilisateur (mot_de_passe, nom) VALUES ('admin123', 'Caissier Test');