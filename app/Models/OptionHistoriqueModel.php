<?php

namespace App\Models;

use CodeIgniter\Model;

class OptionHistoriqueModel extends Model
{
    protected $table = 'option_historique';
    protected $primaryKey = 'id_option_historique';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id_option',
        'prix',
        'reduction_pourcentage',
        'nb_regimes_achetes',
        'date_debut',
    ];

    public function getByOptionId(int $optionId): array
    {
        return $this->where('id_option', $optionId)
            ->orderBy('date_debut', 'DESC')
            ->orderBy('id_option_historique', 'DESC')
            ->findAll();
    }
}
