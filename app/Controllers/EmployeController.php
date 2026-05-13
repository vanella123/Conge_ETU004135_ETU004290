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