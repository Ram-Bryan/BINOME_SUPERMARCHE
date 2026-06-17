<?php

namespace App\Models;

use CodeIgniter\Model;

class AchatDetailsModel extends Model
{
    protected $table = 'achat_details';
    protected $primaryKey = 'id_detail';
    protected $allowedFields = ['id_achat', 'id_produit', 'quantite', 'prix_unitaire'];
}
