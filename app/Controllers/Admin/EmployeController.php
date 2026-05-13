<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class EmployeController extends BaseController
{
    public function index()
    {
        $userModel = new UserModel();
        $users = $userModel->getAllWithDepartment();

        return view('admin/employes_index', [
            'users' => $users,
        ]);
    }

    public function create()
    {
        $departments = $this->db->table('departments')->orderBy('nom', 'ASC')->get()->getResultArray();

        return view('admin/employes_form', [
            'departments' => $departments,
            'user' => null,
            'formAction' => site_url('/admin/employes/nouveau'),
            'pageTitle' => 'Nouvel employe',
        ]);
    }

    public function store()
    {
        $rules = [
            'nom' => 'required|min_length[2]|max_length[100]',
            'prenom' => 'required|min_length[2]|max_length[100]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'role' => 'required|in_list[employe,rh,admin]',
            'password' => 'required|min_length[6]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nom' => (string) $this->request->getPost('nom'),
            'prenom' => (string) $this->request->getPost('prenom'),
            'email' => (string) $this->request->getPost('email'),
            'password_hash' => password_hash((string) $this->request->getPost('password'), PASSWORD_DEFAULT),
            'role' => (string) $this->request->getPost('role'),
            'department_id' => $this->request->getPost('department_id') ?: null,
            'date_embauche' => $this->request->getPost('date_embauche') ?: null,
            'actif' => $this->request->getPost('actif') ? 1 : 0,
        ];

        (new UserModel())->insert($data);

        return redirect()->to('/admin/employes')->with('success', 'Employe cree avec succes.');
    }

    public function edit(int $id)
    {
        $userModel = new UserModel();
        $user = $userModel->find($id);
        if (! $user) {
            return redirect()->to('/admin/employes')->with('error', 'Employe introuvable.');
        }

        $departments = $this->db->table('departments')->orderBy('nom', 'ASC')->get()->getResultArray();

        return view('admin/employes_form', [
            'departments' => $departments,
            'user' => $user,
            'formAction' => site_url('/admin/employes/' . $id),
            'pageTitle' => 'Modifier employe',
        ]);
    }

    public function update(int $id)
    {
        $userModel = new UserModel();
        $user = $userModel->find($id);
        if (! $user) {
            return redirect()->to('/admin/employes')->with('error', 'Employe introuvable.');
        }

        $rules = [
            'nom' => 'required|min_length[2]|max_length[100]',
            'prenom' => 'required|min_length[2]|max_length[100]',
            'email' => 'required|valid_email',
            'role' => 'required|in_list[employe,rh,admin]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = (string) $this->request->getPost('email');
        $emailExists = $userModel->where('email', $email)->where('id !=', $id)->first();
        if ($emailExists) {
            return redirect()->back()->withInput()->with('error', 'Cet email est deja utilise.');
        }

        $data = [
            'nom' => (string) $this->request->getPost('nom'),
            'prenom' => (string) $this->request->getPost('prenom'),
            'email' => $email,
            'role' => (string) $this->request->getPost('role'),
            'department_id' => $this->request->getPost('department_id') ?: null,
            'date_embauche' => $this->request->getPost('date_embauche') ?: null,
            'actif' => $this->request->getPost('actif') ? 1 : 0,
        ];

        $password = (string) $this->request->getPost('password');
        if ($password !== '') {
            $data['password_hash'] = password_hash($password, PASSWORD_DEFAULT);
        }

        $userModel->update($id, $data);

        return redirect()->to('/admin/employes')->with('success', 'Employe mis a jour.');
    }

    public function deactivate(int $id)
    {
        $userModel = new UserModel();
        $user = $userModel->find($id);
        if (! $user) {
            return redirect()->to('/admin/employes')->with('error', 'Employe introuvable.');
        }

        $userModel->update($id, ['actif' => 0]);

        return redirect()->to('/admin/employes')->with('success', 'Employe desactive.');
    }
}
