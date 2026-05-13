<?php

namespace App\Controllers\Admin;

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
        $year = (int) date('Y');

        return view('common/placeholder', [
            'title'   => 'Soldes annuels',
            'message' => 'Initialisation des soldes disponible via le seeder ou une action dédiée.',
        ]);
    }

    public function initialize()
    {
        return redirect()->to('/admin/soldes')->with('success', 'Action enregistrée.');
    }
}