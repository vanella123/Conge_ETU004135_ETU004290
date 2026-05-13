<?php

namespace App\Models;

use CodeIgniter\Model;

class DemandeCodePromoModel extends Model
{
    protected $table = 'demande_code_promo';
    protected $primaryKey = 'id_demande_code_promo';
    protected $returnType = 'array';
    protected $allowedFields = [
        'id_utilisateur',
        'code_saisi',
        'statut',
        'id_admin_traitement',
        'note_admin',
        'date_demande',
        'date_traitement',
    ];

    public function hasPendingRequest(int $userId, string $code): bool
    {
        return $this->where('id_utilisateur', $userId)
            ->where('code_saisi', strtoupper(trim($code)))
            ->where('statut', 'en_attente')
            ->countAllResults() > 0;
    }

    public function getPendingAdminListing(): array
    {
        $builder = $this->db->table('demande_code_promo d');
        $builder->select([
            'd.id_demande_code_promo',
            'd.code_saisi',
            'd.statut',
            'd.note_admin',
            'd.date_demande',
            'd.date_traitement',
            'u.id_utilisateur',
            'u.nom AS utilisateur_nom',
            'u.email AS utilisateur_email',
        ]);
        $builder->join('utilisateur u', 'u.id_utilisateur = d.id_utilisateur', 'left');
        $builder->where('d.statut', 'en_attente');
        $builder->orderBy('d.date_demande', 'DESC');

        return $builder->get()->getResultArray();
    }

    public function countPending(): int
    {
        return $this->where('statut', 'en_attente')->countAllResults();
    }

    public function getUserHistory(int $userId): array
    {
        return $this->where('id_utilisateur', $userId)
            ->orderBy('date_demande', 'DESC')
            ->findAll();
    }

    public function approveRequestTransaction(int $demandeId, array $demande, array $promo, array $user, int $adminId): void
    {
        $userModel = new UtilisateurModel();
        $promoModel = new CodePromoModel();

        $newArgent = (float) ($user['argent'] ?? 0) + (float) ($promo['montant'] ?? 0);

        $this->db->transStart();

        $userModel->update((int) $user['id_utilisateur'], [
            'argent' => $newArgent,
        ]);

        $promoModel->update((int) $promo['id_code'], [
            'deja_utilise' => true,
            'id_utilisateur_utilisation' => (int) $user['id_utilisateur'],
        ]);

        $this->update($demandeId, [
            'statut' => 'accepte',
            'id_admin_traitement' => $adminId,
            'note_admin' => 'Code promo accepte.',
            'date_traitement' => date('Y-m-d H:i:s'),
        ]);

        $this->db->transComplete();

        if (! $this->db->transStatus()) {
            throw new \RuntimeException('La validation a echoue. Merci de reessayer.');
        }
    }
}
