<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * LeaveBalanceModel
 *
 * Gestion des soldes de congés par employé / type / année.
 *
 * Règle fondamentale :
 *   jours_restants = jours_attribues - jours_pris
 *   → jamais stocké, toujours calculé
 */
class LeaveBalanceModel extends Model
{
    protected $table            = 'leave_balances';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $allowedFields = [
        'user_id',
        'leave_type_id',
        'annee',
        'jours_attribues',
        'jours_pris',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // ----------------------------------------------------------------
    // Lecture
    // ----------------------------------------------------------------

    /**
     * Soldes d'un employé pour une année donnée, avec le calcul du restant.
     */
    public function getByUser(int $userId, int $annee): array
    {
        $rows = $this->db->table('leave_balances lb')
            ->select('
                lb.*,
                lt.nom            AS type_nom,
                lt.deductible     AS type_deductible,
                (lb.jours_attribues - lb.jours_pris) AS jours_restants
            ')
            ->join('leave_types lt', 'lt.id = lb.leave_type_id', 'left')
            ->where('lb.user_id', $userId)
            ->where('lb.annee',   $annee)
            ->orderBy('lt.nom', 'ASC')
            ->get()
            ->getResultArray();

        return $rows;
    }

    /**
     * Tous les soldes d'une année avec les infos employé (vue RH).
     */
    public function getAllForYear(int $annee, ?int $departmentId = null): array
    {
        $builder = $this->db->table('leave_balances lb')
            ->select('
                lb.*,
                u.nom             AS emp_nom,
                u.prenom          AS emp_prenom,
                u.email           AS emp_email,
                d.nom             AS dept_nom,
                lt.nom            AS type_nom,
                lt.deductible     AS type_deductible,
                (lb.jours_attribues - lb.jours_pris) AS jours_restants
            ')
            ->join('users u',        'u.id = lb.user_id',        'left')
            ->join('departments d',  'd.id = u.department_id',   'left')
            ->join('leave_types lt', 'lt.id = lb.leave_type_id', 'left')
            ->where('lb.annee',  $annee)
            ->where('u.actif',   1);

        if ($departmentId) {
            $builder->where('u.department_id', $departmentId);
        }

        return $builder
            ->orderBy('u.nom', 'ASC')
            ->orderBy('lt.nom', 'ASC')
            ->get()
            ->getResultArray();
    }

    /**
     * Vérifier si un employé a assez de jours pour une demande.
     *
     * @return array{ok: bool, restant: int, message: string}
     */
    public function checkSolde(int $userId, int $leaveTypeId, int $annee, int $nbJours): array
    {
        $solde = $this->where('user_id',       $userId)
                      ->where('leave_type_id', $leaveTypeId)
                      ->where('annee',         $annee)
                      ->first();

        if (! $solde) {
            return [
                'ok'      => false,
                'restant' => 0,
                'message' => "Aucun solde défini pour l'année {$annee}.",
            ];
        }

        $restant = $solde['jours_attribues'] - $solde['jours_pris'];

        if ($nbJours > $restant) {
            return [
                'ok'      => false,
                'restant' => $restant,
                'message' => "Solde insuffisant : {$restant} jour(s) disponible(s), {$nbJours} demandé(s).",
            ];
        }

        return ['ok' => true, 'restant' => $restant, 'message' => 'OK'];
    }

    // ----------------------------------------------------------------
    // Écriture
    // ----------------------------------------------------------------

    /**
     * Initialiser les soldes pour tous les employés actifs (début d'année).
     * Ne touche pas aux soldes déjà existants pour cette année.
     */
    public function initializeYear(int $annee): int
    {
        $users      = $this->db->table('users')->where('actif', 1)->get()->getResultArray();
        $leaveTypes = $this->db->table('leave_types')->get()->getResultArray();

        $inserted = 0;
        foreach ($users as $user) {
            foreach ($leaveTypes as $lt) {
                $exists = $this->where('user_id',       $user['id'])
                               ->where('leave_type_id', $lt['id'])
                               ->where('annee',         $annee)
                               ->countAllResults();

                if (! $exists) {
                    $this->insert([
                        'user_id'        => $user['id'],
                        'leave_type_id'  => $lt['id'],
                        'annee'          => $annee,
                        'jours_attribues'=> $lt['jours_annuels'],
                        'jours_pris'     => 0,
                    ]);
                    $inserted++;
                }
            }
        }

        return $inserted;
    }
}