<?php

namespace App\Controllers\Rh;

use CodeIgniter\Controller;
use App\Models\LeaveRequestModel;
use App\Models\LeaveBalanceModel;
use App\Models\UserModel;

class DemandesController extends Controller
{
    protected $leaveRequestModel;
    protected $leaveBalanceModel;
    protected $userModel;

    public function __construct()
    {
        $this->leaveRequestModel  = new LeaveRequestModel();
        $this->leaveBalanceModel  = new LeaveBalanceModel();
        $this->userModel          = new UserModel();
    }

    public function index()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/login');
        }

        if (!in_array(session('user_role'), ['rh', 'admin'])) {
            return redirect()->to('/employe/dashboard')
                ->with('error', 'Accès refusé.');
        }

        // Demandes en attente
        $demandes_attente = $this->leaveRequestModel
            ->select('leave_requests.*, users.nom as employe_nom')
            ->join('users', 'leave_requests.employe_id = users.id')
            ->where('leave_requests.status', 'en_attente')
            ->orderBy('leave_requests.created_at', 'ASC')
            ->findAll();

        // Statistiques
        $approuvees_total = $this->leaveRequestModel
            ->where('status', 'approuvee')
            ->countAllResults();

        $refusees_total = $this->leaveRequestModel
            ->where('status', 'refusee')
            ->countAllResults();

        return view('rh/demandes', [
            'title'             => 'Demandes en Attente',
            'demandes_attente'  => $demandes_attente,
            'approuvees_total'  => $approuvees_total,
            'refusees_total'    => $refusees_total,
        ]);
    }

    public function approuver()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/login');
        }

        if (!in_array(session('user_role'), ['rh', 'admin'])) {
            return redirect()->to('/employe/dashboard');
        }

        $demande_id = $this->request->getPost('demande_id');
        $commentaire = $this->request->getPost('commentaire');

        $leave = $this->leaveRequestModel->find($demande_id);
        if (!$leave) {
            return redirect()->back()->with('error', 'Demande non trouvée.');
        }

        // Mettre à jour le statut
        $this->leaveRequestModel->update($demande_id, [
            'status'          => 'approuvee',
            'commentaire_rh'  => $commentaire,
            'approuve_par_id' => session('user_id'),
            'approuve_le'     => date('Y-m-d H:i:s'),
        ]);

        // Mettre à jour le solde
        $this->updateLeaveBalance($leave['employe_id'], $leave['type_conge_id'], $leave['nb_jours']);

        return redirect()->back()
            ->with('success', 'Demande approuvée.');
    }

    public function refuser()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/login');
        }

        if (!in_array(session('user_role'), ['rh', 'admin'])) {
            return redirect()->to('/employe/dashboard');
        }

        $demande_id = $this->request->getPost('demande_id');
        $motif_refus = $this->request->getPost('motif_refus');

        $leave = $this->leaveRequestModel->find($demande_id);
        if (!$leave) {
            return redirect()->back()->with('error', 'Demande non trouvée.');
        }

        // Mettre à jour le statut
        $this->leaveRequestModel->update($demande_id, [
            'status'          => 'refusee',
            'commentaire_rh'  => $motif_refus,
            'refuse_par_id'   => session('user_id'),
            'refuse_le'       => date('Y-m-d H:i:s'),
        ]);

        return redirect()->back()
            ->with('success', 'Demande refusée.');
    }

    private function updateLeaveBalance($employee_id, $leave_type_id, $days)
    {
        $year   = date('Y');
        $balance = $this->leaveBalanceModel
            ->where('employe_id', $employee_id)
            ->where('type_conge_id', $leave_type_id)
            ->where('annee', $year)
            ->first();

        if ($balance) {
            $this->leaveBalanceModel->update($balance['id'], [
                'jours_pris' => $balance['jours_pris'] + $days,
            ]);
        }
    }
}
