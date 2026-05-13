<?php

namespace App\Controllers;

use App\Models\OptionHistoriqueModel;
use App\Models\OptionModel;
use App\Models\UtilisateurModel;

class AdminOptionController extends BaseController
{
    private function requireAdmin()
    {
        if (! session()->has('admin_id')) {
            return redirect()->to('/admin/login');
        }

        return null;
    }

    public function index()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $optionModel = new OptionModel();
        $utilisateurModel = new UtilisateurModel();
        $options = $optionModel->getAdminListing();

        return view('backoffice/option/index', [
            'options' => $options,
            'goldMembersCount' => count($utilisateurModel->getGoldMembers()),
            'activeNav' => 'options',
        ]);
    }

    public function show(int $id)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $optionModel = new OptionModel();
        $historiqueModel = new OptionHistoriqueModel();
        $utilisateurModel = new UtilisateurModel();
        $option = $optionModel->find($id);

        if (! $option) {
            return redirect()->to('/admin/options')->with('error', 'Option introuvable.');
        }

        return view('backoffice/option/show', [
            'option' => $option,
            'historique' => $historiqueModel->getByOptionId($id),
            'goldMembers' => strtolower((string) ($option['nom_option'] ?? '')) === 'gold' ? $utilisateurModel->getGoldMembers() : [],
            'activeNav' => 'options',
        ]);
    }

    public function create()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        return view('backoffice/option/form', [
            'title' => 'Creer une option',
            'action' => base_url('admin/options/store'),
            'option' => null,
            'validation' => session('validation'),
            'activeNav' => 'options',
        ]);
    }

    public function store()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $data = $this->validatedOptionPayload();
        if ($data === null) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $optionModel = new OptionModel();
        $historiqueModel = new OptionHistoriqueModel();
        $id = $optionModel->insert($data, true);

        $historiqueModel->insert([
            'id_option' => (int) $id,
            'prix' => $data['prix_unique'],
            'reduction_pourcentage' => $data['reduction_pourcentage'],
            'nb_regimes_achetes' => $data['nb_regimes_achetes'],
            'date_debut' => $this->request->getPost('date_debut') ?: date('Y-m-d'),
        ]);

        return redirect()->to('/admin/options')->with('success', 'Option creee avec succes.');
    }

    public function edit(int $id)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $optionModel = new OptionModel();
        $option = $optionModel->find($id);

        if (! $option) {
            return redirect()->to('/admin/options')->with('error', 'Option introuvable.');
        }

        return view('backoffice/option/form', [
            'title' => 'Modifier une option',
            'action' => base_url('admin/options/update/' . $id),
            'option' => $option,
            'validation' => session('validation'),
            'activeNav' => 'options',
        ]);
    }

    public function update(int $id)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $optionModel = new OptionModel();
        $historiqueModel = new OptionHistoriqueModel();
        $option = $optionModel->find($id);

        if (! $option) {
            return redirect()->to('/admin/options')->with('error', 'Option introuvable.');
        }

        $data = $this->validatedOptionPayload($id);
        if ($data === null) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $optionModel->update($id, $data);
        $historiqueModel->insert([
            'id_option' => $id,
            'prix' => $data['prix_unique'],
            'reduction_pourcentage' => $data['reduction_pourcentage'],
            'nb_regimes_achetes' => $data['nb_regimes_achetes'],
            'date_debut' => $this->request->getPost('date_debut') ?: date('Y-m-d'),
        ]);

        return redirect()->to('/admin/options/view/' . $id)->with('success', 'Option mise a jour avec succes.');
    }

    public function delete(int $id)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $optionModel = new OptionModel();
        $historiqueModel = new OptionHistoriqueModel();
        $utilisateurModel = new UtilisateurModel();
        $option = $optionModel->find($id);

        if (! $option) {
            return redirect()->to('/admin/options')->with('error', 'Option introuvable.');
        }

        if (strtolower((string) ($option['nom_option'] ?? '')) === 'gold' && count($utilisateurModel->getGoldMembers()) > 0) {
            return redirect()->to('/admin/options')->with('error', 'Impossible de supprimer Gold tant que des utilisateurs sont deja Gold.');
        }

        $historiqueModel->where('id_option', $id)->delete();
        $optionModel->delete($id);

        return redirect()->to('/admin/options')->with('success', 'Option supprimee avec succes.');
    }

    private function validatedOptionPayload(?int $id = null): ?array
    {
        $codeRule = 'required|min_length[2]|max_length[50]';
        if ($id === null) {
            $codeRule .= '|is_unique[option.nom_option]';
        } else {
            $codeRule .= '|is_unique[option.nom_option,id_option,' . $id . ']';
        }

        $rules = [
            'nom_option' => $codeRule,
            'nb_regimes_achetes' => 'required|is_natural',
            'prix_unique' => 'required|numeric',
            'reduction_pourcentage' => 'required|numeric',
            'date_debut' => 'permit_empty|valid_date[Y-m-d]',
        ];

        if (! $this->validate($rules)) {
            return null;
        }

        return [
            'nom_option' => ucfirst(strtolower(trim((string) $this->request->getPost('nom_option')))),
            'nb_regimes_achetes' => (int) $this->request->getPost('nb_regimes_achetes'),
            'prix_unique' => (float) $this->request->getPost('prix_unique'),
            'reduction_pourcentage' => (float) $this->request->getPost('reduction_pourcentage'),
        ];
    }
}
