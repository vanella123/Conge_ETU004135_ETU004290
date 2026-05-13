<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nom'              => 'Admin',
                'prenom'           => 'System',
                'email'            => 'admin@techmada.mg',
                'password_hash'    => password_hash('admin123', PASSWORD_BCRYPT),
                'role'             => 'admin',
                'department_id'    => 1,
                'date_embauche'    => '2020-01-01',
                'actif'            => 1,
                'created_at'       => date('Y-m-d H:i:s'),
            ],
            [
                'nom'              => 'Dupont',
                'prenom'           => 'Marie',
                'email'            => 'marie.dupont@techmada.mg',
                'password_hash'    => password_hash('rh123456', PASSWORD_BCRYPT),
                'role'             => 'rh',
                'department_id'    => 1,
                'date_embauche'    => '2021-03-15',
                'actif'            => 1,
                'created_at'       => date('Y-m-d H:i:s'),
            ],
            [
                'nom'              => 'Martin',
                'prenom'           => 'Jean',
                'email'            => 'jean.martin@techmada.mg',
                'password_hash'    => password_hash('emp123456', PASSWORD_BCRYPT),
                'role'             => 'employe',
                'department_id'    => 2,
                'date_embauche'    => '2022-06-01',
                'actif'            => 1,
                'created_at'       => date('Y-m-d H:i:s'),
            ],
            [
                'nom'              => 'Durand',
                'prenom'           => 'Sophie',
                'email'            => 'sophie.durand@techmada.mg',
                'password_hash'    => password_hash('emp123456', PASSWORD_BCRYPT),
                'role'             => 'employe',
                'department_id'    => 3,
                'date_embauche'    => '2021-09-10',
                'actif'            => 1,
                'created_at'       => date('Y-m-d H:i:s'),
            ],
            [
                'nom'              => 'Bernard',
                'prenom'           => 'Pierre',
                'email'            => 'pierre.bernard@techmada.mg',
                'password_hash'    => password_hash('emp123456', PASSWORD_BCRYPT),
                'role'             => 'employe',
                'department_id'    => 4,
                'date_embauche'    => '2020-11-20',
                'actif'            => 1,
                'created_at'       => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($data as $row) {
            $this->db->table('users')->insert($row);
        }
    }
}
