<?php

namespace App\Controllers;

use App\Models\AdminModel;
use App\Models\ObjectifModel;
use App\Models\UtilisateurModel;

class AdminController extends BaseController
{
    protected AdminModel $adminModel;
    protected UtilisateurModel $utilisateurModel;
    protected ObjectifModel $objectifModel;

    public function __construct()
    {
        $this->adminModel = model(AdminModel::class);
        $this->utilisateurModel = model(UtilisateurModel::class);
        $this->objectifModel = model(ObjectifModel::class);
    }

    public function login()
    {
        return view('auth/admin_login');
    }

    public function authenticate()
    {
        $email = $this->request->getPost('email');
        $motDePasse = $this->request->getPost('mot_de_passe');

        $admin = $this->adminModel->checkAdmin($email, $motDePasse);

        if ($admin) {
            session()->set('admin_id', $admin['id_utilisateur']);
            session()->set('admin_name', $admin['nom']);

            return redirect()->to('/admin/dashboard');
        }

        session()->setFlashdata('error', 'Email ou mot de passe incorrect.');

        return redirect()->back();
    }

    public function dashboard()
    {
        if (! session()->has('admin_id')) {
            return redirect()->to('/admin/login');
        }

        $stats = $this->adminModel->getDashboardStats();

        return view('backoffice/dashboard/index', [
            'usersCount' => $stats['usersCount'],
            'goldCount' => $stats['goldCount'],
            'salesCount' => $stats['salesCount'],
            'objectivesCount' => $stats['objectivesCount'],
            'regimesCount' => $stats['regimesCount'],
            'chiffreAffaire' => $stats['chiffreAffaire'],
            'objectifs' => $stats['objectifs'],
            'recentUsers' => $stats['recentUsers'],
            'pieData' => $stats['pieData'],
            'trendLabels' => $stats['trendLabels'],
            'trendValues' => $stats['trendValues'],
            'activeNav' => 'dashboard',
        ]);
    }

    public function statsCroisees()
    {
        if (! session()->has('admin_id')) {
            return redirect()->to('/admin/login');
        }

        $stats = $this->adminModel->getCrossTabStats();

        return view('backoffice/stats-croisees/index', [
            'regimesObjectifs' => $stats['regimesObjectifs'],
            'objectifsUtilisateurs' => $stats['objectifsUtilisateurs'],
            'regimesRevenu' => $stats['regimesRevenu'],
            'objectifs' => $stats['objectifs'],
            'regimes' => $stats['regimes'],
            'activeNav' => 'stats',
        ]);
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/admin/login');
    }
}
