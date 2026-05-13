<?php

namespace App\Controllers;

use App\Models\UtilisateurModel;

class AdminUtilisateurController extends BaseController
{
    public function index()
    {
        if (!session()->has('admin_id')) {
            return redirect()->to('/admin/login');
        }

        $utilisateurModel = new UtilisateurModel();
        
        // Fetch all users except admin
        $utilisateurs = $utilisateurModel->where('role_utilisateur !=', 'admin')
                                         ->orderBy('id_utilisateur', 'DESC')
                                         ->findAll();

        return view('backoffice/utilisateur/index', [
            'utilisateurs' => $utilisateurs,
            'activeNav'    => 'utilisateurs',
        ]);
    }

    public function show($id)
    {
        if (!session()->has('admin_id')) {
            return redirect()->to('/admin/login');
        }

        $utilisateurModel = new UtilisateurModel();
        
        $user = $utilisateurModel->find($id);

        if (!$user || $user['role_utilisateur'] === 'admin') {
            session()->setFlashdata('error', 'Utilisateur introuvable.');
            return redirect()->to('/admin/utilisateurs');
        }

        $commandes = $utilisateurModel->getUserCommandes($id);
        $imc = $utilisateurModel->calculateImc((float) ($user['poids_kg'] ?? 0), (float) ($user['taille_cm'] ?? 0));

        return view('backoffice/utilisateur/show', [
            'user'      => $user,
            'commandes' => $commandes,
            'imc'       => $imc,
            'activeNav' => 'utilisateurs',
        ]);
    }
}
