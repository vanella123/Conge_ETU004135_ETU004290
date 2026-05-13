<?php

namespace App\Models;

use CodeIgniter\Model;

class UtilisateurModel extends Model
{
    protected $table = 'utilisateur';
    protected $primaryKey = 'id_utilisateur';
    protected $returnType = 'array';
    protected $allowedFields = [
        'nom',
        'email',
        'mot_de_passe',
        'genre',
        'taille_cm',
        'poids_kg',
        'poids_objectif',
        'date_naissance',
        'id_objectif',
        'is_gold',
        'argent',
        'role_utilisateur',
    ];

    public function findByEmail(string $email): ?array
    {
        $user = $this->where('email', $email)->first();

        return is_array($user) ? $user : null;
    }

    public function calculateImc(float $poidsKg, float $tailleCm): ?float
    {
        if ($poidsKg <= 0 || $tailleCm <= 0) {
            return null;
        }
        
        $tailleM = $tailleCm / 100;
        $imc = $poidsKg / ($tailleM ** 2);

        return round($imc, 2);
    }

    public function getImc(array $user): ?float
    {
        return $this->calculateImc(
            (float)$user['poids_kg'],
            (float)$user['taille_cm']
        );
    }

    public function getTestUser(): ?array
    {
        return $this->orderBy($this->primaryKey, 'ASC')->first();
    }

    public function getGoldMembers(): array
    {
        return $this->db->table('utilisateur u')
            ->select([
                'u.id_utilisateur',
                'u.nom',
                'u.email',
                'u.argent',
                'u.genre',
                'COUNT(c.id_commande) AS nb_commandes',
            ])
            ->join('commande c', 'c.id_utilisateur = u.id_utilisateur', 'left')
            ->where('u.is_gold', 1)
            ->groupBy([
                'u.id_utilisateur',
                'u.nom',
                'u.email',
                'u.argent',
                'u.genre',
            ])
            ->orderBy('u.nom', 'ASC')
            ->get()
            ->getResultArray();
    }
    public function getUserCommandes(int $userId): array
    {
        return $this->db->table('v_commande_regime')
            ->where('id_utilisateur', $userId)
            ->orderBy('date_achat', 'DESC')
            ->get()
            ->getResultArray();
    }
}
