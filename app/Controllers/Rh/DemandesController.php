<?php

namespace App\Controllers\Rh;

use App\Controllers\BaseController;
use App\Models\LeaveRequestModel;
use App\Models\LeaveBalanceModel;
use App\Models\UserModel;

class DemandesController extends BaseController
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

        $demandes_attente = $this->leaveRequestModel->getAllWithDetails([
            'statut' => 'en_attente',
        ]);

        $stats = $this->leaveRequestModel->countByStatus();

        return view('rh/demandes', [
            'title'             => 'Demandes en Attente',
            'demandes_attente'  => $demandes_attente,
            'approuvees_total'  => $stats['approuvee'] ?? 0,
            'refusees_total'    => $stats['refusee'] ?? 0,
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

        $demande_id = (int) $this->request->getPost('demande_id');
        $commentaire = (string) $this->request->getPost('commentaire');

        $result = $this->leaveRequestModel->approve($demande_id, (int) session('user_id'), $commentaire);

        if (! $result['ok']) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->back()
            ->with('success', $result['message']);
    }

    public function refuser()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/login');
        }

        if (!in_array(session('user_role'), ['rh', 'admin'])) {
            return redirect()->to('/employe/dashboard');
        }

        $demande_id = (int) $this->request->getPost('demande_id');
        $motif_refus = (string) $this->request->getPost('motif_refus');

        $result = $this->leaveRequestModel->refuse($demande_id, (int) session('user_id'), $motif_refus);

        if (! $result['ok']) {
            return redirect()->back()->with('error', $result['message']);
        }

        return redirect()->back()
            ->with('success', $result['message']);
    }
}
