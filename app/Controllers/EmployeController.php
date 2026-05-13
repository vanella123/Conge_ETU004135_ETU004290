<?php

namespace App\Controllers;

use App\Models\EmployeModel;

class EmployeController extends BaseController
{
    public function login()
    {
        if ($this->CheckConnecte()) {
            return redirect()->to('/dashboard');
        }

        return view('employe/login');
    }

    // Affiche le formulaire de demande de congé
    public function congeForm()
    {
        if (! $this->CheckConnecte()) return redirect()->to('/login');

        $leaveTypes = $this->db->table('leave_types')->orderBy('nom')->get()->getResultArray();

        return view('employe/conge_form', [
            'leaveTypes' => $leaveTypes,
        ]);
    }

    // Soumission d'une demande de congé
    public function submitConge()
    {
        if (! $this->CheckConnecte()) return redirect()->to('/login');

        $rules = [
            'leave_type_id' => 'required|is_natural_no_zero',
            'date_debut' => 'required|valid_date[Y-m-d]',
            'date_fin' => 'required|valid_date[Y-m-d]',
            'nb_jours' => 'required|is_natural_no_zero',
        ];

        if (! $this->validate($rules)) {
            return $this->validationErrorResponse('Veuillez corriger le formulaire.', $this->validator->getErrors());
        }

        $userId = (int) session()->get('id_utilisateur');
        $leaveTypeId = (int) $this->request->getPost('leave_type_id');
        $dateDebut = $this->request->getPost('date_debut');
        $dateFin = $this->request->getPost('date_fin');
        $nbJours = (int) $this->request->getPost('nb_jours');

        if ($dateDebut > $dateFin) {
            return $this->validationErrorResponse('La date de début doit être antérieure ou égale à la date de fin.', ['date_debut' => 'Date invalide']);
        }

        $this->db->table('leave_requests')->insert([
            'user_id' => $userId,
            'leave_type_id' => $leaveTypeId,
            'date_debut' => $dateDebut,
            'date_fin' => $dateFin,
            'nb_jours' => $nbJours,
            'motif' => $this->request->getPost('motif') ?: null,
            'statut' => 'en_attente',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return $this->successResponse('Demande de congé soumise.', '/conges');
    }

    // Liste des demandes de l'utilisateur
    public function listConges()
    {
        if (! $this->CheckConnecte()) return redirect()->to('/login');

        $userId = (int) session()->get('id_utilisateur');
        $requests = $this->db->table('leave_requests lr')
            ->select('lr.*, lt.nom AS leave_type')
            ->join('leave_types lt', 'lt.id = lr.leave_type_id', 'left')
            ->where('lr.user_id', $userId)
            ->orderBy('lr.created_at', 'DESC')
            ->get()
            ->getResultArray();

        return view('employe/conges_list', [
            'requests' => $requests,
        ]);
    }

    // Annulation d'une demande
    public function cancelConge($id = null)
    {
        if (! $this->CheckConnecte()) return redirect()->to('/login');
        $userId = (int) session()->get('id_utilisateur');
        $id = (int) $id;

        $req = $this->db->table('leave_requests')->where('id', $id)->where('user_id', $userId)->get()->getRowArray();
        if (! $req) return $this->errorResponse('Demande introuvable.', '/conges');

        // Only allow cancel if still en_attente
        if ($req['statut'] !== 'en_attente') {
            return $this->errorResponse('Impossible d\'annuler une demande déjà traitée.', '/conges');
        }

        $this->db->table('leave_requests')->where('id', $id)->update(['statut' => 'annulee', 'updated_at' => date('Y-m-d H:i:s')]);

        return $this->successResponse('Demande annulée.', '/conges');
    }

    // Affiche les soldes de congé par type pour l'année (param ?annee=YYYY)
    public function soldes()
    {
        if (! $this->CheckConnecte()) return redirect()->to('/login');

        $userId = (int) session()->get('id_utilisateur');
        $annee = (int) ($this->request->getGet('annee') ?: date('Y'));

        $balances = $this->db->table('leave_balances lb')
            ->select('lb.*, lt.nom AS leave_type, lt.jours_annuels')
            ->join('leave_types lt', 'lt.id = lb.leave_type_id', 'left')
            ->where('lb.user_id', $userId)
            ->where('lb.annee', $annee)
            ->get()
            ->getResultArray();

        return view('employe/soldes', [
            'balances' => $balances,
            'annee' => $annee,
        ]);
    }

    // Edition du profil (réduit)
    public function editProfile()
    {
        if (! $this->CheckConnecte()) return redirect()->to('/login');
        $userId = (int) session()->get('id_utilisateur');
        $user = $this->db->table('users')->where('id', $userId)->get()->getRowArray();
        if (! $user) return redirect()->to('/login');

        return view('employe/profile_edit', ['user' => $user]);
    }

    public function updateProfile()
    {
        if (! $this->CheckConnecte()) return redirect()->to('/login');
        $userId = (int) session()->get('id_utilisateur');

        $rules = [
            'nom' => 'required|min_length[2]|max_length[100]',
            'prenom' => 'required|min_length[2]|max_length[100]',
            'email' => 'required|valid_email',
        ];

        if (! $this->validate($rules)) {
            return $this->validationErrorResponse('Erreur de validation.', $this->validator->getErrors());
        }

        $data = [
            'nom' => $this->request->getPost('nom'),
            'prenom' => $this->request->getPost('prenom'),
            'email' => $this->request->getPost('email'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        // change password si fourni
        $pwd = $this->request->getPost('mot_de_passe');
        if (! empty($pwd)) {
            $data['password_hash'] = password_hash($pwd, PASSWORD_DEFAULT);
        }

        $this->db->table('users')->where('id', $userId)->update($data);

        // refresh session
        session()->set('nom', $data['nom']);

        return $this->successResponse('Profil mis à jour.', '/profil');
    }

    public function checkEmployeExist()
    {
        if ($this->CheckConnecte()) {
            return $this->successResponse('', '/dashboard');
        }

        $rules = [
            'email' => 'required|valid_email',
            'mot_de_passe' => 'required|min_length[1]',
        ];

        if (! $this->validate($rules)) {
            return $this->validationErrorResponse(
                'Veuillez verifier vos identifiants.',
                $this->validator->getErrors()
            );
        }

        $email = trim((string) $this->request->getPost('email'));
        $motDePasse = (string) $this->request->getPost('mot_de_passe');

        $employeModel = new EmployeModel();
        $user = $employeModel->findActiveByEmail($email);

        if ($user === null || ! password_verify($motDePasse, (string) $user['password_hash'])) {
            return $this->validationErrorResponse('Email ou mot de passe incorrect.', [
                'email' => 'Email ou mot de passe incorrect.',
                'mot_de_passe' => 'Email ou mot de passe incorrect.',
            ]);
        }

        session()->regenerate(true);
        session()->set([
            'is_logged_in' => true,
            'id_utilisateur' => (int) $user['id'],
            'nom' => (string) $user['nom'],
            'prenom' => (string) ($user['prenom'] ?? ''),
            'email' => (string) $user['email'],
            'role' => (string) $user['role'],
            'department_id' => isset($user['department_id']) ? (int) $user['department_id'] : null,
            'actif' => (int) ($user['actif'] ?? 1),
        ]);

        return $this->successResponse('Connexion réussie.', '/dashboard');
    }

    public function deconnexion()
    {
        $session = session();
        $session->remove([
            'is_logged_in',
            'id_utilisateur',
            'nom',
            'prenom',
            'email',
            'role',
            'department_id',
            'actif',
        ]);
        $session->destroy();

        return view('employe/deconnexion', [
            'redirectTo' => site_url('/connexion'),
        ]);
    }

    public function CheckConnecte(): bool
    {
        return (bool) session()->get('is_logged_in');
    }

    private function successResponse(string $message, string $redirectTo)
    {
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => $message,
                'redirect' => $redirectTo,
            ]);
        }

        if ($message === '') {
            return redirect()->to($redirectTo);
        }

        return redirect()->to($redirectTo)->with('success', $message);
    }

    private function validationErrorResponse(string $message, array $errors = [])
    {
        if ($this->request->isAJAX()) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => $message,
                'errors' => $errors,
            ]);
        }

        return redirect()->back()->withInput()->with('error', $message)->with('errors', $errors);
    }
}