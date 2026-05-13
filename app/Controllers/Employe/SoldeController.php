<?php

namespace App\Controllers\Employe;

use CodeIgniter\Controller;
use App\Models\LeaveBalanceModel;

class SoldeController extends Controller
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

        $userId = session('user_id');
        $year   = date('Y');

        $soldes = $this->leaveBalanceModel
            ->where('employe_id', $userId)
            ->where('annee', $year)
            ->findAll();

        return view('employe/soldes', [
            'title'  => 'Mes Soldes de Congés',
            'soldes' => $soldes,
        ]);
    }
}
