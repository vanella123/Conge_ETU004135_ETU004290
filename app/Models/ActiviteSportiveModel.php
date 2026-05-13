<?php

namespace App\Models;

use CodeIgniter\Model;

class ActiviteSportiveModel extends Model
{
    protected $table = 'activite_sportive';
    protected $primaryKey = 'id_activite';
    protected $returnType = 'array';
    protected $allowedFields = [
        'label_activite',
        'nb_par_semaine',
    ];

    public function getByIds(array $ids): array
    {
        if (empty($ids)) {
            return [];
        }

        return $this->whereIn('id_activite', $ids)
            ->orderBy('label_activite', 'ASC')
            ->findAll();
    }

    public function getActivityListing(array $filters): array
    {
        $builder = $this->db->table('activite_sportive a');
        $builder->select([
            'a.id_activite',
            'a.label_activite',
            'a.nb_par_semaine',
            'COUNT(DISTINCT ra.id_regime_activite) AS nb_regimes',
        ]);
        $builder->join('regime_activite ra', 'ra.id_activite = a.id_activite', 'left');

        if (($filters['label_activite'] ?? '') !== '') {
            $builder->like('a.label_activite', $filters['label_activite']);
        }

        if (($filters['frequence_min'] ?? '') !== '') {
            $builder->where('a.nb_par_semaine >=', (int) $filters['frequence_min']);
        }

        if (($filters['frequence_max'] ?? '') !== '') {
            $builder->where('a.nb_par_semaine <=', (int) $filters['frequence_max']);
        }

        $builder->groupBy([
            'a.id_activite',
            'a.label_activite',
            'a.nb_par_semaine',
        ]);
        $builder->orderBy('a.label_activite', 'ASC');

        return $builder->get()->getResultArray();
    }

    public function getLinkedRegimes(int $activityId): array
    {
        $variationColumn = $this->db->fieldExists('variation_mensuelle_kg', 'regime')
            ? 'variation_mensuelle_kg'
            : 'variation_poids';

        return $this->db->table('regime_activite ra')
            ->select([
                'r.id_regime',
                'r.nom_regime',
                'r.' . $variationColumn . ' AS variation_mensuelle_kg',
            ])
            ->join('regime r', 'r.id_regime = ra.id_regime')
            ->where('ra.id_activite', $activityId)
            ->orderBy('r.nom_regime', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function hasLinkedRegimes(int $id): bool
    {
        return $this->db->table('regime_activite')
            ->where('id_activite', $id)
            ->countAllResults() > 0;
    }
}
