<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class DepartmentsSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'nom'         => 'Ressources Humaines',
                'description' => 'Département RH',
            ],
            [
                'nom'         => 'Informatique',
                'description' => 'Département IT',
            ],
            [
                'nom'         => 'Ventes',
                'description' => 'Département Ventes',
            ],
            [
                'nom'         => 'Finance',
                'description' => 'Département Finance',
            ],
        ];

        foreach ($data as $row) {
            $this->db->table('departments')->insert($row);
        }
    }
}
