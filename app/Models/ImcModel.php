<?php

namespace App\Models;

use CodeIgniter\Model;

class ImcModel extends Model
{
    protected $table = 'imc';
    protected $primaryKey = 'id_imc';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'label_imc',
        'imc_min',
        'imc_max',
    ];

    public function getIdealRange(): ?array
    {
        $row = $this->where('label_imc', 'Poids normal')->first();

        return $row ?: null;
    }
}
