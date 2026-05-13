<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class DashboardController extends BaseController
{
    public function index()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/login');
        }

        if (session('user_role') !== 'admin') {
            return redirect()->to('/employe/dashboard');
        }

        return view('admin/dashboard', [
            'title' => 'Tableau de Bord Admin',
        ]);
    }
}
