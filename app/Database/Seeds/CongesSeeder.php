<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use CodeIgniter\I18n\Time;

class CongesSeeder extends Seeder
{
    public function run()
    {
        // ============================================================
        //  1. INSERTION DES DÉPARTEMENTS
        // ============================================================
        $this->seedDepartments();

        // ============================================================
        //  2. INSERTION DES TYPES DE CONGÉ
        // ============================================================
        $this->seedLeaveTypes();

        // ============================================================
        //  3. INSERTION DES UTILISATEURS
        // ============================================================
        $this->seedUsers();

        // ============================================================
        //  4. INSERTION DES SOLDES DE CONGÉS
        // ============================================================
        $this->seedLeaveBalances();

        // ============================================================
        //  5. INSERTION DES DEMANDES DE CONGÉ (EXEMPLES)
        // ============================================================
        $this->seedLeaveRequests();
    }

    /**
     * Seed des départements
     */
    private function seedDepartments()
    {
        $data = [
            [
                'nom'         => 'Informatique',
                'description' => 'Département technique et développement',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'nom'         => 'Ressources Humaines',
                'description' => 'Gestion du personnel',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'nom'         => 'Finance',
                'description' => 'Comptabilité et finances',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'nom'         => 'Marketing',
                'description' => 'Communication et marketing',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
            [
                'nom'         => 'Direction',
                'description' => 'Direction générale',
                'created_at'  => date('Y-m-d H:i:s'),
                'updated_at'  => date('Y-m-d H:i:s'),
            ],
        ];

        // Insertion avec ignore si existe
        foreach ($data as $row) {
            $this->db->table('departments')->insert($row);
        }

        echo "✓ Départements insérés\n";
    }

    /**
     * Seed des types de congé
     */
    private function seedLeaveTypes()
    {
        $data = [
            [
                'nom'           => 'Congé annuel',
                'jours_annuels' => 30,
                'deductible'    => 1,
                'description'   => 'Congé payé annuel',
                'created_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'nom'           => 'Congé maladie',
                'jours_annuels' => 15,
                'deductible'    => 1,
                'description'   => 'Arrêt maladie avec justificatif',
                'created_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'nom'           => 'Congé exceptionnel',
                'jours_annuels' => 5,
                'deductible'    => 1,
                'description'   => 'Évènements familiaux',
                'created_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'nom'           => 'Congé maternité',
                'jours_annuels' => 98,
                'deductible'    => 1,
                'description'   => 'Congé maternité légal',
                'created_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'nom'           => 'Permission spéciale',
                'jours_annuels' => 3,
                'deductible'    => 0,
                'description'   => 'Permission non déductible du solde',
                'created_at'    => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($data as $row) {
            $this->db->table('leave_types')->insert($row);
        }

        echo "✓ Types de congé insérés\n";
    }

    /**
     * Seed des utilisateurs
     */
    private function seedUsers()
    {
        $data = [
            // Admin
            [
                'nom'            => 'Admin',
                'prenom'         => 'Système',
                'email'          => 'admin@company.com',
                'password_hash'  => password_hash('Admin123!', PASSWORD_BCRYPT),
                'role'           => 'admin',
                'department_id'  => 5, // Direction
                'date_embauche'  => '2020-01-15',
                'actif'          => 1,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            // RH
            [
                'nom'            => 'Martin',
                'prenom'         => 'Sophie',
                'email'          => 'sophie.martin@company.com',
                'password_hash'  => password_hash('RH123!', PASSWORD_BCRYPT),
                'role'           => 'rh',
                'department_id'  => 2, // RH
                'date_embauche'  => '2021-03-10',
                'actif'          => 1,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'nom'            => 'Dupont',
                'prenom'         => 'Jean',
                'email'          => 'jean.dupont@company.com',
                'password_hash'  => password_hash('RH123!', PASSWORD_BCRYPT),
                'role'           => 'rh',
                'department_id'  => 2, // RH
                'date_embauche'  => '2021-06-01',
                'actif'          => 1,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            // Employés IT
            [
                'nom'            => 'Dubois',
                'prenom'         => 'Alice',
                'email'          => 'alice.dubois@company.com',
                'password_hash'  => password_hash('User123!', PASSWORD_BCRYPT),
                'role'           => 'employe',
                'department_id'  => 1, // Informatique
                'date_embauche'  => '2022-01-20',
                'actif'          => 1,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'nom'            => 'Bernard',
                'prenom'         => 'Marc',
                'email'          => 'marc.bernard@company.com',
                'password_hash'  => password_hash('User123!', PASSWORD_BCRYPT),
                'role'           => 'employe',
                'department_id'  => 1, // Informatique
                'date_embauche'  => '2021-09-15',
                'actif'          => 1,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'nom'            => 'Lefevre',
                'prenom'         => 'Claire',
                'email'          => 'claire.lefevre@company.com',
                'password_hash'  => password_hash('User123!', PASSWORD_BCRYPT),
                'role'           => 'employe',
                'department_id'  => 1, // Informatique
                'date_embauche'  => '2022-06-01',
                'actif'          => 1,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            // Employés Finance
            [
                'nom'            => 'Moreau',
                'prenom'         => 'Pierre',
                'email'          => 'pierre.moreau@company.com',
                'password_hash'  => password_hash('User123!', PASSWORD_BCRYPT),
                'role'           => 'employe',
                'department_id'  => 3, // Finance
                'date_embauche'  => '2020-03-10',
                'actif'          => 1,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'nom'            => 'Laurent',
                'prenom'         => 'Isabelle',
                'email'          => 'isabelle.laurent@company.com',
                'password_hash'  => password_hash('User123!', PASSWORD_BCRYPT),
                'role'           => 'employe',
                'department_id'  => 3, // Finance
                'date_embauche'  => '2021-02-15',
                'actif'          => 1,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            // Employés Marketing
            [
                'nom'            => 'Girard',
                'prenom'         => 'Luc',
                'email'          => 'luc.girard@company.com',
                'password_hash'  => password_hash('User123!', PASSWORD_BCRYPT),
                'role'           => 'employe',
                'department_id'  => 4, // Marketing
                'date_embauche'  => '2022-01-10',
                'actif'          => 1,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
            [
                'nom'            => 'Renard',
                'prenom'         => 'Émilie',
                'email'          => 'emilie.renard@company.com',
                'password_hash'  => password_hash('User123!', PASSWORD_BCRYPT),
                'role'           => 'employe',
                'department_id'  => 4, // Marketing
                'date_embauche'  => '2021-11-20',
                'actif'          => 1,
                'created_at'     => date('Y-m-d H:i:s'),
                'updated_at'     => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($data as $row) {
            $this->db->table('users')->insert($row);
        }

        echo "✓ Utilisateurs insérés\n";
    }

    /**
     * Seed des soldes de congés
     */
    private function seedLeaveBalances()
    {
        // On récupère tous les utilisateurs
        $users = $this->db->table('users')
            ->select('id')
            ->where('role', 'employe')
            ->get()
            ->getResultArray();

        // On récupère tous les types de congé
        $leaveTypes = $this->db->table('leave_types')
            ->select('id')
            ->get()
            ->getResultArray();

        $currentYear = date('Y');
        $data = [];

        foreach ($users as $user) {
            foreach ($leaveTypes as $type) {
                $data[] = [
                    'user_id'       => $user['id'],
                    'leave_type_id' => $type['id'],
                    'annee'         => $currentYear,
                    'jours_attribues' => 30, // Par défaut 30 jours (sera spécifique par type)
                    'jours_pris'    => 0,
                    'created_at'    => date('Y-m-d H:i:s'),
                    'updated_at'    => date('Y-m-d H:i:s'),
                ];
            }
        }

        // Batch insert pour performance
        if (!empty($data)) {
            $this->db->table('leave_balances')->insertBatch($data);
        }

        echo "✓ Soldes de congés insérés (" . count($data) . " entrées)\n";
    }

    /**
     * Seed des demandes de congé (exemples)
     */
    private function seedLeaveRequests()
    {
        // Récupérer un employé et un gestionnaire RH pour les exemples
        $employe = $this->db->table('users')
            ->where('role', 'employe')
            ->where('department_id', 1) // IT
            ->limit(1)
            ->get()
            ->getRow();

        $rh = $this->db->table('users')
            ->where('role', 'rh')
            ->limit(1)
            ->get()
            ->getRow();

        $leaveTypeId = $this->db->table('leave_types')
            ->where('nom', 'Congé annuel')
            ->get()
            ->getRow()->id ?? 1;

        if (!$employe || !$rh) {
            return;
        }

        $data = [
            [
                'user_id'       => $employe->id,
                'leave_type_id' => $leaveTypeId,
                'date_debut'    => '2026-06-15',
                'date_fin'      => '2026-06-22',
                'nb_jours'      => 6,
                'motif'         => 'Vacances d\'été',
                'statut'        => 'approuvee',
                'commentaire_rh' => 'Approuvé',
                'traite_par'    => $rh->id,
                'traite_le'     => date('Y-m-d H:i:s'),
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'user_id'       => $employe->id,
                'leave_type_id' => $leaveTypeId,
                'date_debut'    => '2026-07-01',
                'date_fin'      => '2026-07-10',
                'nb_jours'      => 8,
                'motif'         => 'Vacances familiales',
                'statut'        => 'en_attente',
                'commentaire_rh' => null,
                'traite_par'    => null,
                'traite_le'     => null,
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
            [
                'user_id'       => $employe->id,
                'leave_type_id' => $this->db->table('leave_types')
                    ->where('nom', 'Congé maladie')
                    ->get()
                    ->getRow()->id ?? 2,
                'date_debut'    => '2026-05-18',
                'date_fin'      => '2026-05-20',
                'nb_jours'      => 2,
                'motif'         => 'Maladie',
                'statut'        => 'approuvee',
                'commentaire_rh' => 'Justificatif reçu',
                'traite_par'    => $rh->id,
                'traite_le'     => date('Y-m-d H:i:s'),
                'created_at'    => date('Y-m-d H:i:s'),
                'updated_at'    => date('Y-m-d H:i:s'),
            ],
        ];

        foreach ($data as $row) {
            $this->db->table('leave_requests')->insert($row);
        }

        echo "✓ Demandes de congé (exemples) insérées (" . count($data) . " entrées)\n";
    }
}
