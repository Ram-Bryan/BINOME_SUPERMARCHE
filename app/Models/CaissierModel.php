<?php

namespace App\Models;

use CodeIgniter\Model;

class CaissierModel extends Model
{
    protected $table = 'caissier';
    protected $primaryKey = 'id_caissier';
    protected $allowedFields = ['mot_de_passe', 'email'];
}
