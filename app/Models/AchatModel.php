<?php

namespace App\Models;

use CodeIgniter\Model;

class AchatModel extends Model
{
    protected $table = 'achat';
    protected $primaryKey = 'id_achat';
    protected $allowedFields = ['id_client', 'id_caisse', 'id_caissier', 'date_achat', 'statut'];

    public function getListeAchats()
    {
        $builder = $this->db->table($this->table);
        $builder->select('achat.*, caisse.numero as caisse_numero, caissier.email as caissier_email');
        $builder->join('caisse', 'caisse.id_caisse = achat.id_caisse', 'left');
        $builder->join('caissier', 'caissier.id_caissier = achat.id_caissier', 'left');
        $builder->orderBy('achat.date_achat', 'DESC');
        
        $achats = $builder->get()->getResultArray();
        
        foreach ($achats as &$achat) {
            $total = $this->db->query('SELECT SUM(quantite * prix_unitaire) as total FROM achat_details WHERE id_achat = ?', [$achat['id_achat']])->getRow()->total;
            $achat['total'] = $total ?? 0;
        }

        return $achats;
    }

    public function getFacture($id_achat)
    {
        $achat = $this->db->table($this->table)
            ->select('achat.*, caisse.numero as caisse_numero, caissier.email as caissier_email')
            ->join('caisse', 'caisse.id_caisse = achat.id_caisse', 'left')
            ->join('caissier', 'caissier.id_caissier = achat.id_caissier', 'left')
            ->where('id_achat', $id_achat)
            ->get()->getRowArray();

        if (!$achat) {
            return null;
        }

        $details = $this->db->table('achat_details')
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

        return [
            'achat' => $achat,
            'panier' => $panier,
            'total' => $total
        ];
    }

    public function cloturerAchat($panier, $caisseId, $caissierId)
    {
        $clientModel = new ClientModel();
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

        $this->db->transStart();

        $achatId = $this->insert([
            'id_client' => $clientId,
            'id_caisse' => $caisseId,
            'id_caissier' => $caissierId,
            'statut' => 'cloture'
        ]);

        $detailsModel = new AchatDetailsModel();
        $produitModel = new ProduitModel();

        foreach ($panier as $ligne) {
            $detailsModel->insert([
                'id_achat' => $achatId,
                'id_produit' => $ligne['id_produit'],
                'quantite' => $ligne['quantite'],
                'prix_unitaire' => $ligne['prix_unitaire']
            ]);

            $produitModel->decrementerStock($ligne['id_produit'], $ligne['quantite']);
        }

        $this->db->transComplete();

        return $this->db->transStatus();
    }
}
