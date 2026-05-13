<?php

namespace App\Models;

use CodeIgniter\Model;

class CodePromoModel extends Model
{
    protected $table = 'code_promo';
    protected $primaryKey = 'id_code';
    protected $returnType = 'array';
    protected $allowedFields = [
        'montant',
        'code',
        'deja_utilise',
        'id_utilisateur_utilisation',
    ];

    public function findByCode(string $code): ?array
    {
        $promo = $this->where('code', strtoupper(trim($code)))->first();

        return is_array($promo) ? $promo : null;
    }

    public function findAvailableByCode(string $code): ?array
    {
        $promo = $this->where('code', strtoupper(trim($code)))
            ->where('deja_utilise', 0)
            ->first();

        return is_array($promo) ? $promo : null;
    }

    public function getAdminListing(array $filters = []): array
    {
        $builder = $this->db->table('code_promo cp');
        $builder->select([
            'cp.id_code',
            'cp.code',
            'cp.montant',
            'cp.deja_utilise',
            'cp.id_utilisateur_utilisation',
            'u.nom AS utilisateur_nom',
        ]);
        $builder->join('utilisateur u', 'u.id_utilisateur = cp.id_utilisateur_utilisation', 'left');

        if (($filters['montant_min'] ?? '') !== '') {
            $builder->where('cp.montant >=', (float) $filters['montant_min']);
        }

        if (($filters['montant_max'] ?? '') !== '') {
            $builder->where('cp.montant <=', (float) $filters['montant_max']);
        }

        $etat = $filters['etat'] ?? 'tous';
        if ($etat === 'disponible') {
            $builder->where('cp.deja_utilise', 0);
        } elseif ($etat === 'utilise') {
            $builder->where('cp.deja_utilise', 1);
        }

        $builder->orderBy('cp.id_code', 'DESC');

        return $builder->get()->getResultArray();
    }

}
