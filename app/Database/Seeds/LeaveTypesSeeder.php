<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class LeaveTypesSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'libelle'         => 'Congé Annuel',
                'nb_jours_par_an' => 25,
                'description'     => 'Congés annuels rémunérés',
            ],
            [
                'libelle'         => 'Congé Maladie',
                'nb_jours_par_an' => 15,
                'description'     => 'Congés pour maladie',
            ],
            [
                'libelle'         => 'Congé Spécial',
                'nb_jours_par_an' => 5,
                'description'     => 'Congés spéciaux (mariage, décès, etc.)',
            ],
            [
                'libelle'         => 'Congé sans Solde',
                'nb_jours_par_an' => 0,
                'description'     => 'Congé sans rémunération',
            ],
        ];

        foreach ($data as $row) {
            $this->db->table('leave_types')->insert($row);
        }
    }
}
