<?php

namespace App\Controllers\Rh;

use CodeIgniter\Controller;
use App\Models\UserModel;

class EmployesController extends Controller
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/login');
        }

        if (!in_array(session('user_role'), ['rh', 'admin'])) {
            return redirect()->to('/employe/dashboard');
        }

        $employes = $this->userModel
            ->where('role', 'employe')
            ->findAll();

        return view('rh/employes', [
            'title'    => 'Gestion des Employés',
            'employes' => $employes,
        ]);
    }
}
