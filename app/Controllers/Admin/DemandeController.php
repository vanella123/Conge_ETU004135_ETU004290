<?php

namespace App\Controllers\Admin;

use App\Models\LeaveRequestModel;
use App\Controllers\BaseController;

class DemandeController extends BaseController
{
    private LeaveRequestModel $leaveRequestModel;

    public function __construct()
    {
        $this->leaveRequestModel = new LeaveRequestModel();
    }

    public function index()
    {
        return view('common/placeholder', [
            'title'   => 'Demandes globales',
            'message' => 'Vue d\'administration des demandes à compléter.',
        ]);
    }
}