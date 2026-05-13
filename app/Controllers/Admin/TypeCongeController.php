<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class TypeCongeController extends BaseController
{
    public function index()
    {
        return view('common/placeholder', [
            'title'   => 'Types de congé',
            'message' => 'Gestion des types de congé à compléter.',
        ]);
    }

    public function store()
    {
        return redirect()->to('/admin/types-conge')->with('success', 'Action enregistrée.');
    }

    public function update($id)
    {
        return redirect()->to('/admin/types-conge')->with('success', 'Action enregistrée.');
    }
}