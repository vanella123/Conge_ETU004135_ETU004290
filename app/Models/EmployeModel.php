<?php

namespace App\Models;

use CodeIgniter\Model;

class EmployeModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;
    protected $allowedFields = [
        'nom',
        'prenom',
        'email',
        'password_hash',
        'role',
        'department_id',
        'date_embauche',
        'actif',
        'created_at',
        'updated_at',
    ];

    public function findByEmail(string $email): ?array
    {
        $user = $this->where('email', $email)->first();

        return is_array($user) ? $user : null;
    }

    public function findActiveByEmail(string $email): ?array
    {
        $user = $this->where('email', $email)
            ->where('actif', 1)
            ->first();

        return is_array($user) ? $user : null;
    }
}