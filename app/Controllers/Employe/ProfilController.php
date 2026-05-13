<?php

namespace App\Controllers\Employe;

use App\Models\UserModel;
use App\Controllers\BaseController;

class ProfilController extends BaseController
{
    private UserModel $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        if (! session()->has('user_id')) {
            return redirect()->to('/login');
        }

        $user = $this->userModel->find((int) session('user_id'));

        return view('employe/profil', [
            'title' => 'Mon Profil',
            'user'  => $user,
        ]);
    }

    public function update()
    {
        if (! session()->has('user_id')) {
            return redirect()->to('/login');
        }

        $rules = [
            'nom'     => 'required|min_length[2]|max_length[100]',
            'prenom'  => 'required|min_length[2]|max_length[100]',
            'email'   => 'required|valid_email|max_length[150]',
            'password' => 'permit_empty|min_length[6]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nom'    => $this->request->getPost('nom'),
            'prenom' => $this->request->getPost('prenom'),
            'email'  => $this->request->getPost('email'),
        ];

        $password = (string) $this->request->getPost('password');
        if ($password !== '') {
            $data['password_hash'] = password_hash($password, PASSWORD_BCRYPT);
        }

        $this->userModel->update((int) session('user_id'), $data);

        session()->set([
            'user_nom'    => $data['nom'],
            'user_prenom' => $data['prenom'],
            'user_email'  => $data['email'],
        ]);

        return redirect()->to('/employe/profil')->with('success', 'Profil mis à jour.');
    }
}