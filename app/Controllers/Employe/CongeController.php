<?php

namespace App\Controllers\Employe;

use CodeIgniter\Controller;
use App\Models\LeaveRequestModel;
use App\Models\LeaveTypeModel;

class CongeController extends Controller
{
    protected $leaveRequestModel;
    protected $leaveTypeModel;

    public function __construct()
    {
        $this->leaveRequestModel = new LeaveRequestModel();
        $this->leaveTypeModel    = new LeaveTypeModel();
    }

    public function index()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/login');
        }

        $userId = session('user_id');

        $conges = $this->leaveRequestModel
            ->where('employe_id', $userId)
            ->orderBy('date_debut', 'DESC')
            ->findAll();

        return view('employe/conges', [
            'title'  => 'Mes Demandes de Congés',
            'conges' => $conges,
        ]);
    }

    public function form()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/login');
        }

        $types_conges = $this->leaveTypeModel->findAll();

        return view('employe/demande', [
            'title'        => 'Nouvelle Demande de Congés',
            'types_conges' => $types_conges,
        ]);
    }

    public function submit()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/login');
        }

        $rules = [
            'type_conge_id' => 'required|numeric',
            'date_debut'    => 'required|valid_date',
            'date_fin'      => 'required|valid_date',
            'motif'         => 'permit_empty|string',
            'commentaire'   => 'permit_empty|string',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $userId  = session('user_id');
        $debut   = $this->request->getPost('date_debut');
        $fin     = $this->request->getPost('date_fin');

        // Calculer le nombre de jours (jours ouvrables)
        $nb_jours = $this->calculateWorkingDays($debut, $fin);

        $data = [
            'employe_id'    => $userId,
            'type_conge_id' => $this->request->getPost('type_conge_id'),
            'date_debut'    => $debut,
            'date_fin'      => $fin,
            'nb_jours'      => $nb_jours,
            'motif'         => $this->request->getPost('motif'),
            'commentaire'   => $this->request->getPost('commentaire'),
            'status'        => 'en_attente',
        ];

        if ($this->leaveRequestModel->save($data)) {
            return redirect()->to('employe/conges')
                ->with('success', 'Demande de congé soumise avec succès !');
        }

        return redirect()->back()
            ->with('error', 'Une erreur s\'est produite lors de la soumission.');
    }

    public function cancel($id)
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/login');
        }

        $userId = session('user_id');
        $leave  = $this->leaveRequestModel->find($id);

        if (!$leave || $leave['employe_id'] != $userId) {
            return redirect()->back()
                ->with('error', 'Demande non trouvée ou accès refusé.');
        }

        if ($leave['status'] !== 'en_attente') {
            return redirect()->back()
                ->with('error', 'Seules les demandes en attente peuvent être annulées.');
        }

        if ($this->leaveRequestModel->delete($id)) {
            return redirect()->to('employe/conges')
                ->with('success', 'Demande annulée.');
        }

        return redirect()->back()
            ->with('error', 'Erreur lors de l\'annulation.');
    }

    private function calculateWorkingDays(string $start, string $end): int
    {
        $start_date = new \DateTime($start);
        $end_date   = new \DateTime($end);
        $count      = 0;

        while ($start_date <= $end_date) {
            $day_of_week = $start_date->format('N');
            if ($day_of_week < 6) { // Lundi à vendredi
                $count++;
            }
            $start_date->modify('+1 day');
        }

        return $count;
    }
}
