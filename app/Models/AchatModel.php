<?php

namespace App\Models;

use CodeIgniter\Model;

class AchatModel extends Model
{
    protected $table = 'achat';
    protected $primaryKey = 'id_achat';
    protected $allowedFields = ['id_client', 'id_caisse', 'id_caissier', 'date_achat', 'statut'];

    public function getAchatsWithDetails(): array
    {
        $achats = $this->db->table('v_achat')->orderBy('date_achat', 'DESC')->get()->getResultArray();
        
        foreach ($achats as &$achat) {
            $total = $this->db->query('SELECT SUM(quantite * prix_unitaire) as total FROM achat_details WHERE id_achat = ?', [$achat['id_achat']])->getRow()->total;
            $achat['total'] = $total ?? 0;
        }

        return $achats;
    }

    public function getAchatWithDetailsById($id)
    {
        $achat = $this->db->table('v_achat')->where('id_achat', $id)->get()->getRowArray();

        if (!$achat) {
            return null;
        }

        $details = $this->db->table('v_achat_details')->where('id_achat', $id)->get()->getResultArray();

        return ['achat' => $achat, 'details' => $details];
    }

    public function cloturerAchat(array $panier, int $caisseId, int $caissierId, array $clientData): bool
    {
        $this->db->transStart();

        $clientModel = new ClientModel();
        if (!empty($clientData['id_client'])) {
            $clientId = $clientData['id_client'];
        } else {
            $clientId = $clientModel->insert([
                'nom' => $clientData['nom'],
                'telephone' => $clientData['telephone'],
                'email' => $clientData['email']
            ]);
        }

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
