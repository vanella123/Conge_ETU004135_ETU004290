<?php

namespace App\Controllers\RH;

use App\Controllers\BaseController;
use App\Models\LeaveRequestModel;
use App\Models\LeaveBalanceModel;

/**
 * RH\DemandeController
 *
 * Gestion des demandes de congé côté RH.
 *
 * Routes :
 *   GET  /rh/demandes                       → liste avec filtres
 *   GET  /rh/demandes/:id                   → détail
 *   POST /rh/demandes/:id/approuver         → approuver + MAJ solde
 *   POST /rh/demandes/:id/refuser           → refuser
 *   POST /rh/demandes/:id/annuler           → annuler (restaure solde)
 */
class DemandeController extends BaseController
{
    private LeaveRequestModel $requestModel;
    private LeaveBalanceModel $balanceModel;

    public function __construct()
    {
        $this->requestModel = new LeaveRequestModel();
        $this->balanceModel = new LeaveBalanceModel();
    }

    // ----------------------------------------------------------------
    // GET /rh/demandes
    // ----------------------------------------------------------------
    public function index(): string
    {
        $statut       = $this->request->getGet('statut') ?? '';
        $deptId       = (int) ($this->request->getGet('department_id') ?? 0);
        $annee        = (int) ($this->request->getGet('annee') ?? date('Y'));

        $filters = [];
        if ($statut)  $filters['statut']        = $statut;
        if ($deptId)  $filters['department_id'] = $deptId;
        if ($annee)   $filters['annee']          = $annee;

        $demandes     = $this->requestModel->getAllWithDetails($filters);
        $departments  = $this->db()->table('departments')->orderBy('nom')->get()->getResultArray();
        $stats        = $this->requestModel->countByStatus();

        return view('rh/demandes/index', [
            'title'       => 'Gestion des demandes',
            'demandes'    => $demandes,
            'departments' => $departments,
            'stats'       => $stats,
            'filters'     => ['statut' => $statut, 'department_id' => $deptId, 'annee' => $annee],
            'annees'      => range(date('Y'), date('Y') - 2),
        ]);
    }

    // ----------------------------------------------------------------
    // GET /rh/demandes/:id
    // ----------------------------------------------------------------
    public function show(int $id): string
    {
        $demande = $this->requestModel->getWithDetails($id);

        if (! $demande) {
            return redirect()->to('/rh/demandes')->with('error', 'Demande introuvable.');
        }

        // Solde de l'employé pour ce type de congé
        $annee  = (int) substr($demande['date_debut'], 0, 4);
        $soldes = $this->balanceModel->getByUser($demande['user_id'], $annee);

        return view('rh/demandes/show', [
            'title'   => "Demande #{$id}",
            'demande' => $demande,
            'soldes'  => $soldes,
        ]);
    }

    // ----------------------------------------------------------------
    // POST /rh/demandes/:id/approuver
    // ----------------------------------------------------------------
    public function approve(int $id)
    {
        $commentaire = $this->request->getPost('commentaire_rh') ?? '';
        $rhUserId    = session('user_id');

        $result = $this->requestModel->approve($id, $rhUserId, $commentaire);

        if ($result['ok']) {
            return redirect()->to('/rh/demandes')->with('success', $result['message']);
        }

        return redirect()->to("/rh/demandes/{$id}")->with('error', $result['message']);
    }

    // ----------------------------------------------------------------
    // POST /rh/demandes/:id/refuser
    // ----------------------------------------------------------------
    public function refuse(int $id)
    {
        $commentaire = $this->request->getPost('commentaire_rh') ?? '';

        if (empty(trim($commentaire))) {
            return redirect()->to("/rh/demandes/{$id}")
                ->with('error', 'Un commentaire est obligatoire pour refuser une demande.');
        }

        $rhUserId = session('user_id');
        $result   = $this->requestModel->refuse($id, $rhUserId, $commentaire);

        if ($result['ok']) {
            return redirect()->to('/rh/demandes')->with('success', $result['message']);
        }

        return redirect()->to("/rh/demandes/{$id}")->with('error', $result['message']);
    }

    // ----------------------------------------------------------------
    // POST /rh/demandes/:id/annuler
    // ----------------------------------------------------------------
    public function cancel(int $id)
    {
        $rhUserId = session('user_id');
        $result   = $this->requestModel->cancel($id, $rhUserId);

        if ($result['ok']) {
            return redirect()->to('/rh/demandes')->with('success', $result['message']);
        }

        return redirect()->to("/rh/demandes/{$id}")->with('error', $result['message']);
    }

    // ----------------------------------------------------------------
    // Helper
    // ----------------------------------------------------------------
    private function db()
    {
        return \Config\Database::connect();
    }
}