<?php

namespace App\Models;

use CodeIgniter\Model;

class CommandeModel extends Model
{
    protected string $variationColumn = 'variation_mensuelle_kg';
    protected $table = 'commande';
    protected $primaryKey = 'id_commande';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id_utilisateur',
        'id_regime',
        'id_duree_regime',
        'date_achat',
        'montant_paye',
    ];

    public function __construct()
    {
        parent::__construct();
        $this->variationColumn = $this->db->fieldExists('variation_mensuelle_kg', 'regime')
            ? 'variation_mensuelle_kg'
            : 'variation_poids';
    }

    public function getHistoryByUserId(int $userId): array
    {
        return $this->basePurchaseBuilder()
            ->select([
                'c.id_commande',
                'c.date_achat',
                'c.montant_paye',
                'r.nom_regime',
                'd.nb_jours',
                'd.prix AS prix_duree',
            ])
            ->where('c.id_utilisateur', $userId)
            ->orderBy('c.date_achat', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getPurchasedRegimesByUserId(int $userId): array
    {
        return $this->basePurchaseBuilder()
            ->select([
                'c.id_commande',
                'c.date_achat',
                'c.montant_paye',
                'c.id_regime',
                'r.nom_regime',
                'r.' . $this->variationColumn . ' AS variation_mensuelle_kg',
                'd.nb_jours',
                'd.prix AS prix_duree',
            ])
            ->where('c.id_utilisateur', $userId)
            ->orderBy('c.date_achat', 'DESC')
            ->get()
            ->getResultArray();
    }

    public function getPurchaseById(int $commandeId, int $userId): ?array
    {
        $row = $this->basePurchaseBuilder()
            ->select([
                'c.id_commande',
                'c.id_utilisateur',
                'c.id_regime',
                'c.id_duree_regime',
                'c.date_achat',
                'c.montant_paye',
                'r.nom_regime',
                'r.' . $this->variationColumn . ' AS variation_mensuelle_kg',
                'r.pourcentage_viande',
                'r.pourcentage_poisson',
                'r.pourcentage_volaille',
                'd.nb_jours',
                'd.prix AS prix_duree',
            ])
            ->where('c.id_commande', $commandeId)
            ->where('c.id_utilisateur', $userId)
            ->get()
            ->getFirstRow('array');

        return $row ?: null;
    }

    public function hasActiveRegime(int $userId, int $regimeId, int $dureeId): bool
    {
        $rows = $this->basePurchaseBuilder()
            ->select([
                'c.date_achat',
                'd.nb_jours',
                'c.id_duree_regime',
            ])
            ->where('c.id_utilisateur', $userId)
            ->where('c.id_regime', $regimeId)
            ->where('c.id_duree_regime', $dureeId)
            ->orderBy('c.date_achat', 'DESC')
            ->get()
            ->getResultArray();

        $now = new \DateTimeImmutable('now');

        foreach ($rows as $row) {
            if (empty($row['date_achat']) || empty($row['nb_jours'])) {
                continue;
            }
            $start = new \DateTimeImmutable((string) $row['date_achat']);
            $end = $start->modify('+' . (int) $row['nb_jours'] . ' days');
            if ($end >= $now) {
                return true;
            }
        }

        return false;
    }

    public function countUserPurchases(int $userId): int
    {
        return $this->db->table('commande')
            ->where('id_utilisateur', $userId)
            ->countAllResults();
    }

    private function basePurchaseBuilder()
    {
        return $this->db->table('commande c')
            ->join('regime r', 'r.id_regime = c.id_regime')
            ->join('duree_regime d', 'd.id_duree_regime = c.id_duree_regime');
    }
}
