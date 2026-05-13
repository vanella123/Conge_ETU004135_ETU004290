<?php

namespace App\Controllers;

use App\Models\ActiviteSportiveModel;
use App\Models\RegimeModel;

class AdminActiviteController extends BaseController
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

        $filters = [
            'label_activite' => trim((string) $this->request->getGet('label_activite')),
            'frequence_min' => $this->request->getGet('frequence_min'),
            'frequence_max' => $this->request->getGet('frequence_max'),
        ];

        return view('backoffice/activite/index', [
            'activites' => $this->getActivityListing($filters),
            'filters' => $filters,
            'activeNav' => 'activites',
        ]);
    }

    public function create()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        return view('backoffice/activite/form', [
            'title' => 'Creer une activite sportive',
            'action' => base_url('admin/activites/store'),
            'activite' => null,
            'validation' => session('validation'),
            'activeNav' => 'activites',
        ]);
    }

    public function store()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $rules = [
            'label_activite' => 'required|min_length[3]|max_length[100]',
            'nb_par_semaine' => 'required|is_natural_no_zero',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $activiteModel = new ActiviteSportiveModel();
        $activiteModel->insert([
            'label_activite' => trim((string) $this->request->getPost('label_activite')),
            'nb_par_semaine' => (int) $this->request->getPost('nb_par_semaine'),
        ]);

        return redirect()->to('/admin/activites')->with('success', 'Activite sportive creee avec succes.');
    }

    public function show(int $id)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $activite = (new ActiviteSportiveModel())->find($id);

        if (! $activite) {
            return redirect()->to('/admin/activites')->with('error', 'Activite introuvable.');
        }

        return view('backoffice/activite/show', [
            'activite' => $activite,
            'linkedRegimes' => $this->getLinkedRegimes($id),
            'activeNav' => 'activites',
        ]);
    }

    public function edit(int $id)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $activiteModel = new ActiviteSportiveModel();
        $activite = $activiteModel->find($id);

        if (! $activite) {
            return redirect()->to('/admin/activites')->with('error', 'Activite introuvable.');
        }

        return view('backoffice/activite/form', [
            'title' => 'Modifier une activite sportive',
            'action' => base_url('admin/activites/update/' . $id),
            'activite' => $activite,
            'validation' => session('validation'),
            'activeNav' => 'activites',
        ]);
    }

    public function update(int $id)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $rules = [
            'label_activite' => 'required|min_length[3]|max_length[100]',
            'nb_par_semaine' => 'required|is_natural_no_zero',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $activiteModel = new ActiviteSportiveModel();
        if (! $activiteModel->find($id)) {
            return redirect()->to('/admin/activites')->with('error', 'Activite introuvable.');
        }

        $activiteModel->update($id, [
            'label_activite' => trim((string) $this->request->getPost('label_activite')),
            'nb_par_semaine' => (int) $this->request->getPost('nb_par_semaine'),
        ]);

        return redirect()->to('/admin/activites')->with('success', 'Activite sportive mise a jour avec succes.');
    }

    public function delete(int $id)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $activiteModel = new ActiviteSportiveModel();
        $activite = $activiteModel->find($id);

        if (! $activite) {
            return redirect()->to('/admin/activites')->with('error', 'Activite introuvable.');
        }

        $linkedCount = (new \App\Models\RegimeActiviteModel())
            ->where('id_activite', $id)
            ->countAllResults();

        if ($linkedCount > 0) {
            return redirect()->to('/admin/activites')->with(
                'error',
                'Suppression impossible: cette activite est liee a ' . $linkedCount . ' regime(s).'
            );
        }

        $activiteModel->delete($id);

        return redirect()->to('/admin/activites')->with('success', 'Activite sportive supprimee avec succes.');
    }

    public function regimeActivites(int $regimeId)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $regimeModel = new RegimeModel();
        if (! $regimeModel->find($regimeId)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Regime introuvable.'])->setStatusCode(404);
        }

        $assigned = (new \App\Models\RegimeActiviteModel())->getAssignedActivites($regimeId);

        return $this->response->setJSON([
            'status' => 'success',
            'regime_id' => $regimeId,
            'activites' => $assigned,
        ]);
    }

    public function addRegimeActivite(int $regimeId)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $rules = [
            'id_activite' => 'required|is_natural_no_zero',
        ];

        if (! $this->validate($rules)) {
            return $this->response->setJSON(['status' => 'error', 'message' => $this->validator->getErrors()])->setStatusCode(422);
        }

        $activiteId = (int) $this->request->getPost('id_activite');
        $regimeActiviteModel = new \App\Models\RegimeActiviteModel();

        if ($regimeActiviteModel->existsLink($regimeId, $activiteId)) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Cette activite est deja liee a ce regime.'])->setStatusCode(409);
        }

        $regimeActiviteModel->insert([
            'id_regime' => $regimeId,
            'id_activite' => $activiteId,
        ]);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Activite ajoutee au regime.'])->setStatusCode(201);
    }

    public function removeRegimeActivite(int $id)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $regimeActiviteModel = new \App\Models\RegimeActiviteModel();
        $row = $regimeActiviteModel->find($id);

        if (! $row) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Lien introuvable.'])->setStatusCode(404);
        }

        $regimeActiviteModel->delete($id);

        return $this->response->setJSON(['status' => 'success', 'message' => 'Lien supprime.']);
    }

    private function getActivityListing(array $filters): array
    {
        return (new ActiviteSportiveModel())->getActivityListing($filters);
    }

    private function getLinkedRegimes(int $activityId): array
    {
        return (new ActiviteSportiveModel())->getLinkedRegimes($activityId);
    }
}
