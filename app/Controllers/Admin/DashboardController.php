<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\LeaveRequestModel;
use App\Models\UserModel;

class DashboardController extends BaseController
{
    public function index()
    {
        $userModel = new UserModel();
        $requestModel = new LeaveRequestModel();

        $statsDemandes = $requestModel->countByStatus();

        $data = [
            'totalUsers' => $userModel->countAllResults(),
            'totalEmployes' => $userModel->where('role', 'employe')->countAllResults(),
            'totalRh' => $userModel->where('role', 'rh')->countAllResults(),
            'demandes' => $statsDemandes,
        ];

        return view('admin/dashboard', $data);
    }
}
