<?php

namespace App\Controllers\Rh;

use App\Models\LeaveBalanceModel;
use App\Controllers\BaseController;

class SoldeController extends BaseController
{
    private LeaveBalanceModel $leaveBalanceModel;

    public function __construct()
    {
        $this->leaveBalanceModel = new LeaveBalanceModel();
    }

    public function index()
    {
        if (! session()->has('user_id')) {
            return redirect()->to('/login');
        }

        if (! in_array((string) session('user_role'), ['rh', 'admin'], true)) {
            return redirect()->to('/employe/dashboard');
        }

        $year = (int) date('Y');

        return view('rh/soldes', [
            'title'   => 'Soldes employés',
            'soldes'  => $this->leaveBalanceModel->getAllForYear($year),
            'annee'   => $year,
        ]);
    }
}