<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class DepartementController extends BaseController
{
    public function index()
    {
        return view('common/placeholder', [
            'title'   => 'Départements',
            'message' => 'Gestion des départements à compléter.',
        ]);
    }

    public function store()
    {
        return redirect()->to('/admin/departements')->with('success', 'Action enregistrée.');
    }

    public function update($id)
    {
        return redirect()->to('/admin/departements')->with('success', 'Action enregistrée.');
    }

    public function delete($id)
    {
        return redirect()->to('/admin/departements')->with('success', 'Action enregistrée.');
    }
}