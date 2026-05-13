<?php

namespace App\Controllers\Employe;

use App\Controllers\BaseController;
use App\Models\LeaveBalanceModel;

class SoldeController extends BaseController
{
    protected $leaveBalanceModel;

    public function __construct()
    {
        $this->leaveBalanceModel = new LeaveBalanceModel();
    }

    public function index()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/login');
        }

        $userId = (int) session('user_id');
        $year   = date('Y');

        $soldes = $this->leaveBalanceModel->getByUser($userId, (int) $year);

        return view('employe/soldes', [
            'title'  => 'Mes Soldes de Congés',
            'soldes' => $soldes,
        ]);
    }
}
