<?php

namespace App\Controllers;

use App\Models\CodePromoModel;
use App\Models\DemandeCodePromoModel;
use App\Models\UtilisateurModel;

class AdminPromoController extends BaseController
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

        $promoModel = new CodePromoModel();
        $filters = [
            'montant_min' => trim((string) $this->request->getGet('montant_min')),
            'montant_max' => trim((string) $this->request->getGet('montant_max')),
            'etat' => trim((string) ($this->request->getGet('etat') ?? 'tous')),
        ];

        if (! in_array($filters['etat'], ['tous', 'disponible', 'utilise'], true)) {
            $filters['etat'] = 'tous';
        }

        return view('backoffice/promo/index', [
            'promos' => $promoModel->getAdminListing($filters),
            'filters' => $filters,
            'activeNav' => 'promos',
        ]);
    }

    public function create()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        return view('backoffice/promo/form', [
            'title' => 'Creer un code promo',
            'action' => base_url('admin/promos/store'),
            'promo' => null,
            'validation' => session('validation'),
            'activeNav' => 'promos',
        ]);
    }

    public function store()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $rules = [
            'code' => 'required|min_length[3]|max_length[50]|is_unique[code_promo.code]',
            'montant' => 'required|numeric',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $promoModel = new CodePromoModel();
        $promoModel->insert([
            'code' => strtoupper(trim((string) $this->request->getPost('code'))),
            'montant' => $this->request->getPost('montant'),
            'deja_utilise' => false,
            'id_utilisateur_utilisation' => null,
        ]);

        return redirect()->to('/admin/promos')->with('success', 'Code promo cree avec succes.');
    }

    public function edit(int $id)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $promoModel = new CodePromoModel();
        $promo = $promoModel->find($id);

        if (! $promo) {
            return redirect()->to('/admin/promos')->with('error', 'Code promo introuvable.');
        }

        return view('backoffice/promo/form', [
            'title' => 'Modifier un code promo',
            'action' => base_url('admin/promos/update/' . $id),
            'promo' => $promo,
            'validation' => session('validation'),
            'activeNav' => 'promos',
        ]);
    }

    public function update(int $id)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $promoModel = new CodePromoModel();
        $promo = $promoModel->find($id);

        if (! $promo) {
            return redirect()->to('/admin/promos')->with('error', 'Code promo introuvable.');
        }

        $rules = [
            'code' => 'required|min_length[3]|max_length[50]|is_unique[code_promo.code,id_code,' . $id . ']',
            'montant' => 'required|numeric',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('validation', $this->validator);
        }

        $promoModel->update($id, [
            'code' => strtoupper(trim((string) $this->request->getPost('code'))),
            'montant' => $this->request->getPost('montant'),
        ]);

        return redirect()->to('/admin/promos')->with('success', 'Code promo mis a jour avec succes.');
    }

    public function delete(int $id)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $promoModel = new CodePromoModel();
        $promo = $promoModel->find($id);
        if (! $promo) {
            return redirect()->to('/admin/promos')->with('error', 'Code promo introuvable.');
        }

        if ((int) ($promo['deja_utilise'] ?? 0) === 1) {
            return redirect()->to('/admin/promos')->with('error', 'Impossible de supprimer un code promo deja utilise.');
        }

        $promoModel->delete($id);

        return redirect()->to('/admin/promos')->with('success', 'Code promo supprime avec succes.');
    }

    public function validatePage()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $demandeModel = new DemandeCodePromoModel();
        $promoModel = new CodePromoModel();
        $demandes = $demandeModel->getPendingAdminListing();
        $codeMap = [];
        foreach ($promoModel->select('id_code, code, montant, deja_utilise')->findAll() as $promo) {
            $codeMap[strtoupper((string) $promo['code'])] = $promo;
        }

        $stats = [
            'en_attente' => count($demandes),
            'codes_existants' => 0,
            'codes_inexistants' => 0,
        ];

        foreach ($demandes as &$demande) {
            $code = strtoupper(trim((string) ($demande['code_saisi'] ?? '')));
            $promo = $codeMap[$code] ?? null;
            $demande['promo_match'] = $promo;
            $demande['code_existe'] = $promo !== null;

            if ($promo !== null) {
                $stats['codes_existants']++;
            } else {
                $stats['codes_inexistants']++;
            }
        }
        unset($demande);

        return view('backoffice/promo/validate', [
            'demandes' => $demandes,
            'stats' => $stats,
            'activeNav' => 'promos',
        ]);
    }

    public function approveRequest(int $id)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $demandeModel = new DemandeCodePromoModel();
        $promoModel = new CodePromoModel();
        $userModel = new UtilisateurModel();

        $demande = $demandeModel->find($id);
        if (! $demande) {
            return redirect()->to('/admin/promos/validate')->with('error', 'Demande de code promo introuvable.');
        }

        if (($demande['statut'] ?? '') !== 'en_attente') {
            return redirect()->to('/admin/promos/validate')->with('error', 'Cette demande n\'est plus en attente.');
        }

        $promo = $promoModel->findAvailableByCode((string) ($demande['code_saisi'] ?? ''));
        if ($promo === null) {
            return redirect()->to('/admin/promos/validate')->with('error', 'Ce code n\'existe pas ou il a deja ete utilise.');
        }

        $user = $userModel->find((int) $demande['id_utilisateur']);
        if ($user === null) {
            return redirect()->to('/admin/promos/validate')->with('error', 'Utilisateur introuvable pour cette demande.');
        }

        try {
            $demandeModel->approveRequestTransaction($id, $demande, $promo, $user, (int) session()->get('admin_id'));
        } catch (\RuntimeException $exception) {
            return redirect()->to('/admin/promos/validate')->with('error', $exception->getMessage());
        }

        return redirect()->to('/admin/promos/validate')->with('success', 'La demande a ete acceptee et le solde du client a ete credite.');
    }

    public function rejectRequest(int $id)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $demandeModel = new DemandeCodePromoModel();
        $demande = $demandeModel->find($id);
        if (! $demande) {
            return redirect()->to('/admin/promos/validate')->with('error', 'Demande de code promo introuvable.');
        }

        if (($demande['statut'] ?? '') !== 'en_attente') {
            return redirect()->to('/admin/promos/validate')->with('error', 'Cette demande n\'est plus en attente.');
        }

        $demandeModel->update($id, [
            'statut' => 'refuse',
            'id_admin_traitement' => (int) session()->get('admin_id'),
            'note_admin' => 'Code promo refuse.',
            'date_traitement' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to('/admin/promos/validate')->with('success', 'La demande a ete refusee.');
    }
}
