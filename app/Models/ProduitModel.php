<?php

namespace App\Models;

use CodeIgniter\Model;

class ProduitModel extends Model
{
    protected $table      = 'produit';
    protected $primaryKey = 'id_produit';
    protected $allowedFields = ['designation', 'prix', 'quantite_stock'];
    protected $returnType = 'array';

    public function decrementerStock(int $idProduit, int $quantite): bool
    {
        return $this->db->query(
            'UPDATE produit SET quantite_stock = MAX(0, quantite_stock - ?) WHERE id_produit = ?',
            [$quantite, $idProduit]
        );
    }
}
