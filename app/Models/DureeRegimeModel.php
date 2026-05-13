<?php

namespace App\Models;

use CodeIgniter\Model;

class DureeRegimeModel extends Model
{
    protected $table = 'duree_regime';
    protected $primaryKey = 'id_duree_regime';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id_regime',
        'nb_jours',
        'prix',
    ];

    public function getByRegimeId(int $idRegime): array
    {
        return $this->where('id_regime', $idRegime)
            ->orderBy('nb_jours', 'ASC')
            ->findAll();
    }

    public function getDistinctDurations(): array
    {
        $rows = $this->select('nb_jours')
            ->groupBy('nb_jours')
            ->orderBy('nb_jours', 'ASC')
            ->findAll();

        return array_map(static fn(array $row): int => (int) $row['nb_jours'], $rows);
    }

    public function getAllGroupedByRegime(): array
    {
        $rows = $this->select('id_regime, nb_jours')
            ->orderBy('nb_jours', 'ASC')
            ->findAll();

        $grouped = [];
        foreach ($rows as $row) {
            $idRegime = (int) $row['id_regime'];
            $grouped[$idRegime][] = (int) $row['nb_jours'];
        }

        return $grouped;
    }
}
