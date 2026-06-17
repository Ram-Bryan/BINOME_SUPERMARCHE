<?php

namespace App\Controllers;

use App\Models\ProduitModel;
use App\Models\AchatModel;

class Achat extends BaseController
{
    public function saisie(): string
    {
        $produitModel = new ProduitModel();
        $produits     = $produitModel->tousLesProduits();

        $panier = session()->get('panier') ?? [];

        foreach ($produits as &$p) {
            $id = $p['id_produit'];
            if (isset($panier[$id])) {
                $p['quantite_stock'] = max(0, $p['quantite_stock'] - $panier[$id]['quantite']);
            }
        }
        unset($p);

        $total = 0;
        foreach ($panier as $ligne) {
            $total += $ligne['montant'];
        }

        $data = [
            'titre'    => 'Saisie des achats',
            'produits' => $produits,
            'panier'   => $panier,
            'total'    => $total,
            'flash_success' => session()->getFlashdata('success'),
            'flash_error'   => session()->getFlashdata('error'),
        ];

        return view('achat/saisie', $data);
    }

    public function ajouter(): \CodeIgniter\HTTP\RedirectResponse
    {
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
            session()->setFlashdata('error', implode('<br>', $this->validator->getErrors()));
            return redirect()->to(site_url('achat/saisie'));
        }

        $idProduit = (int) $this->request->getPost('id_produit');
        $quantite  = (int) $this->request->getPost('quantite');

        $produitModel = new ProduitModel();
        $produit      = $produitModel->trouverParId($idProduit);

        if ($produit === null) {
            session()->setFlashdata('error', 'Produit introuvable.');
            return redirect()->to(site_url('achat/saisie'));
        }

        if ($quantite > $produit['quantite_stock']) {
            session()->setFlashdata('error', esc("Stock insuffisant pour « {$produit['designation']} ». Stock disponible : {$produit['quantite_stock']} unité(s)."));
            return redirect()->to(site_url('achat/saisie'));
        }

        $panier = session()->get('panier') ?? [];

        if (isset($panier[$idProduit])) {
            $nouvelleQte = $panier[$idProduit]['quantite'] + $quantite;

            if ($nouvelleQte > $produit['quantite_stock']) {
                session()->setFlashdata('error', esc("Stock insuffisant. Vous avez déjà {$panier[$idProduit]['quantite']} « {$produit['designation']} » dans le panier. Stock disponible : {$produit['quantite_stock']} unité(s)."));
                return redirect()->to(site_url('achat/saisie'));
            }

            $panier[$idProduit]['quantite'] = $nouvelleQte;
            $panier[$idProduit]['montant']  = $nouvelleQte * $produit['prix'];
        } else {
            $panier[$idProduit] = [
                'id_produit'   => $idProduit,
                'designation'  => $produit['designation'],
                'prix_unitaire'=> $produit['prix'],
                'quantite'     => $quantite,
                'montant'      => $quantite * $produit['prix'],
            ];
        }

        session()->set('panier', $panier);
        session()->setFlashdata('success', esc("« {$produit['designation']} » × {$quantite} ajouté au panier."));

        return redirect()->to(site_url('achat/saisie'));
    }

    public function supprimerLigne(): \CodeIgniter\HTTP\RedirectResponse
    {
        $idProduit = (int) $this->request->getPost('id_produit');
        $panier    = session()->get('panier') ?? [];

        if (isset($panier[$idProduit])) {
            $designation = $panier[$idProduit]['designation'];
            unset($panier[$idProduit]);
            session()->set('panier', $panier);
            session()->setFlashdata('success', esc("« {$designation} » retiré du panier."));
        }

        return redirect()->to(site_url('achat/saisie'));
    }

    public function viderPanier(): \CodeIgniter\HTTP\RedirectResponse
    {
        session()->remove('panier');
        session()->setFlashdata('success', 'Panier vidé. Prêt pour le client suivant.');
        return redirect()->to(site_url('achat/saisie'));
    }

    public function liste()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $achatModel = new AchatModel();
        
        $data = [
            'titre' => 'Liste des achats',
            'achats' => $achatModel->getListeAchats()
        ];

        return view('achat/liste', $data);
    }

    public function exportFacture($id_achat = null): string|\CodeIgniter\HTTP\RedirectResponse
    {
        if (!$id_achat) {
            return redirect()->to('/achat/liste')->with('error', 'Facture introuvable.');
        }

        $achatModel = new AchatModel();
        $factureData = $achatModel->getFacture($id_achat);

        if (!$factureData) {
            return redirect()->to('/achat/liste')->with('error', 'Facture introuvable.');
        }

        $data = [
            'titre'  => 'Facture #' . $factureData['achat']['id_achat'],
            'panier' => $factureData['panier'],
            'total'  => $factureData['total'],
            'caisse_numero' => $factureData['achat']['caisse_numero'],
            'caissier' => $factureData['achat']['caissier_email'],
            'date_achat' => $factureData['achat']['date_achat']
        ];

        return view('achat/facture', $data);
    }

    public function cloturer(): \CodeIgniter\HTTP\RedirectResponse
    {
        $panier = session()->get('panier') ?? [];

        if (empty($panier)) {
            return redirect()->to(site_url('achat/saisie'))->with('error', 'Le panier est vide.');
        }

        $caisseId = session()->get('caisse_id');
        $caissierId = session()->get('id_caissier');

        $achatModel = new AchatModel();
        $success = $achatModel->cloturerAchat($panier, $caisseId, $caissierId);

        if (!$success) {
            return redirect()->to(site_url('achat/saisie'))->with('error', 'Erreur lors de la clôture de l\'achat.');
        }

        session()->remove('panier');
        return redirect()->to(site_url('achat/saisie'))->with('success', 'Achat clôturé avec succès. Prêt pour le client suivant.');
    }
}

