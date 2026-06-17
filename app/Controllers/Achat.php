<?php

namespace App\Controllers;

use App\Models\ProduitModel;

/**
 * Achat
 *
 * Contrôleur principal de la saisie des achats.
 *
 * Responsabilités :
 *   - Afficher la page de saisie (formulaire produit + panier en session)
 *   - Ajouter un produit au panier (stocké en session)
 *   - Vider le panier (clôture — la sauvegarde BDD est pour la tâche suivante)
 *
 * Règle MVC respectée :
 *   - Tout accès BDD passe par ProduitModel (jamais ici directement)
 *   - La vue ne fait que rendre les données, aucune logique métier
 */
class Achat extends BaseController
{
    // ---------------------------------------------------------------
    // Clés de session — convenues en Phase 0 avec l'étudiant A
    // ---------------------------------------------------------------
    private const SESSION_PANIER    = 'panier';
    private const SESSION_CAISSE_ID = 'caisse_id';
    private const SESSION_USER_ID   = 'user_id';

    // ---------------------------------------------------------------
    // ACTIONS
    // ---------------------------------------------------------------

    /**
     * GET /achat/saisie
     *
     * Affiche la page de saisie des achats :
     *   - partie haute : liste déroulante des produits + stepper quantité + bouton Ajouter
     *   - partie basse : récapitulatif du panier (tableau + total)
     */
    public function saisie(): string
    {
        // Récupère les produits via le Model (jamais de requête dans le Controller)
        $produitModel = new ProduitModel();
        $produits     = $produitModel->tousLesProduits();

        $panier = session()->get(self::SESSION_PANIER) ?? [];

        // Dynamic stock adjustment for the dropdown
        foreach ($produits as &$p) {
            $id = $p['id_produit'];
            if (isset($panier[$id])) {
                $p['quantite_stock'] = max(0, $p['quantite_stock'] - $panier[$id]['quantite']);
            }
        }
        unset($p);

        // Calcule le total côté serveur (ne jamais faire confiance au client)
        $total = 0;
        foreach ($panier as $ligne) {
            $total += $ligne['montant'];
        }

        // Prépare les données à passer à la vue
        $data = [
            'titre'    => 'Saisie des achats',
            'produits' => $produits,
            'panier'   => $panier,
            'total'    => $total,
            // Messages flash éventuels (succès ajout / erreur stock)
            'flash_success' => session()->getFlashdata('success'),
            'flash_error'   => session()->getFlashdata('error'),
        ];

        // Charge la vue qui étend layout/default.php
        return view('achat/saisie', $data);
    }

    /**
     * POST /achat/ajouter
     *
     * Traite le formulaire d'ajout d'un produit au panier.
     * Le panier vit en session jusqu'à la clôture de l'achat.
     */
    public function ajouter(): \CodeIgniter\HTTP\RedirectResponse
    {
        // --- Validation de l'entrée ---
        $validation = \Config\Services::validation();

        $regles = [
            'id_produit' => [
                'label' => 'Produit',
                'rules' => 'required|is_natural_no_zero',
            ],
            'quantite' => [
                'label' => 'Quantité',
                'rules' => 'required|is_natural_no_zero|less_than_equal_to[9999]',
            ],
        ];

        if (! $this->validate($regles)) {
            // Renvoie les erreurs en flash et redirige
            session()->setFlashdata('error', implode('<br>', $this->validator->getErrors()));
            return redirect()->to(site_url('achat/saisie'));
        }

        // --- Récupère et sanitise les données POST ---
        $idProduit = (int) $this->request->getPost('id_produit');
        $quantite  = (int) $this->request->getPost('quantite');

        // --- Vérifie que le produit existe en base ---
        $produitModel = new ProduitModel();
        $produit      = $produitModel->trouverParId($idProduit);

        if ($produit === null) {
            session()->setFlashdata('error', 'Produit introuvable.');
            return redirect()->to(site_url('achat/saisie'));
        }

        // --- Vérifie le stock disponible ---
        if ($quantite > $produit['quantite_stock']) {
            session()->setFlashdata(
                'error',
                esc("Stock insuffisant pour « {$produit['designation']} ». "
                    . "Stock disponible : {$produit['quantite_stock']} unité(s).")
            );
            return redirect()->to(site_url('achat/saisie'));
        }

        // --- Ajoute ou met à jour la ligne dans le panier (session) ---
        $panier = session()->get(self::SESSION_PANIER) ?? [];

        if (isset($panier[$idProduit])) {
            // Le produit est déjà dans le panier : on incrémente la quantité
            $nouvelleQte = $panier[$idProduit]['quantite'] + $quantite;

            // Vérification du stock cumulé
            if ($nouvelleQte > $produit['quantite_stock']) {
                session()->setFlashdata(
                    'error',
                    esc("Stock insuffisant. Vous avez déjà {$panier[$idProduit]['quantite']} "
                        . "« {$produit['designation']} » dans le panier. "
                        . "Stock disponible : {$produit['quantite_stock']} unité(s).")
                );
                return redirect()->to(site_url('achat/saisie'));
            }

            $panier[$idProduit]['quantite'] = $nouvelleQte;
            $panier[$idProduit]['montant']  = $nouvelleQte * $produit['prix'];
        } else {
            // Nouveau produit dans le panier
            $panier[$idProduit] = [
                'id_produit'   => $idProduit,
                'designation'  => $produit['designation'],
                'prix_unitaire'=> $produit['prix'],
                'quantite'     => $quantite,
                'montant'      => $quantite * $produit['prix'],
            ];
        }

        // Sauvegarde le panier mis à jour en session
        session()->set(self::SESSION_PANIER, $panier);

        // Message de confirmation discret
        session()->setFlashdata(
            'success',
            esc("« {$produit['designation']} » × {$quantite} ajouté au panier.")
        );

        return redirect()->to(site_url('achat/saisie'));
    }

    /**
     * POST /achat/supprimer-ligne
     *
     * Supprime une ligne précise du panier (par id_produit).
     */
    public function supprimerLigne(): \CodeIgniter\HTTP\RedirectResponse
    {
        $idProduit = (int) $this->request->getPost('id_produit');
        $panier    = session()->get(self::SESSION_PANIER) ?? [];

        if (isset($panier[$idProduit])) {
            $designation = $panier[$idProduit]['designation'];
            unset($panier[$idProduit]);
            session()->set(self::SESSION_PANIER, $panier);
            session()->setFlashdata('success', esc("« {$designation} » retiré du panier."));
        }

        return redirect()->to(site_url('achat/saisie'));
    }

    /**
     * POST /achat/vider-panier
     *
     * Vide entièrement le panier (bouton Annuler / Clôture partielle).
     * La clôture complète (sauvegarde en BDD + décrémentation stock) sera dans une tâche suivante.
     */
    public function viderPanier(): \CodeIgniter\HTTP\RedirectResponse
    {
        session()->remove(self::SESSION_PANIER);
        session()->setFlashdata('success', 'Panier vidé. Prêt pour le client suivant.');
        return redirect()->to(site_url('achat/saisie'));
    }

    /**
     * GET /achat/liste
     *
     * Affiche la liste des achats passés.
     */
    public function liste()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();
        
        $builder = $db->table('achat');
        $builder->select('achat.*, caisse.numero as caisse_numero, caissier.email as caissier_email');
        $builder->join('caisse', 'caisse.id_caisse = achat.id_caisse', 'left');
        $builder->join('caissier', 'caissier.id_caissier = achat.id_caissier', 'left');
        $builder->orderBy('achat.date_achat', 'DESC');
        
        $achats = $builder->get()->getResultArray();
        
        foreach ($achats as &$achat) {
            $total = $db->query('SELECT SUM(quantite * prix_unitaire) as total FROM achat_details WHERE id_achat = ?', [$achat['id_achat']])->getRow()->total;
            $achat['total'] = $total ?? 0;
        }

        $data = [
            'titre' => 'Liste des achats',
            'achats' => $achats
        ];

        return view('achat/liste', $data);
    }

    /**
     * GET /achat/export/(:num)
     *
     * Exporte un achat spécifique depuis la base de données.
     */
    public function exportFacture($id_achat = null): string|\CodeIgniter\HTTP\RedirectResponse
    {
        if (!$id_achat) {
            return redirect()->to('/achat/liste')->with('error', 'Facture introuvable.');
        }

        $db = \Config\Database::connect();
        
        $achat = $db->table('achat')
            ->select('achat.*, caisse.numero as caisse_numero, caissier.email as caissier_email')
            ->join('caisse', 'caisse.id_caisse = achat.id_caisse', 'left')
            ->join('caissier', 'caissier.id_caissier = achat.id_caissier', 'left')
            ->where('id_achat', $id_achat)
            ->get()->getRowArray();

        if (!$achat) {
            return redirect()->to('/achat/liste')->with('error', 'Facture introuvable.');
        }

        $details = $db->table('achat_details')
            ->select('achat_details.*, produit.designation')
            ->join('produit', 'produit.id_produit = achat_details.id_produit')
            ->where('id_achat', $id_achat)
            ->get()->getResultArray();

        $panier = [];
        $total = 0;
        foreach ($details as $detail) {
            $montant = $detail['quantite'] * $detail['prix_unitaire'];
            $total += $montant;
            $panier[] = [
                'designation' => $detail['designation'],
                'prix_unitaire' => $detail['prix_unitaire'],
                'quantite' => $detail['quantite'],
                'montant' => $montant
            ];
        }

        $data = [
            'titre'  => 'Facture #' . $achat['id_achat'],
            'panier' => $panier,
            'total'  => $total,
            'caisse_numero' => $achat['caisse_numero'],
            'caissier' => $achat['caissier_email'],
            'date_achat' => $achat['date_achat']
        ];

        return view('achat/facture', $data);
    }

    /**
     * POST /achat/cloturer
     *
     * Clôture l'achat en l'insérant en base et décrémentant les stocks.
     */
    public function cloturer(): \CodeIgniter\HTTP\RedirectResponse
    {
        $panier = session()->get(self::SESSION_PANIER) ?? [];

        if (empty($panier)) {
            return redirect()->to(site_url('achat/saisie'))->with('error', 'Le panier est vide.');
        }

        $caisseId = session()->get(self::SESSION_CAISSE_ID);
        $caissierId = session()->get('id_caissier');

        // Check if there is a generic client (ID 1), create if not.
        $clientModel = new \App\Models\ClientModel();
        $client = $clientModel->first();
        if (!$client) {
            $clientId = $clientModel->insert([
                'nom' => 'Client Anonyme',
                'telephone' => '',
                'email' => ''
            ]);
        } else {
            $clientId = $client['id_client'];
        }

        $db = \Config\Database::connect();
        $db->transStart();

        $achatModel = new \App\Models\AchatModel();
        $achatId = $achatModel->insert([
            'id_client' => $clientId,
            'id_caisse' => $caisseId,
            'id_caissier' => $caissierId,
            'statut' => 'cloture'
        ]);

        $detailsModel = new \App\Models\AchatDetailsModel();
        $produitModel = new ProduitModel();

        foreach ($panier as $ligne) {
            $detailsModel->insert([
                'id_achat' => $achatId,
                'id_produit' => $ligne['id_produit'],
                'quantite' => $ligne['quantite'],
                'prix_unitaire' => $ligne['prix_unitaire']
            ]);

            // Decrement stock
            $produitModel->decrementerStock($ligne['id_produit'], $ligne['quantite']);
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return redirect()->to(site_url('achat/saisie'))->with('error', 'Erreur lors de la clôture de l\'achat.');
        }

        session()->remove(self::SESSION_PANIER);
        return redirect()->to(site_url('achat/saisie'))->with('success', 'Achat clôturé avec succès. Prêt pour le client suivant.');
    }
}
