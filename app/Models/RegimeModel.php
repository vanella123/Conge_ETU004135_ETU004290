<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Models\UtilisateurModel;

class RegimeModel extends Model
{
    protected ImcModel $imcModel;
    protected string $variationColumn = 'variation_mensuelle_kg';
    protected $table = 'regime';
    protected $primaryKey = 'id_regime';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [
        'nom_regime',
        'variation_mensuelle_kg',
        'variation_poids',
        'pourcentage_viande',
        'pourcentage_poisson',
        'pourcentage_volaille',
    ];
    protected $afterFind = ['normalizeVariationColumn'];

    public function __construct()
    {
        parent::__construct();
        $this->imcModel = model(ImcModel::class);
        $this->variationColumn = $this->db->fieldExists('variation_mensuelle_kg', $this->table)
            ? 'variation_mensuelle_kg'
            : 'variation_poids';
    }
    
    public function getSuggestedByUser(array $user): array
    {
        $idObjectif = (int)($user['id_objectif'] ?? 0);

        if ($idObjectif === 1) {
            return $this->where($this->variationColumn . ' <', 0)->findAll();
        }

        if ($idObjectif === 2) {
            return $this->where($this->variationColumn . ' >', 0)->findAll();
        }

        if ($idObjectif === 3) {

            $poidsKg = (float)($user['poids_kg'] ?? 0);
            $tailleCm = (float)($user['taille_cm'] ?? 0);

            if ($poidsKg <= 0 || $tailleCm <= 0) return [];

            $tailleM = $tailleCm / 100;
            $imc = $poidsKg / ($tailleM ** 2);

            $ideal = $this->imcModel->getIdealRange();
            if ($ideal === null) return [];

            if ($imc < (float)$ideal['imc_min']) return $this->where($this->variationColumn . ' >', 0)->findAll();
            if ($imc > (float)$ideal['imc_max']) return $this->where($this->variationColumn . ' <', 0)->findAll();

            // Already in ideal IMC -> nothing to suggest to attain it
            return [];
        }

        return [];
    }

    public function getFilteredList(?int $dureeJours, ?int $objectifId, ?int $userId = null): array
    {
        $builder = $this->db->table('regime r');
        $variationColumn = 'r.' . $this->variationColumn;
        $builder->distinct();
        $builder->select('r.id_regime, r.nom_regime, ' . $variationColumn . ' AS variation_mensuelle_kg, r.pourcentage_viande, r.pourcentage_poisson, r.pourcentage_volaille');
        $builder->join('duree_regime d', 'd.id_regime = r.id_regime');

        if (!empty($dureeJours)) {
            $builder->where('d.nb_jours', $dureeJours);
        }

        if ($objectifId === 1) {
            $builder->where($variationColumn . ' <', 0);
        } elseif ($objectifId === 2) {
            $builder->where($variationColumn . ' >', 0);
        } elseif ($objectifId === 3) {
            $idealConditionSet = false;
            
            if ($userId !== null) {
                $userModel = model(UtilisateurModel::class);
                $user = $userModel->find($userId);
                
                if ($user) {
                    $poidsKg = (float)($user['poids_kg'] ?? 0);
                    $tailleCm = (float)($user['taille_cm'] ?? 0);
                    
                    if ($poidsKg > 0 && $tailleCm > 0) {
                        $tailleM = $tailleCm / 100;
                        $imc = $poidsKg / ($tailleM ** 2);
                        
                        $ideal = $this->imcModel->getIdealRange();
                        if ($ideal !== null) {
                            if ($imc < (float)$ideal['imc_min']) {
                                $builder->where($variationColumn . ' >', 0);
                                $idealConditionSet = true;
                            } elseif ($imc > (float)$ideal['imc_max']) {
                                $builder->where($variationColumn . ' <', 0);
                                $idealConditionSet = true;
                            } else {
                                // User is already at ideal IMC, they don't need any regime to attain it
                                $builder->where('1 = 0'); 
                                $idealConditionSet = true;
                            }
                        }
                    }
                }
            }
            
            if (!$idealConditionSet) {
                // Fallback for an objective 3 without a valid logged-in user => show nothing or maintenance
                $builder->where('1 = 0');
            }
        }

        $builder->orderBy('r.nom_regime', 'ASC');

        return $builder->get()->getResultArray();
    }

    protected function normalizeVariationColumn(array $data): array
    {
        if (! isset($data['data'])) {
            return $data;
        }

        if (isset($data['data']['variation_poids']) && ! isset($data['data']['variation_mensuelle_kg'])) {
            $data['data']['variation_mensuelle_kg'] = $data['data']['variation_poids'];
            return $data;
        }

        if (is_array($data['data'])) {
            foreach ($data['data'] as &$row) {
                if (is_array($row) && isset($row['variation_poids']) && ! isset($row['variation_mensuelle_kg'])) {
                    $row['variation_mensuelle_kg'] = $row['variation_poids'];
                }
            }
        }

        return $data;
    }

    public function getFeaturedRegimes(int $limit = 3): array
    {
        $builder = $this->db->table('regime r');
        $variationColumn = 'r.' . $this->variationColumn;
        $builder->select('r.id_regime, r.nom_regime, ' . $variationColumn . ' AS variation_mensuelle_kg, r.pourcentage_viande, r.pourcentage_poisson, r.pourcentage_volaille, MIN(d.prix) AS prix_min');
        $builder->join('duree_regime d', 'd.id_regime = r.id_regime');
        $builder->groupBy('r.id_regime, r.nom_regime, ' . $variationColumn . ', r.pourcentage_viande, r.pourcentage_poisson, r.pourcentage_volaille');
        $builder->orderBy('r.nom_regime', 'ASC');
        $builder->limit($limit);

        return $builder->get()->getResultArray();
    }

    public function createRegimeWithRelations(array $payload, array $activityIds, array $durationRows): int
    {
        $this->db->transStart();

        $regimeId = $this->insert($payload, true);
        $this->syncRelations((int) $regimeId, $activityIds, $durationRows);

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            throw new \RuntimeException('Database transaction failed');
        }

        return (int) $regimeId;
    }

    public function updateRegimeWithRelations(int $id, array $payload, array $activityIds, array $durationRows): void
    {
        $this->db->transStart();

        $this->update($id, $payload);
        $this->syncRelations($id, $activityIds, $durationRows);

        $this->db->transComplete();

        if (!$this->db->transStatus()) {
            throw new \RuntimeException('Database transaction failed');
        }
    }

    public function deleteWithRelations(int $id): bool
    {
        $this->db->transStart();

        (new RegimeActiviteModel())->where('id_regime', $id)->delete();
        (new DureeRegimeModel())->where('id_regime', $id)->delete();
        $this->delete($id);

        $this->db->transComplete();

        return $this->db->transStatus();
    }

    public function durationHasDependencies(int $durationId): bool
    {
        $hasCommandes = $this->db->table('commande')
            ->where('id_duree_regime', $durationId)
            ->countAllResults() > 0;

        if ($hasCommandes) {
            return true;
        }

        if (! $this->db->tableExists('duree_regime_prix')) {
            return false;
        }

        return $this->db->table('duree_regime_prix')
            ->where('id_duree_regime', $durationId)
            ->countAllResults() > 0;
    }

    public function syncRelations(int $regimeId, array $activityIds, array $durationRows): void
    {
        $regimeActiviteModel = new RegimeActiviteModel();
        $dureeRegimeModel = new DureeRegimeModel();

        $regimeActiviteModel->where('id_regime', $regimeId)->delete();
        if ($activityIds !== []) {
            $regimeActiviteModel->insertBatch(array_map(
                static fn (int $activityId): array => [
                    'id_regime' => $regimeId,
                    'id_activite' => $activityId,
                ],
                $activityIds
            ));
        }

        $existingRows = $dureeRegimeModel
            ->where('id_regime', $regimeId)
            ->findAll();

        $existingById = [];
        foreach ($existingRows as $existingRow) {
            $existingById[(int) $existingRow['id_duree_regime']] = $existingRow;
        }

        $submittedIds = [];
        foreach ($durationRows as $row) {
            $durationId = (int) ($row['id_duree_regime'] ?? 0);
            if ($durationId > 0) {
                $submittedIds[] = $durationId;
            }
        }

        $rowsToDelete = array_diff(array_keys($existingById), $submittedIds);
        $lockedLabels = [];
        foreach ($rowsToDelete as $durationId) {
            if ($this->durationHasDependencies((int) $durationId)) {
                $lockedLabels[] = (int) ($existingById[$durationId]['nb_jours'] ?? 0) . ' jours';
            }
        }

        if ($lockedLabels !== []) {
            throw new \RuntimeException(
                'Impossible de retirer les durees deja utilisees ou historisees: ' . implode(', ', $lockedLabels) . '.'
            );
        }

        foreach ($durationRows as $row) {
            $payload = [
                'id_regime' => $regimeId,
                'nb_jours' => (int) $row['nb_jours'],
                'prix' => (float) $row['prix'],
            ];
            $durationId = (int) ($row['id_duree_regime'] ?? 0);

            if ($durationId > 0 && isset($existingById[$durationId])) {
                $existingRow = $existingById[$durationId];
                if (
                    $this->durationHasDependencies($durationId)
                    && (
                        (int) ($existingRow['nb_jours'] ?? 0) !== $payload['nb_jours']
                        || (float) ($existingRow['prix'] ?? 0) !== $payload['prix']
                    )
                ) {
                    throw new \RuntimeException(
                        'La duree ' . (int) ($existingRow['nb_jours'] ?? 0) . ' jours est deja utilisee et ne peut plus etre modifiee.'
                    );
                }

                $dureeRegimeModel->update($durationId, $payload);
                continue;
            }

            $dureeRegimeModel->insert($payload);
        }

        foreach ($rowsToDelete as $durationId) {
            $this->db->table('duree_regime')->where('id_duree_regime', (int) $durationId)->delete();
        }
    }

    public function getAdminRegimeListing(array $filters): array
    {
        $variationColumn = $this->variationColumn;
        $builder = $this->builder('regime r');
        $builder->select([
            'r.id_regime',
            'r.nom_regime',
            'r.' . $variationColumn . ' AS variation_mensuelle_kg',
            'r.pourcentage_viande',
            'r.pourcentage_poisson',
            'r.pourcentage_volaille',
            'COUNT(DISTINCT ra.id_regime_activite) AS nb_activites',
            'COUNT(DISTINCT d_all.id_duree_regime) AS nb_durees',
        ]);
        $builder->join('regime_activite ra', 'ra.id_regime = r.id_regime', 'left');
        $builder->join('duree_regime d_all', 'd_all.id_regime = r.id_regime', 'left');

        if (($filters['nom_regime'] ?? '') !== '') {
            $builder->like('r.nom_regime', $filters['nom_regime']);
        }

        if (($filters['variation_min'] ?? '') !== '') {
            $builder->where('r.' . $variationColumn . ' >=', (float) $filters['variation_min']);
        }

        if (($filters['variation_max'] ?? '') !== '') {
            $builder->where('r.' . $variationColumn . ' <=', (float) $filters['variation_max']);
        }

        if (
            (($filters['duree_min'] ?? '') !== '')
            || (($filters['duree_max'] ?? '') !== '')
            || (($filters['prix_min'] ?? '') !== '')
            || (($filters['prix_max'] ?? '') !== '')
        ) {
            $existsConditions = ['d_filter.id_regime = r.id_regime'];

            if (($filters['duree_min'] ?? '') !== '') {
                $existsConditions[] = 'd_filter.nb_jours >= ' . (int) $filters['duree_min'];
            }

            if (($filters['duree_max'] ?? '') !== '') {
                $existsConditions[] = 'd_filter.nb_jours <= ' . (int) $filters['duree_max'];
            }

            if (($filters['prix_min'] ?? '') !== '') {
                $existsConditions[] = 'd_filter.prix >= ' . (float) $filters['prix_min'];
            }

            if (($filters['prix_max'] ?? '') !== '') {
                $existsConditions[] = 'd_filter.prix <= ' . (float) $filters['prix_max'];
            }

            $builder->where(
                'EXISTS (SELECT 1 FROM duree_regime d_filter WHERE ' . implode(' AND ', $existsConditions) . ')',
                null,
                false
            );
        }

        $builder->groupBy([
            'r.id_regime',
            'r.nom_regime',
            'r.' . $variationColumn,
            'r.pourcentage_viande',
            'r.pourcentage_poisson',
            'r.pourcentage_volaille',
        ]);
        $builder->orderBy('r.nom_regime', 'ASC');

        return $builder->get()->getResultArray();
    }

    public function getAdminRegimeDetail(int $id): ?array
    {
        $regime = $this->find($id);

        if (! $regime) {
            return null;
        }

        $regime['activities'] = $this->db->table('regime_activite ra')
            ->select('a.id_activite, a.label_activite, a.nb_par_semaine')
            ->join('activite_sportive a', 'a.id_activite = ra.id_activite')
            ->where('ra.id_regime', $id)
            ->orderBy('a.label_activite', 'ASC')
            ->get()
            ->getResultArray();

        $regime['durations'] = (new DureeRegimeModel())
            ->where('id_regime', $id)
            ->orderBy('nb_jours', 'ASC')
            ->findAll();

        return $regime;
    }
}
