<?php

namespace App\Models;

use CodeIgniter\Model;

class AchatModel extends Model
{
    protected $table = 'achat';
    protected $primaryKey = 'id_achat';
    protected $allowedFields = ['id_client', 'id_caisse', 'id_caissier', 'date_achat', 'statut'];
}
