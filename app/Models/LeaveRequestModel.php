<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\Exceptions\DatabaseException;

/**
 * LeaveRequestModel
 *
 * Modèle pour la table `leave_requests`.
 * Contient toute la logique métier : approbation, refus, annulation,
 * et mise à jour atomique du solde.
 */
class LeaveRequestModel extends Model
{
    protected $table            = 'leave_requests';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $allowedFields = [
        'user_id',
        'leave_type_id',
        'date_debut',
        'date_fin',
        'nb_jours',
        'motif',
        'statut',
        'commentaire_rh',
        'traite_par',
        'traite_le',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // ----------------------------------------------------------------
    // Lecture / Listes
    // ----------------------------------------------------------------

    /**
     * Toutes les demandes avec infos employé, département et type de congé.
     */
    public function getAllWithDetails(array $filters = []): array
    {
        $builder = $this->db->table('leave_requests lr')
            ->select('
                lr.*,
                u.nom            AS emp_nom,
                u.prenom         AS emp_prenom,
                u.email          AS emp_email,
                d.nom            AS dept_nom,
                lt.nom           AS type_nom,
                lt.deductible    AS type_deductible,
                rh.nom           AS rh_nom,
                rh.prenom        AS rh_prenom
            ')
            ->join('users u',         'u.id = lr.user_id',       'left')
            ->join('departments d',   'd.id = u.department_id',  'left')
            ->join('leave_types lt',  'lt.id = lr.leave_type_id','left')
            ->join('users rh',        'rh.id = lr.traite_par',   'left');

        if (! empty($filters['statut'])) {
            $builder->where('lr.statut', $filters['statut']);
        }
        if (! empty($filters['department_id'])) {
            $builder->where('u.department_id', $filters['department_id']);
        }
        if (! empty($filters['user_id'])) {
            $builder->where('lr.user_id', $filters['user_id']);
        }
        if (! empty($filters['annee'])) {
            $builder->like('lr.date_debut', $filters['annee'], 'after');
        }

        return $builder
            ->orderBy('lr.created_at', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Une demande avec tous ses détails (pour la vue détail).
     */
    public function getWithDetails(int $id): ?array
    {
        $result = $this->db->table('leave_requests lr')
            ->select('
                lr.*,
                u.nom            AS emp_nom,
                u.prenom         AS emp_prenom,
                u.email          AS emp_email,
                u.department_id  AS emp_dept_id,
                d.nom            AS dept_nom,
                lt.nom           AS type_nom,
                lt.deductible    AS type_deductible,
                rh.nom           AS rh_nom,
                rh.prenom        AS rh_prenom
            ')
            ->join('users u',         'u.id = lr.user_id',       'left')
            ->join('departments d',   'd.id = u.department_id',  'left')
            ->join('leave_types lt',  'lt.id = lr.leave_type_id','left')
            ->join('users rh',        'rh.id = lr.traite_par',   'left')
            ->where('lr.id', $id)
            ->get()
            ->getRowArray();

        return $result ?: null;
    }

    /**
     * Nombre de demandes par statut (pour les stats dashboard).
     */
    public function countByStatus(): array
    {
        $rows = $this->db->table('leave_requests')
            ->select('statut, COUNT(*) AS total')
            ->groupBy('statut')
            ->get()
            ->getResultArray();

        $stats = ['en_attente' => 0, 'approuvee' => 0, 'refusee' => 0, 'annulee' => 0];
        foreach ($rows as $row) {
            $stats[$row['statut']] = (int) $row['total'];
        }
        return $stats;
    }

    /**
     * Absences en cours aujourd'hui (statut approuvee, date couvrant today).
     */
    public function getAbsencesToday(): array
    {
        $today = date('Y-m-d');
        return $this->db->table('leave_requests lr')
            ->select('lr.*, u.nom, u.prenom, d.nom AS dept_nom, lt.nom AS type_nom')
            ->join('users u',        'u.id = lr.user_id',        'left')
            ->join('departments d',  'd.id = u.department_id',   'left')
            ->join('leave_types lt', 'lt.id = lr.leave_type_id', 'left')
            ->where('lr.statut', 'approuvee')
            ->where('lr.date_debut <=', $today)
            ->where('lr.date_fin >=',   $today)
            ->get()
            ->getResultArray();
    }

    // ----------------------------------------------------------------
    // Actions métier : Approuver / Refuser / Annuler
    // ----------------------------------------------------------------

    /**
     * Approuver une demande + déduire le solde.
     *
     * Règles :
     *  - La demande doit être en_attente
     *  - Le type doit être déductible
     *  - jours_pris + nb_jours <= jours_attribues
     *
     * @return array{ok: bool, message: string}
     */
    public function approve(int $requestId, int $rhUserId, string $commentaire = ''): array
    {
        $demande = $this->getWithDetails($requestId);

        if (! $demande) {
            return ['ok' => false, 'message' => 'Demande introuvable.'];
        }
        if ($demande['statut'] !== 'en_attente') {
            return ['ok' => false, 'message' => 'Cette demande n\'est plus en attente.'];
        }

        $this->db->transStart();

        try {
            $annee = (int) substr($demande['date_debut'], 0, 4);

            // Vérifier et mettre à jour le solde si déductible
            if ($demande['type_deductible']) {
                $solde = $this->db->table('leave_balances')
                    ->where('user_id',       $demande['user_id'])
                    ->where('leave_type_id', $demande['leave_type_id'])
                    ->where('annee',         $annee)
                    ->get()
                    ->getRowArray();

                if (! $solde) {
                    $this->db->transRollback();
                    return ['ok' => false, 'message' => "Aucun solde défini pour l'année {$annee}."];
                }

                $restant = $solde['jours_attribues'] - $solde['jours_pris'];

                if ($demande['nb_jours'] > $restant) {
                    $this->db->transRollback();
                    return [
                        'ok'      => false,
                        'message' => "Solde insuffisant : {$restant} jour(s) disponible(s), "
                                   . "{$demande['nb_jours']} demandé(s).",
                    ];
                }

                // Déduire les jours
                $this->db->table('leave_balances')
                    ->where('user_id',       $demande['user_id'])
                    ->where('leave_type_id', $demande['leave_type_id'])
                    ->where('annee',         $annee)
                    ->update(['jours_pris' => $solde['jours_pris'] + $demande['nb_jours']]);
            }

            // Mettre à jour la demande
            $this->db->table('leave_requests')
                ->where('id', $requestId)
                ->update([
                    'statut'         => 'approuvee',
                    'commentaire_rh' => $commentaire,
                    'traite_par'     => $rhUserId,
                    'traite_le'      => date('Y-m-d H:i:s'),
                    'updated_at'     => date('Y-m-d H:i:s'),
                ]);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return ['ok' => false, 'message' => 'Erreur lors de la transaction.'];
            }

            log_message('info', "Demande #{$requestId} approuvée par RH #{$rhUserId}");
            return ['ok' => true, 'message' => 'Demande approuvée et solde mis à jour.'];

        } catch (DatabaseException $e) {
            $this->db->transRollback();
            log_message('error', "Approbation demande #{$requestId} : " . $e->getMessage());
            return ['ok' => false, 'message' => 'Erreur base de données.'];
        }
    }

    /**
     * Refuser une demande (aucune déduction de solde).
     *
     * @return array{ok: bool, message: string}
     */
    public function refuse(int $requestId, int $rhUserId, string $commentaire = ''): array
    {
        $demande = $this->find($requestId);

        if (! $demande) {
            return ['ok' => false, 'message' => 'Demande introuvable.'];
        }
        if ($demande['statut'] !== 'en_attente') {
            return ['ok' => false, 'message' => 'Cette demande n\'est plus en attente.'];
        }

        $updated = $this->update($requestId, [
            'statut'         => 'refusee',
            'commentaire_rh' => $commentaire,
            'traite_par'     => $rhUserId,
            'traite_le'      => date('Y-m-d H:i:s'),
        ]);

        if (! $updated) {
            return ['ok' => false, 'message' => 'Erreur lors du refus.'];
        }

        log_message('info', "Demande #{$requestId} refusée par RH #{$rhUserId}");
        return ['ok' => true, 'message' => 'Demande refusée.'];
    }

    /**
     * Annuler une demande approuvée → restaurer le solde.
     *
     * @return array{ok: bool, message: string}
     */
    public function cancel(int $requestId, int $byUserId): array
    {
        $demande = $this->getWithDetails($requestId);

        if (! $demande) {
            return ['ok' => false, 'message' => 'Demande introuvable.'];
        }
        if (! in_array($demande['statut'], ['en_attente', 'approuvee'], true)) {
            return ['ok' => false, 'message' => 'Seules les demandes en attente ou approuvées peuvent être annulées.'];
        }

        $this->db->transStart();

        try {
            // Restaurer le solde si la demande était approuvée et déductible
            if ($demande['statut'] === 'approuvee' && $demande['type_deductible']) {
                $annee = (int) substr($demande['date_debut'], 0, 4);
                $solde = $this->db->table('leave_balances')
                    ->where('user_id',       $demande['user_id'])
                    ->where('leave_type_id', $demande['leave_type_id'])
                    ->where('annee',         $annee)
                    ->get()
                    ->getRowArray();

                if ($solde) {
                    $newPris = max(0, $solde['jours_pris'] - $demande['nb_jours']);
                    $this->db->table('leave_balances')
                        ->where('user_id',       $demande['user_id'])
                        ->where('leave_type_id', $demande['leave_type_id'])
                        ->where('annee',         $annee)
                        ->update(['jours_pris' => $newPris]);
                }
            }

            // Mettre à jour la demande
            $this->db->table('leave_requests')
                ->where('id', $requestId)
                ->update([
                    'statut'     => 'annulee',
                    'traite_par' => $byUserId,
                    'traite_le'  => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

            $this->db->transComplete();

            if ($this->db->transStatus() === false) {
                return ['ok' => false, 'message' => 'Erreur lors de la transaction.'];
            }

            return ['ok' => true, 'message' => 'Demande annulée et solde restauré si nécessaire.'];

        } catch (DatabaseException $e) {
            $this->db->transRollback();
            log_message('error', "Annulation demande #{$requestId} : " . $e->getMessage());
            return ['ok' => false, 'message' => 'Erreur base de données.'];
        }
    }
}