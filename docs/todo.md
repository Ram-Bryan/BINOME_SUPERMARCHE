# To-Do — Caisse de supermarché (CodeIgniter + SQLite)

## Phase 0 — Socle commun (à faire ensemble, ~35mn)

- [x] Créer la base de données SQLite + les 5 tables (voir `schema.sql`) — 30mn
- [x] Exécuter les inserts (5 produits, 2 caisses, 1 caissier test) — inclus dans `schema.sql`
- [x] Initialiser le projet CodeIgniter — 5mn
- [x] Se mettre d'accord sur : noms des clés de session (`user_id`, `caisse_id`, `panier`), arborescence des vues, config de la connexion SQLite dans `app/Config/Database.php`

## Étudiant A — Authentification, navigation & session (~95mn)

- [x] Créer le template à partir du fichier fourni — 25mn
- [x] Créer l'écran de login (formulaire + vérification login/mot de passe contre la table `caissier`) — 25mn
- [x] Créer l'écran d'accueil « Choix de la caisse » (liste déroulante des caisses + bouton Valider) — 20mn
- [x] Stocker en session le caissier connecté et la caisse choisie, puis afficher au-dessus du menu sur les pages suivantes — 25mn

## Étudiant B — Saisie des achats & clôture (~130mn)

- [ ] Page de saisie des achats — partie haute : liste déroulante des produits + champ quantité + bouton Valider, qui ajoute une ligne au panier (en session) — 60mn
- [ ] Page de saisie des achats — partie basse : tableau récapitulatif (Produit / Prix Unit / Qté / Montant) + ligne Total — 45mn
- [ ] Bouton « Clôturer achat » : enregistrer l'achat et ses lignes en base, décrémenter le stock, vider le panier pour le client suivant — 25mn
- [ ] Export

## Intégration finale (ensemble)

- [ ] Vérifier l'enchaînement complet : Login → Choix caisse → Saisie achats → Clôture → retour saisie vide
- [ ] Vérifier que changer de caisse ne mélange pas les paniers entre caissiers
- [ ] Relecture croisée du code de l'autre avant de rendre

## ✅ Vérification de couverture des fonctionnalités du sujet

| Exigence du sujet | Couverte par | Statut |
|---|---|---|
| Table Produit (désignation, prix, quantité en stock) | `schema.sql` | ✅ |
| Table Caisse | `schema.sql` | ✅ |
| Table Achat | `schema.sql` (`achat` + `ligne_achat`) | ✅ |
| Insertion de 5 produits et 2 caisses | `schema.sql` | ✅ |
| Initialiser CodeIgniter | Phase 0 | ✅ |
| Template à partir du fichier fourni | Étudiant A | ✅ |
| Écran de choix de caisse (liste déroulante + Valider) | Étudiant A | ✅ |
| Affichage de la caisse choisie en session, au-dessus du menu | Étudiant A | ✅ |
| Page de saisie des achats — partie haute | Étudiant B | ✅ |
| Page de saisie des achats — partie basse + Total | Étudiant B | ✅ |
| Écran de login avant la page de choix de caisse | Étudiant A | ✅ |
| Bouton « Clôturer achat » + remise à zéro du panier | Étudiant B | ✅ |

Toutes les fonctionnalités demandées dans le sujet sont couvertes. Seul ajout par rapport à l'énoncé : la table `caissier`, nécessaire pour faire fonctionner l'écran de login (non listée explicitement dans le sujet initial, mais indispensable au Travail à faire 4.1).