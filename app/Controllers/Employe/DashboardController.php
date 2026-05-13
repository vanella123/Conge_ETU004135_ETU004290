<?php

namespace App\Controllers\Employe;

use App\Controllers\BaseController;
use App\Models\LeaveRequestModel;
use App\Models\LeaveBalanceModel;

class DashboardController extends BaseController
{
    protected $leaveRequestModel;
    protected $leaveBalanceModel;

    public function __construct()
    {
        $this->leaveRequestModel  = new LeaveRequestModel();
        $this->leaveBalanceModel  = new LeaveBalanceModel();
    }

    public function index()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/login');
        }

        $userId = session('user_id');
        $year   = date('Y');

        $conges_total = $this->leaveRequestModel
            ->where('user_id', $userId)
            ->like('date_debut', $year, 'after')
            ->countAllResults();

        $conges_approuves = $this->leaveRequestModel
            ->where('user_id', $userId)
            ->where('statut', 'approuvee')
            ->like('date_debut', $year, 'after')
            ->countAllResults();

        $conges_attente = $this->leaveRequestModel
            ->where('user_id', $userId)
            ->where('statut', 'en_attente')
            ->like('date_debut', $year, 'after')
            ->countAllResults();

        $conges_refuses = $this->leaveRequestModel
            ->where('user_id', $userId)
            ->where('statut', 'refusee')
            ->like('date_debut', $year, 'after')
            ->countAllResults();

        $soldes = $this->leaveBalanceModel->getByUser((int) $userId, (int) $year);

        $derniers_conges = $this->leaveRequestModel
            ->select('leave_requests.*, leave_types.nom AS type_nom')
            ->join('leave_types', 'leave_types.id = leave_requests.leave_type_id', 'left')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->findAll();

        return view('employe/dashboard', [
            'title'              => 'Mon Tableau de Bord',
            'conges_total'       => $conges_total,
            'conges_approuves'   => $conges_approuves,
            'conges_attente'     => $conges_attente,
            'conges_refuses'     => $conges_refuses,
            'soldes'             => $soldes,
            'derniers_conges'    => $derniers_conges,
        ]);
    }
}
