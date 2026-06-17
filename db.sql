PRAGMA foreign_keys = ON;

-- ---------- Table des caissiers (caissiers) ----------

CREATE TABLE client (
    id_client  INTEGER PRIMARY KEY AUTOINCREMENT,
    nom        TEXT NOT NULL,
    telephone  TEXT,
    email      TEXT
);

CREATE TABLE caissier (
    id_caissier INTEGER PRIMARY KEY AUTOINCREMENT,
    mot_de_passe TEXT NOT NULL,
    email TEXT
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
    id_achat INTEGER PRIMARY KEY AUTOINCREMENT,
    id_client INTEGER NOT NULL,
    id_caisse INTEGER NOT NULL,
    id_caissier INTEGER,
    date_achat TEXT NOT NULL DEFAULT (datetime('now')),
    statut TEXT NOT NULL DEFAULT 'en_cours'
        CHECK (statut IN ('en_cours','cloture')),
    FOREIGN KEY (id_caisse) REFERENCES caisse(id_caisse),
    FOREIGN KEY (id_caissier) REFERENCES caissier(id_caissier),
    FOREIGN KEY (id_client) REFERENCES client(id_client) ON DELETE CASCADE
);

CREATE TABLE achat_details (
    id_detail       INTEGER PRIMARY KEY AUTOINCREMENT,
    id_achat       INTEGER NOT NULL,
    id_produit     INTEGER NOT NULL,
    quantite       INTEGER NOT NULL,
    prix_unitaire  REAL NOT NULL,
    FOREIGN KEY (id_achat) REFERENCES achat(id_achat),
    FOREIGN KEY (id_produit) REFERENCES produit(id_produit)
);

CREATE VIEW v_achat AS 
SELECT achat.*, caisse.numero AS caisse_numero, caissier.email AS caissier_email, client.nom as client_nom, client.telephone as client_telephone, client.email as client_email
FROM achat 
LEFT JOIN caisse ON caisse.id_caisse = achat.id_caisse 
LEFT JOIN caissier ON caissier.id_caissier = achat.id_caissier
LEFT JOIN client ON client.id_client = achat.id_client;

CREATE VIEW v_achat_details AS 
SELECT achat_details.*, produit.designation 
FROM achat_details 
JOIN produit ON produit.id_produit = achat_details.id_produit;

INSERT INTO caisse (numero, libelle) VALUES ('1', 'Caisse 1');
INSERT INTO caisse (numero, libelle) VALUES ('2', 'Caisse 2');

INSERT INTO produit (designation, prix, quantite_stock) VALUES ('Biscuit', 1000, 50);
INSERT INTO produit (designation, prix, quantite_stock) VALUES ('Pain', 400, 100);
INSERT INTO produit (designation, prix, quantite_stock) VALUES ('Lait', 1200, 30);
INSERT INTO produit (designation, prix, quantite_stock) VALUES ('Riz (1kg)', 2500, 40);
INSERT INTO produit (designation, prix, quantite_stock) VALUES ('Savon', 800, 60);


-- caissier123
-- caissier@gmail.com

INSERT INTO caissier (mot_de_passe, email) VALUES ('$2y$10$5ggM0DclsFQ9nv0xZhVmv.Yn.xBh7xA6NkQff0zbE/eWRgujvssfS', 'caissier@gmail.com');
