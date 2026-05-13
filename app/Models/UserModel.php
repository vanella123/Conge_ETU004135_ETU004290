<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * UserModel
 *
 * Modèle pour la table `users`.
 * Gère les opérations CRUD sur les utilisateurs.
 */
class UserModel extends Model
{
    protected $table            = 'users';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'nom',
        'prenom',
        'email',
        'password_hash',
        'role',
        'department_id',
        'date_embauche',
        'actif',
    ];

    // Timestamps automatiques
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation à la création/modification
    protected $validationRules = [
        'nom'    => 'required|min_length[2]|max_length[100]',
        'prenom' => 'required|min_length[2]|max_length[100]',
        'email'  => 'required|valid_email|max_length[150]',
        'role'   => 'required|in_list[employe,rh,admin]',
    ];

    protected $validationMessages = [
        'email' => [
            'valid_email' => 'L\'adresse email n\'est pas valide.',
        ],
        'role' => [
            'in_list' => 'Le rôle doit être : employe, rh ou admin.',
        ],
    ];

    // Ne jamais retourner le hash du mot de passe dans les listes
    protected $hidden = ['password_hash'];

    // ----------------------------------------------------------------
    // Méthodes métier
    // ----------------------------------------------------------------

    /**
     * Trouver un utilisateur actif par email (pour l'authentification).
     * Retourne le hash — ne pas utiliser pour les listes.
     */
    public function findActiveByEmail(string $email): ?array
    {
        return $this->select('id, nom, prenom, email, password_hash, role, department_id, actif')
                    ->where('email', $email)
                    ->where('actif', 1)
                    ->first();
    }

    /**
     * Créer un utilisateur avec mot de passe haché.
     */
    public function createUser(array $data): int|false
    {
        if (isset($data['password'])) {
            $data['password_hash'] = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            unset($data['password']);
        }

        return $this->insert($data);
    }

    /**
     * Changer le mot de passe d'un utilisateur.
     */
    public function updatePassword(int $userId, string $newPassword): bool
    {
        return $this->update($userId, [
            'password_hash' => password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]),
        ]);
    }

    /**
     * Désactiver un utilisateur (soft-delete métier).
     */
    public function deactivate(int $userId): bool
    {
        return $this->update($userId, ['actif' => 0]);
    }

    /**
     * Récupérer tous les utilisateurs avec le nom du département.
     */
    public function getAllWithDepartment(): array
    {
        return $this->select('users.*, departments.nom AS department_nom')
                    ->join('departments', 'departments.id = users.department_id', 'left')
                    ->orderBy('users.nom', 'ASC')
                    ->findAll();
    }
}