<?php

namespace App\Controllers\Employe;

use CodeIgniter\Controller;
use App\Models\LeaveRequestModel;
use App\Models\LeaveBalanceModel;

class DashboardController extends Controller
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

        // Récupérer les statistiques de l'employé
        $conges_total = $this->leaveRequestModel
            ->where('employe_id', $userId)
            ->where('YEAR(date_debut)', $year)
            ->countAllResults();

        $conges_approuves = $this->leaveRequestModel
            ->where('employe_id', $userId)
            ->where('status', 'approuvee')
            ->where('YEAR(date_debut)', $year)
            ->countAllResults();

        $conges_attente = $this->leaveRequestModel
            ->where('employe_id', $userId)
            ->where('status', 'en_attente')
            ->where('YEAR(date_debut)', $year)
            ->countAllResults();

        $conges_refuses = $this->leaveRequestModel
            ->where('employe_id', $userId)
            ->where('status', 'refusee')
            ->where('YEAR(date_debut)', $year)
            ->countAllResults();

        // Récupérer les soldes de congés
        $soldes = $this->leaveBalanceModel
            ->where('employe_id', $userId)
            ->where('annee', $year)
            ->findAll();

        // Derniers congés
        $derniers_conges = $this->leaveRequestModel
            ->where('employe_id', $userId)
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
