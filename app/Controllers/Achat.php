<?php

namespace App\Controllers;

use App\Models\ProduitModel;
use App\Models\AchatModel;
use App\Models\ClientModel;
use App\Models\AchatDetailsModel;

class Achat extends BaseController
{
    private function getPanierSessionKey(): string
    {
        $caisseId = session()->get('caisse_id') ?? '0';
        return 'panier_' . $caisseId;
    }

    public function saisie(): string
    {
        $produitModel = new ProduitModel();
        $produits     = $produitModel->findAll();
        
        $clientModel = new ClientModel();
        $clients = $clientModel->findAll();

        $panier = session()->get($this->getPanierSessionKey()) ?? [];

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
            'clients'  => $clients,
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
        $produit      = $produitModel->find($idProduit);

        if ($produit === null) {
            session()->setFlashdata('error', 'Produit introuvable.');
            return redirect()->to(site_url('achat/saisie'));
        }

        if ($quantite > $produit['quantite_stock']) {
            session()->setFlashdata(
                'error',
                esc("Stock insuffisant pour « {$produit['designation']} ». Stock disponible : {$produit['quantite_stock']} unité(s).")
            );
            return redirect()->to(site_url('achat/saisie'));
        }

        $panier = session()->get($this->getPanierSessionKey()) ?? [];

        if (isset($panier[$idProduit])) {
            $nouvelleQte = $panier[$idProduit]['quantite'] + $quantite;

            if ($nouvelleQte > $produit['quantite_stock']) {
                session()->setFlashdata(
                    'error',
                    esc("Stock insuffisant. Vous avez déjà {$panier[$idProduit]['quantite']} « {$produit['designation']} » dans le panier. Stock disponible : {$produit['quantite_stock']} unité(s).")
                );
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

        session()->set($this->getPanierSessionKey(), $panier);

        session()->setFlashdata(
            'success',
            esc("« {$produit['designation']} » × {$quantite} ajouté au panier.")
        );

        return redirect()->to(site_url('achat/saisie'));
    }

    public function supprimerLigne(): \CodeIgniter\HTTP\RedirectResponse
    {
        $idProduit = (int) $this->request->getPost('id_produit');
        $panier    = session()->get($this->getPanierSessionKey()) ?? [];

        if (isset($panier[$idProduit])) {
            $designation = $panier[$idProduit]['designation'];
            unset($panier[$idProduit]);
            session()->set($this->getPanierSessionKey(), $panier);
            session()->setFlashdata('success', esc("« {$designation} » retiré du panier."));
        }

        return redirect()->to(site_url('achat/saisie'));
    }

    public function viderPanier(): \CodeIgniter\HTTP\RedirectResponse
    {
        session()->remove($this->getPanierSessionKey());
        session()->setFlashdata('success', 'Panier vidé.');
        return redirect()->to(site_url('achat/saisie'));
    }

    public function liste()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        $achatModel = new AchatModel();
        $achats = $achatModel->getAchatsWithDetails();

        $data = [
            'titre' => 'Liste des achats',
            'achats' => $achats
        ];

        return view('achat/liste', $data);
    }

    public function exportFacture($id_achat = null): string|\CodeIgniter\HTTP\RedirectResponse
    {
        if (!$id_achat) {
            return redirect()->to('/achat/liste')->with('error', 'Facture introuvable.');
        }

        $achatModel = new AchatModel();
        $result = $achatModel->getAchatWithDetailsById($id_achat);

        if (!$result) {
            return redirect()->to('/achat/liste')->with('error', 'Facture introuvable.');
        }

        $achat = $result['achat'];
        $details = $result['details'];

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
            'date_achat' => $achat['date_achat'],
            'client_nom' => $achat['client_nom'] ?? 'Inconnu',
            'client_telephone' => $achat['client_telephone'] ?? '',
            'client_email' => $achat['client_email'] ?? '',
        ];

        return view('achat/facture', $data);
    }

    public function cloturer(): \CodeIgniter\HTTP\RedirectResponse
    {
        $panier = session()->get($this->getPanierSessionKey()) ?? [];

        if (empty($panier)) {
            return redirect()->to(site_url('achat/saisie'))->with('error', 'Le panier est vide.');
        }

        $caisseId = session()->get('caisse_id');
        $caissierId = session()->get('id_caissier');

        $clientData = [
            'id_client' => $this->request->getPost('id_client'),
            'nom' => $this->request->getPost('nouveau_client_nom'),
            'telephone' => $this->request->getPost('nouveau_client_telephone'),
            'email' => $this->request->getPost('nouveau_client_email')
        ];

        if ($clientData['id_client'] === 'NEW') {
            $clientData['id_client'] = null;
        }

        if (empty($clientData['id_client']) && empty($clientData['nom'])) {
            return redirect()->to(site_url('achat/saisie'))->with('error', 'Veuillez sélectionner un client.');
        }

        $achatModel = new AchatModel();
        $success = $achatModel->cloturerAchat($panier, $caisseId, $caissierId, $clientData);

        if (!$success) {
            return redirect()->to(site_url('achat/saisie'))->with('error', 'Erreur lors de la clôture.');
        }

        session()->remove($this->getPanierSessionKey());
        return redirect()->to(site_url('achat/saisie'))->with('success', 'Achat clôturé avec succès.');
    }
}
