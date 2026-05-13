<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\LeaveBalanceModel;

class EmployeController extends BaseController
{
    protected $userModel;
    protected $leaveBalanceModel;

    public function __construct()
    {
        $this->userModel          = new UserModel();
        $this->leaveBalanceModel  = new LeaveBalanceModel();
    }

    public function index()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/login');
        }

        if (session('user_role') !== 'admin') {
            return redirect()->to('/employe/dashboard');
        }

        $employes = $this->userModel->findAll();

        return view('admin/employes', [
            'title'    => 'Gestion des Employés',
            'employes' => $employes,
        ]);
    }

    public function create()
    {
        if (!session()->has('user_id') || session('user_role') !== 'admin') {
            return redirect()->to('/login');
        }

        return view('admin/employe_form', [
            'title' => 'Ajouter un Employé',
        ]);
    }

    public function store()
    {
        if (!session()->has('user_id') || session('user_role') !== 'admin') {
            return redirect()->to('/login');
        }

        $rules = [
            'nom'      => 'required|min_length[2]',
            'prenom'   => 'required|min_length[2]',
            'email'    => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'role'     => 'required|in_list[employe,rh]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nom'             => $this->request->getPost('nom'),
            'prenom'          => $this->request->getPost('prenom'),
            'email'           => $this->request->getPost('email'),
            'password_hash'   => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
            'role'            => $this->request->getPost('role'),
            'date_embauche'   => $this->request->getPost('date_embauche'),
            'actif'           => 1,
        ];

        if ($this->userModel->insert($data)) {
            return redirect()->to('admin/employes')
                ->with('success', 'Employé ajouté avec succès.');
        }

        return redirect()->back()
            ->with('error', 'Erreur lors de l\'ajout.');
    }
}
