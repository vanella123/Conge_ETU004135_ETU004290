<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

/**
 * AuthController
 *
 * Gère : connexion, déconnexion, et redirection selon le rôle.
 *
 * Rôles supportés :
 *   - employe  → /employe/dashboard
 *   - rh       → /rh/dashboard
 *   - admin    → /admin/dashboard
 */
class AuthController extends Controller
{
    // ----------------------------------------------------------------
    // GET  /login
    // ----------------------------------------------------------------
    public function loginForm(): string
    {
        // Si déjà connecté → rediriger directement
        if (session()->has('user_id')) {
            return redirect()->to($this->redirectByRole(session('user_role')));
        }

        return view('auth/login', [
            'title' => 'Connexion — Gestion des Congés',
        ]);
    }

    // ----------------------------------------------------------------
    // POST /login
    // ----------------------------------------------------------------
    public function loginAction()
    {
        // --- Validation des champs ---
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required|min_length[6]',
        ];

        $messages = [
            'email' => [
                'required'    => 'L\'adresse email est obligatoire.',
                'valid_email' => 'Veuillez entrer une adresse email valide.',
            ],
            'password' => [
                'required'   => 'Le mot de passe est obligatoire.',
                'min_length' => 'Le mot de passe doit comporter au moins 6 caractères.',
            ],
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        // --- Recherche de l'utilisateur ---
        $userModel = new UserModel();
        $user      = $userModel->where('email', $email)
                               ->where('actif', 1)
                               ->first();

        // --- Vérification du mot de passe ---
        if (! $user || ! password_verify($password, $user['password_hash'])) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Email ou mot de passe incorrect.');
        }

        // --- Création de la session ---
        $this->createSession($user);

        // --- Journaliser la connexion (optionnel) ---
        log_message('info', "Connexion : {$user['email']} (rôle : {$user['role']})");

        // --- Redirection selon le rôle ---
        return redirect()->to($this->redirectByRole($user['role']));
    }

    // ----------------------------------------------------------------
    // GET  /logout
    // ----------------------------------------------------------------
    public function logout()
    {
        $email = session('user_email') ?? 'inconnu';
        log_message('info', "Déconnexion : {$email}");

        session()->destroy();

        return redirect()->to('/login')
            ->with('success', 'Vous avez été déconnecté avec succès.');
    }

    // ----------------------------------------------------------------
    // Méthodes privées
    // ----------------------------------------------------------------

    /**
     * Enregistre les données utilisateur dans la session.
     */
    private function createSession(array $user): void
    {
        session()->set([
            'user_id'         => $user['id'],
            'user_nom'        => $user['nom'],
            'user_prenom'     => $user['prenom'],
            'user_email'      => $user['email'],
            'user_role'       => $user['role'],
            'user_department' => $user['department_id'] ?? null,
            'is_logged_in'    => true,
        ]);

        // Régénérer l'ID de session pour éviter la fixation de session
        session()->regenerate(true);
    }

    /**
     * Retourne l'URL de redirection selon le rôle.
     */
    private function redirectByRole(string $role): string
    {
        return match ($role) {
            'admin'   => '/admin/dashboard',
            'rh'      => '/rh/dashboard',
            'employe' => '/employe/dashboard',
            default   => '/login',
        };
    }
}