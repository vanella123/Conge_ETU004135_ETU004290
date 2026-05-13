<?php

namespace App\Models;

use CodeIgniter\Model;

class OptionModel extends Model
{
    protected $table = 'option';
    protected $primaryKey = 'id_option';
    protected $returnType = 'array';
    protected $allowedFields = [
        'nom_option',
        'nb_regimes_achetes',
        'prix_unique',
        'reduction_pourcentage',
    ];

    public function getAdminListing(): array
    {
        return $this->orderBy('id_option', 'ASC')->findAll();
    }

    public function getGoldOption(): ?array
    {
        $row = $this->where('nom_option', 'Gold')->first();

        return is_array($row) ? $row : null;
    }
}
