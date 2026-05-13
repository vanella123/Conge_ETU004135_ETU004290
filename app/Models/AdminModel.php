<?php 
namespace App\Models;
use CodeIgniter\Model;
class AdminModel extends Model
{
    protected $table = 'utilisateur';
    protected $primaryKey = 'id_utilisateur';
    protected $returnType = 'array';
    protected $allowedFields = [
        'nom',
        'email',
        'mot_de_passe',
        'role_utilisateur',
    ];
    public function checkAdmin($email, $mot_de_passe): ?array
    {
        $admin = $this->where('email', $email)
            ->where('role_utilisateur', 'admin')
            ->first();

        if ($admin && password_verify($mot_de_passe, $admin['mot_de_passe'])) {
            return $admin;
        }

        return null;
    }

    public function getDashboardStats(): array
    {
        $db = \Config\Database::connect();
        $utilisateurModel = new UtilisateurModel();
        $objectifModel = new ObjectifModel();

        $usersCount = $utilisateurModel->countAllResults();
        $goldCount = $utilisateurModel->where('is_gold', 1)->countAllResults();
        $salesCount = $db->table('commande')->countAllResults();
        $objectivesCount = $objectifModel->countAllResults();
        $regimesCount = $db->table('regime')->countAllResults();
        $chiffreAffaireRow = $db->table('commande')->selectSum('montant_paye')->get()->getRowArray();
        $chiffreAffaire = empty($chiffreAffaireRow['montant_paye']) ? 0 : $chiffreAffaireRow['montant_paye'];

        $objectifs = $objectifModel
            ->select('objectif.id_objectif, objectif.label_objectif, COUNT(utilisateur.id_utilisateur) AS total', false)
            ->join('utilisateur', 'utilisateur.id_objectif = objectif.id_objectif', 'left')
            ->groupBy('objectif.id_objectif')
            ->orderBy('objectif.id_objectif', 'ASC')
            ->findAll();

        $recentUsers = $utilisateurModel
            ->select('nom, email')
            ->orderBy('id_utilisateur', 'DESC')
            ->findAll(5);

        $pieColors = ['#1f8f6a', '#2563eb', '#f59e0b', '#ef4444', '#8b5cf6', '#0ea5e9'];
        $pieData = [];
        foreach ($objectifs as $index => $objectif) {
            $pieData[] = [
                'label' => $objectif['label_objectif'],
                'total' => (int) $objectif['total'],
                'color' => $pieColors[$index % count($pieColors)],
            ];
        }

        $trendLabels = [];
        $trendValues = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = date('Y-m', strtotime("-{$i} months"));
            $trendLabels[] = date('M Y', strtotime("-{$i} months"));
            $row = $db->table('commande')
                ->selectSum('montant_paye')
                ->where("DATE_FORMAT(date_achat, '%Y-%m')", $date)
                ->get()
                ->getRowArray();
            $trendValues[] = (float) ($row['montant_paye'] ?? 0);
        }

        return [
            'usersCount' => $usersCount,
            'goldCount' => $goldCount,
            'salesCount' => $salesCount,
            'objectivesCount' => $objectivesCount,
            'regimesCount' => $regimesCount,
            'chiffreAffaire' => $chiffreAffaire,
            'objectifs' => $objectifs,
            'recentUsers' => $recentUsers,
            'pieData' => $pieData,
            'trendLabels' => $trendLabels,
            'trendValues' => $trendValues,
        ];
    }

    public function getCrossTabStats(): array
    {
        $db = \Config\Database::connect();

        // Tableau croisé: Régimes × Objectifs (nombre d'achats)
        $regimesObjectifsData = $db->table('commande c')
            ->select('r.id_regime, r.nom_regime, o.id_objectif, o.label_objectif, COUNT(c.id_commande) AS total', false)
            ->join('regime r', 'r.id_regime = c.id_regime')
            ->join('utilisateur u', 'u.id_utilisateur = c.id_utilisateur')
            ->join('objectif o', 'o.id_objectif = u.id_objectif')
            ->groupBy('r.id_regime, o.id_objectif')
            ->orderBy('r.id_regime, o.id_objectif')
            ->get()
            ->getResultArray();

        // Tableau croisé: Objectifs × Utilisateurs (types d'objectifs)
        $objectifsUtilisatairesData = $db->table('utilisateur u')
            ->select('o.id_objectif, o.label_objectif, COUNT(u.id_utilisateur) AS total, SUM(CASE WHEN u.is_gold = 1 THEN 1 ELSE 0 END) AS gold_count', false)
            ->join('objectif o', 'o.id_objectif = u.id_objectif', 'left')
            ->groupBy('o.id_objectif')
            ->orderBy('o.id_objectif')
            ->get()
            ->getResultArray();

        // Revenus par régime
        $regimesRevenuData = $db->table('commande c')
            ->select('r.id_regime, r.nom_regime, SUM(c.montant_paye) AS total_revenu, COUNT(c.id_commande) AS ventes', false)
            ->join('regime r', 'r.id_regime = c.id_regime')
            ->groupBy('r.id_regime')
            ->orderBy('total_revenu DESC')
            ->get()
            ->getResultArray();

        // Tous les régimes pour la pivot table
        $regimes = $db->table('regime')->orderBy('nom_regime')->get()->getResultArray();

        // Tous les objectifs pour la pivot table
        $objectifs = $db->table('objectif')->orderBy('id_objectif')->get()->getResultArray();

        // Construire le tableau croisé Régimes × Objectifs
        $regimesObjectifsMatrix = [];
        foreach ($regimes as $regime) {
            $row = ['id_regime' => $regime['id_regime'], 'nom_regime' => $regime['nom_regime']];
            foreach ($objectifs as $objectif) {
                $found = array_filter($regimesObjectifsData, fn($item) => 
                    $item['id_regime'] == $regime['id_regime'] && $item['id_objectif'] == $objectif['id_objectif']
                );
                $row['obj_' . $objectif['id_objectif']] = $found ? (int) reset($found)['total'] : 0;
            }
            $regimesObjectifsMatrix[] = $row;
        }

        return [
            'regimesObjectifs' => $regimesObjectifsMatrix,
            'objectifsUtilisateurs' => $objectifsUtilisatairesData,
            'regimesRevenu' => $regimesRevenuData,
            'objectifs' => $objectifs,
            'regimes' => $regimes,
        ];
    }
}
?>
