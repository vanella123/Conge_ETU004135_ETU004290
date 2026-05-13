<?php

namespace App\Controllers\Rh;

use App\Models\LeaveRequestModel;
use App\Models\UserModel;
use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    private LeaveRequestModel $leaveRequestModel;
    private UserModel $userModel;

    public function __construct()
    {
        $this->leaveRequestModel = new LeaveRequestModel();
        $this->userModel = new UserModel();
    }

    public function index()
    {
        if (! session()->has('user_id')) {
            return redirect()->to('/login');
        }

        if (! in_array((string) session('user_role'), ['rh', 'admin'], true)) {
            return redirect()->to('/employe/dashboard');
        }

        $stats = $this->leaveRequestModel->countByStatus();

        return view('rh/dashboard', [
            'title'            => 'Tableau de Bord RH',
            'demandes_attente'  => $stats['en_attente'] ?? 0,
            'approuvees_total'  => $stats['approuvee'] ?? 0,
            'refusees_total'    => $stats['refusee'] ?? 0,
            'employes_total'    => $this->userModel->where('role', 'employe')->countAllResults(),
        ]);
    }
}