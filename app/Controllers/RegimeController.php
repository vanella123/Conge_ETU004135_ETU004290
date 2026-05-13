<?php

namespace App\Controllers;

use App\Models\ActiviteSportiveModel;
use App\Models\CommandeModel;
use App\Models\DureeRegimeModel;
use App\Models\ImcModel;
use App\Models\ObjectifModel;
use App\Models\OptionModel;
use App\Models\RegimeActiviteModel;
use App\Models\RegimeModel;
use App\Models\UtilisateurModel;
use CodeIgniter\Exceptions\PageNotFoundException;

require_once APPPATH . 'Libraries/fpdf186/fpdf.php';

class RegimeController extends BaseController
{
    public function index()
    {
        $regimeModel = new RegimeModel();
        $dureeModel = new DureeRegimeModel();
        $objectifModel = new ObjectifModel();
        $regimeActiviteModel = new RegimeActiviteModel();

        $duree = $this->request->getGet('duree');
        $duree = is_numeric($duree) ? (int) $duree : null;

        $objectifParam = $this->request->getGet('objectif');

        if ($objectifParam !== null) {
            // User explicitly chose a filter (either a number or "Tous" which is empty string)
            $objectif = ($objectifParam !== '') ? (int) $objectifParam : null;
        } else {
            // No filter applied yet, try fallback to user's default objective
            $objectif = null;
            if (session()->get('is_logged_in')) {
                $userModel = new UtilisateurModel();
                $user = $userModel->find((int) session()->get('id_utilisateur'));
                if (! empty($user['id_objectif'])) {
                    $objectif = (int) $user['id_objectif'];
                }
            }
        }
        
        $userId = session()->get('is_logged_in') ? (int) session()->get('id_utilisateur') : null;
        $regimes = $regimeModel->getFilteredList($duree, $objectif, $userId);
        $regimeDurees = $dureeModel->getAllGroupedByRegime();
        $dureeOptions = $dureeModel->getDistinctDurations();
        $objectifOptions = $objectifModel->orderBy('id_objectif', 'ASC')->findAll();
        $activityCounts = $regimeActiviteModel->getCountsByRegime();

        $regimes = array_map(function (array $regime) use ($activityCounts) {
            $variation = (float) $regime['variation_mensuelle_kg'];
            $regime['variation_label'] = $this->formatVariationLabel($variation);
            $regime['activity_count'] = $activityCounts[(int) $regime['id_regime']] ?? 0;
            $regime['composition_gradient'] = $this->buildCompositionGradient($regime);
            $regime['composition_legend'] = $this->buildCompositionLegend($regime);
            $regime['composition_tooltip'] = $this->buildCompositionTooltip($regime);
            return $regime;
        }, $regimes);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'regimes' => $regimes,
                'regimeDurees' => $regimeDurees,
            ]);
        }

        return view('frontoffice/regime/index', [
            'regimes' => $regimes,
            'regimeDurees' => $regimeDurees,
            'dureeOptions' => $dureeOptions,
            'selectedDuree' => $duree,
            'selectedObjectif' => $objectif,
            'objectifOptions' => $objectifOptions,
        ]);
    }

    public function show(int $id)
    {
        $regimeModel = new RegimeModel();
        $dureeModel = new DureeRegimeModel();
        $regimeActiviteModel = new RegimeActiviteModel();
        $activiteModel = new ActiviteSportiveModel();
        $imcModel = new ImcModel();
        $userModel = new UtilisateurModel();

        $regime = $regimeModel->find($id);
        if ($regime === null) {
            throw PageNotFoundException::forPageNotFound('Régime introuvable');
        }

        $variation = (float) $regime['variation_mensuelle_kg'];
        $regime['variation_label'] = $this->formatVariationLabel($variation);
        $objectiveLabel = $this->getObjectiveLabel($variation);

        $durees = $dureeModel->getByRegimeId($id);
        $activiteIds = $regimeActiviteModel->getActiviteIdsForRegime($id);
        $activites = $activiteModel->getByIds($activiteIds);

        $user = null;
        $discountPercent = 0.0;
        if (session()->get('is_logged_in')) {
            $user = $userModel->find((int) session()->get('id_utilisateur'));
            if (! empty($user['is_gold'])) {
                $optionModel = new OptionModel();
                $gold = $optionModel->getGoldOption();
                $discountPercent = (float) ($gold['reduction_pourcentage'] ?? 0);
            }
        }

        $imcIdeal = $imcModel->getIdealRange();
        $imcIdealMin = $imcIdeal !== null ? (float) $imcIdeal['imc_min'] : null;
        $imcIdealMax = $imcIdeal !== null ? (float) $imcIdeal['imc_max'] : null;

        $durees = array_map(function (array $duree) use ($user, $variation, $imcIdealMin, $imcIdealMax) {
            $status = $this->evaluateObjectiveStatus($user, $variation, (int) $duree['nb_jours'], $imcIdealMin, $imcIdealMax);
            $duree['objective_status'] = $status;
            return $duree;
        }, $durees);

        $regime['composition_gradient'] = $this->buildCompositionGradient($regime);
        $regime['composition_legend'] = $this->buildCompositionLegend($regime);
        $regime['composition_tooltip'] = $this->buildCompositionTooltip($regime);

        return view('frontoffice/regime/show', [
            'regime' => $regime,
            'durees' => $durees,
            'activites' => $activites,
            'objectiveLabel' => $objectiveLabel,
            'user' => $user,
            'discountPercent' => $discountPercent,
            'imcIdealMin' => $imcIdealMin,
            'imcIdealMax' => $imcIdealMax,
        ]);
    }

    private function buildCompositionGradient(array $regime): string
    {
        $colors = $this->getCompositionColors();
        $segments = [
            ['color' => $colors['viande'], 'value' => (float) ($regime['pourcentage_viande'] ?? 0)],
            ['color' => $colors['poisson'], 'value' => (float) ($regime['pourcentage_poisson'] ?? 0)],
            ['color' => $colors['volaille'], 'value' => (float) ($regime['pourcentage_volaille'] ?? 0)],
        ];

        $parts = [];
        $cumulative = 0.0;
        foreach ($segments as $segment) {
            if ($segment['value'] <= 0) {
                continue;
            }
            $next = $cumulative + $segment['value'];
            $parts[] = $segment['color'] . ' ' . $cumulative . '% ' . $next . '%';
            $cumulative = $next;
        }

        return $parts !== [] ? implode(', ', $parts) : '#e9eef3 0% 100%';
    }

    private function evaluateObjectiveStatus(?array $user, float $monthlyVariation, int $days, ?float $imcIdealMin, ?float $imcIdealMax): ?array
    {
        if ($user === null) {
            return null;
        }

        $variation = $monthlyVariation * ($days / 30);
        $poidsActuel = (float) ($user['poids_kg'] ?? 0);
        $poidsObjectif = $user['poids_objectif'] !== null ? (float) $user['poids_objectif'] : null;
        $tailleCm = (float) ($user['taille_cm'] ?? 0);
        $objectifId = (int) ($user['id_objectif'] ?? 0);

        $ok = null;
        if ($objectifId === 1 && $poidsObjectif !== null) {
            $cible = $poidsObjectif - $poidsActuel;
            $ok = $variation <= $cible;
        } elseif ($objectifId === 2 && $poidsObjectif !== null) {
            $cible = $poidsObjectif - $poidsActuel;
            $ok = $variation >= $cible;
        } elseif ($objectifId === 3 && $tailleCm > 0 && $imcIdealMin !== null && $imcIdealMax !== null) {
            $tailleM = $tailleCm / 100;
            $nouveauPoids = $poidsActuel + $variation;
            $imc = $nouveauPoids / ($tailleM * $tailleM);
            $ok = $imc >= $imcIdealMin && $imc <= $imcIdealMax;
        }

        if ($ok === null) {
            return null;
        }

        return [
            'label' => $ok ? 'Compatible avec votre objectif' : 'Non compatible avec votre objectif',
            'tone' => $ok ? 'success' : 'warning',
        ];
    }

    private function getCompositionColors(): array
    {
        return [
            'viande' => '#ef4444',
            'poisson' => '#3b82f6',
            'volaille' => '#f59e0b',
        ];
    }

    private function formatPercent(float $value): string
    {
        return number_format($value, 2, ',', ' ');
    }

    private function buildCompositionLegend(array $regime): array
    {
        $colors = $this->getCompositionColors();

        return [
            [
                'key' => 'viande',
                'label' => 'Viande',
                'value' => (float) ($regime['pourcentage_viande'] ?? 0),
                'value_label' => $this->formatPercent((float) ($regime['pourcentage_viande'] ?? 0)),
                'color' => $colors['viande'],
            ],
            [
                'key' => 'poisson',
                'label' => 'Poisson',
                'value' => (float) ($regime['pourcentage_poisson'] ?? 0),
                'value_label' => $this->formatPercent((float) ($regime['pourcentage_poisson'] ?? 0)),
                'color' => $colors['poisson'],
            ],
            [
                'key' => 'volaille',
                'label' => 'Volaille',
                'value' => (float) ($regime['pourcentage_volaille'] ?? 0),
                'value_label' => $this->formatPercent((float) ($regime['pourcentage_volaille'] ?? 0)),
                'color' => $colors['volaille'],
            ],
        ];
    }

    private function buildCompositionTooltip(array $regime): string
    {
        $legend = $this->buildCompositionLegend($regime);
        $parts = [];

        foreach ($legend as $item) {
            $parts[] = $item['label'] . ' ' . $item['value_label'] . '%';
        }

        return implode(' | ', $parts);
    }

    private function buildWeightGraphData(float $monthlyVariation): array
    {
        $points = [
            ['days' => 0, 'value' => 0.0],
            ['days' => 30, 'value' => $monthlyVariation],
            ['days' => 60, 'value' => $monthlyVariation * 2],
            ['days' => 90, 'value' => $monthlyVariation * 3],
        ];

        $graphWidth = 640;
        $graphHeight = 280;
        $padLeft = 54;
        $padRight = 24;
        $padTop = 24;
        $padBottom = 58;
        $plotWidth = $graphWidth - $padLeft - $padRight;
        $plotHeight = $graphHeight - $padTop - $padBottom;

        $values = array_map(static fn (array $point): float => (float) $point['value'], $points);
        $minValue = min(array_merge([0.0], $values));
        $maxValue = max(array_merge([0.0], $values));
        if ($minValue === $maxValue) {
            $minValue -= 1;
            $maxValue += 1;
        }
        $range = $maxValue - $minValue;

        $linePoints = [];
        foreach ($points as $point) {
            $days = (float) $point['days'];
            $x = $padLeft + (($days / 90) * $plotWidth);
            $y = $padTop + (($maxValue - (float) $point['value']) / $range) * $plotHeight;
            $linePoints[] = [
                'x' => $x,
                'y' => $y,
                'days' => $days,
                'value' => (float) $point['value'],
            ];
        }

        return [
            'width' => $graphWidth,
            'height' => $graphHeight,
            'padLeft' => $padLeft,
            'padRight' => $padRight,
            'padTop' => $padTop,
            'padBottom' => $padBottom,
            'plotWidth' => $plotWidth,
            'plotHeight' => $plotHeight,
            'minValue' => $minValue,
            'maxValue' => $maxValue,
            'range' => $range,
            'points' => $points,
            'linePoints' => $linePoints,
        ];
    }

    public function exportPdf(int $id)
    {
        if (! session()->get('is_logged_in')) {
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter.');
        }

        $regimeModel = new RegimeModel();
        $dureeModel = new DureeRegimeModel();
        $regimeActiviteModel = new RegimeActiviteModel();
        $activiteModel = new ActiviteSportiveModel();
        $userModel = new UtilisateurModel();

        $regime = $regimeModel->find($id);
        if ($regime === null) {
            throw PageNotFoundException::forPageNotFound('Régime introuvable');
        }

        $user = $userModel->find((int) session()->get('id_utilisateur'));
        if ($user === null) {
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter.');
        }

        $durees = $dureeModel->getByRegimeId($id);
        $selectedDuree = null;
        foreach ($durees as $duree) {
            if ($selectedDuree === null || (int) $duree['nb_jours'] > (int) $selectedDuree['nb_jours']) {
                $selectedDuree = $duree;
            }
        }

        $variation = (float) $regime['variation_mensuelle_kg'];
        $estimated = $selectedDuree !== null
            ? $variation * ((int) $selectedDuree['nb_jours'] / 30)
            : $variation;

        $objectifDiff = null;
        if (isset($user['poids_objectif']) && $user['poids_objectif'] !== null) {
            $objectifDiff = (float) $user['poids_kg'] - (float) $user['poids_objectif'];
        }

        $objectiveText = 'Non défini';
        if ($objectifDiff !== null) {
            $absDiff = abs($objectifDiff);
            $formattedDiff = rtrim(rtrim(number_format($absDiff, 2, ',', ' '), '0'), ',');
            if ($objectifDiff > 0) {
                $objectiveText = 'perdre ' . $formattedDiff . 'kg';
            } elseif ($objectifDiff < 0) {
                $objectiveText = 'prendre ' . $formattedDiff . 'kg';
            } else {
                $objectiveText = 'maintenir le poids';
            }
        }

        $activiteIds = $regimeActiviteModel->getActiviteIdsForRegime($id);
        $activites = $activiteModel->getByIds($activiteIds);

        require_once APPPATH . 'Libraries/fpdf186/fpdf.php';
        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true, 20);

        // Header (Banner)
        $pdf->SetFillColor(41, 128, 185); // Blue
        $pdf->Rect(0, 0, 210, 40, 'F');
        $pdf->SetFont('Arial', 'B', 22);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetXY(10, 15);
        $pdf->Cell(0, 10, utf8_decode('PROPOSITION DE REGIME'), 0, 1, 'C');
        $pdf->Ln(20);

        // Reset Text Color
        $pdf->SetTextColor(50, 50, 50);

        // Calculate IMC
        $tailleCm = (float) $user['taille_cm'];
        $poidsKg = (float) $user['poids_kg'];
        $imcText = 'N/A';
        if ($tailleCm > 0 && $poidsKg > 0) {
            $imc = $poidsKg / (($tailleCm / 100) ** 2);
            $imcText = number_format($imc, 1, ',', '') . ' kg/m²';
        }

        // Section: Utilisateur
        $pdf->SetFillColor(236, 240, 241); // Light gray
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, utf8_decode('  Informations Utilisateur'), 0, 1, 'L', true);
        $pdf->Ln(2);

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(40, 8, utf8_decode('Nom:'), 0, 0);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 8, utf8_decode($user['nom']), 0, 1);
        $pdf->SetFont('Arial', '', 12);
        
        $pdf->Cell(40, 8, utf8_decode('Poids / Taille:'), 0, 0);
        $pdf->Cell(0, 8, utf8_decode($poidsKg . ' kg / ' . $tailleCm . ' cm'), 0, 1);

        $pdf->Cell(40, 8, utf8_decode('IMC Actuel:'), 0, 0);
        $pdf->Cell(0, 8, utf8_decode($imcText), 0, 1);
        
        $pdf->Cell(40, 8, utf8_decode('Objectif:'), 0, 0);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(231, 76, 60); // Red
        $pdf->Cell(0, 8, utf8_decode($objectiveText), 0, 1);
        $pdf->SetTextColor(50, 50, 50);
        $pdf->Ln(5);

        // Section: Régime
        $pdf->SetFillColor(212, 239, 223); // Light green
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, utf8_decode('  Détails du Régime : ' . $regime['nom_regime']), 0, 1, 'L', true);
        $pdf->Ln(2);

        $pdf->SetFont('Arial', '', 12);
        
        // Variation
        $pdf->Cell(40, 8, utf8_decode('Objectif visé:'), 0, 0);
        $pdf->Cell(0, 8, utf8_decode($this->formatVariationLabel((float) $regime['variation_mensuelle_kg'])), 0, 1);
        
        // Proportions
        $pdf->Cell(40, 8, utf8_decode('Composition:'), 0, 0);
        $pdf->Cell(0, 8, utf8_decode(
            $regime['pourcentage_viande'] . '% Viande, ' .
            $regime['pourcentage_poisson'] . '% Poisson, ' .
            $regime['pourcentage_volaille'] . '% Volaille'
        ), 0, 1);

        if ($selectedDuree !== null) {
            $pdf->Cell(40, 8, utf8_decode('Durée max:'), 0, 0);
            $pdf->Cell(0, 8, utf8_decode($selectedDuree['nb_jours'] . ' jours'), 0, 1);
        }

        $estimatedLabel = rtrim(rtrim(number_format($estimated, 2, ',', ' '), '0'), ',');
        $estimatedSign = $estimated > 0 ? '+' : '';
        $pdf->Cell(40, 8, utf8_decode('Résultat estimé:'), 0, 0);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(39, 174, 96); // Dark green
        $pdf->Cell(0, 8, utf8_decode($estimatedSign . $estimatedLabel . ' kg'), 0, 1);
        $pdf->SetTextColor(50, 50, 50);
        $pdf->Ln(5);

        // Section Activites sportives
        $pdf->SetFillColor(252, 243, 207); // Light yellow
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, utf8_decode('  Activités Sportives Recommandées'), 0, 1, 'L', true);
        $pdf->Ln(2);

        $pdf->SetFont('Arial', '', 12);
        if (empty($activites)) {
            $pdf->Cell(0, 8, utf8_decode('Aucune activité recommandée.'), 0, 1);
        } else {
            foreach ($activites as $activite) {
                // simple bullet
                $pdf->Cell(10, 8, '-', 0, 0, 'C');
                $pdf->Cell(0, 8, utf8_decode($activite['label_activite'] . ' (' . $activite['nb_par_semaine'] . 'x par semaine)'), 0, 1);
            }
        }
        $pdf->Ln(10);

        // Footer / Prix
        $pdf->SetFillColor(236, 240, 241);
        $y = $pdf->GetY();
        $pdf->Rect(10, $y, 190, 25, 'F');
        $pdf->SetY($y + 5);
        $pdf->SetFont('Arial', 'B', 13);
        
        if ($selectedDuree !== null) {
            $priceLabel = number_format((float) $selectedDuree['prix'], 0, ',', ' ');
            $pdf->Cell(0, 8, utf8_decode('Prix indicatif : ' . $priceLabel . ' Ar'), 0, 1, 'C');
        } else {
            $pdf->Cell(0, 8, utf8_decode('Prix indicatif : Non disponible'), 0, 1, 'C');
        }
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->SetTextColor(100, 100, 100);
        $pdf->Cell(0, 6, utf8_decode('Document généré le ' . date('d/m/Y H:i')), 0, 1, 'C');

        $pdfData = $pdf->Output('S');

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="regime-' . $id . '.pdf"')
            ->setBody($pdfData);
    }

    public function myRegimes()
    {
        if (! session()->get('is_logged_in')) {
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter.');
        }

        $commandeModel = new CommandeModel();
        $purchases = $commandeModel->getPurchasedRegimesByUserId((int) session()->get('id_utilisateur'));

        $purchases = array_map(function (array $purchase) {
            $variation = (float) ($purchase['variation_mensuelle_kg'] ?? 0);
            $purchase['variation_label'] = $this->formatVariationLabel($variation);
            $purchase['objective_label'] = $this->getObjectiveLabel($variation);
            return $purchase;
        }, $purchases);

        return view('frontoffice/regime/my_regimes', [
            'purchases' => $purchases,
        ]);
    }

    public function myRegimeDetail(int $commandeId)
    {
        if (! session()->get('is_logged_in')) {
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter.');
        }

        $commandeModel = new CommandeModel();
        $regimeActiviteModel = new RegimeActiviteModel();
        $activiteModel = new ActiviteSportiveModel();
        $userModel = new UtilisateurModel();

        $userId = (int) session()->get('id_utilisateur');
        $purchase = $commandeModel->getPurchaseById($commandeId, $userId);

        if ($purchase === null) {
            throw PageNotFoundException::forPageNotFound('Achat introuvable');
        }

        $variation = (float) ($purchase['variation_mensuelle_kg'] ?? 0);
        $purchase['variation_label'] = $this->formatVariationLabel($variation);
        $purchase['objective_label'] = $this->getObjectiveLabel($variation);
        $purchase['composition_gradient'] = $this->buildCompositionGradient($purchase);
        $purchase['composition_legend'] = $this->buildCompositionLegend($purchase);
        $purchase['composition_tooltip'] = $this->buildCompositionTooltip($purchase);

        $activiteIds = $regimeActiviteModel->getActiviteIdsForRegime((int) $purchase['id_regime']);
        $activites = $activiteModel->getByIds($activiteIds);

        $user = $userModel->find($userId);
        $weightGraph = $this->buildWeightGraphData($variation);

        return view('frontoffice/regime/my_regime_detail', [
            'purchase' => $purchase,
            'activites' => $activites,
            'user' => $user,
            'weightGraph' => $weightGraph,
        ]);
    }

    public function exportRegimePdf(int $commandeId)
    {
        if (! session()->get('is_logged_in')) {
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter.');
        }

        $commandeModel = new CommandeModel();
        $regimeActiviteModel = new RegimeActiviteModel();
        $activiteModel = new ActiviteSportiveModel();
        $userModel = new UtilisateurModel();

        $userId = (int) session()->get('id_utilisateur');
        $purchase = $commandeModel->getPurchaseById($commandeId, $userId);

        if ($purchase === null) {
            throw PageNotFoundException::forPageNotFound('Achat introuvable');
        }

        $user = $userModel->find($userId);
        if ($user === null) {
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter.');
        }

        $activiteIds = $regimeActiviteModel->getActiviteIdsForRegime((int) $purchase['id_regime']);
        $activites = $activiteModel->getByIds($activiteIds);

        $variation = (float) ($purchase['variation_mensuelle_kg'] ?? 0);
        $estimated = $variation * ((int) ($purchase['nb_jours'] ?? 0) / 30);

        $objectifDiff = null;
        if (isset($user['poids_objectif']) && $user['poids_objectif'] !== null) {
            $objectifDiff = (float) $user['poids_kg'] - (float) $user['poids_objectif'];
        }

        $objectiveText = 'Non défini';
        if ($objectifDiff !== null) {
            $absDiff = abs($objectifDiff);
            $formattedDiff = rtrim(rtrim(number_format($absDiff, 2, ',', ' '), '0'), ',');
            if ($objectifDiff > 0) {
                $objectiveText = 'perdre ' . $formattedDiff . 'kg';
            } elseif ($objectifDiff < 0) {
                $objectiveText = 'prendre ' . $formattedDiff . 'kg';
            } else {
                $objectiveText = 'maintenir le poids';
            }
        }

        $pdf = new \FPDF();
        $pdf->AddPage();
        $pdf->SetAutoPageBreak(true, 20);

        // Header (Banner)
        $pdf->SetFillColor(46, 204, 113); // Emerald Green
        $pdf->Rect(0, 0, 210, 40, 'F');
        $pdf->SetFont('Arial', 'B', 22);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetXY(10, 15);
        $pdf->Cell(0, 10, utf8_decode('RECAPITULATIF DE VOTRE ACHAT'), 0, 1, 'C');
        $pdf->Ln(20);

        // Reset Text Color
        $pdf->SetTextColor(50, 50, 50);

        // Calculate IMC
        $tailleCm = (float) $user['taille_cm'];
        $poidsKg = (float) $user['poids_kg'];
        $imcText = 'N/A';
        if ($tailleCm > 0 && $poidsKg > 0) {
            $imc = $poidsKg / (($tailleCm / 100) ** 2);
            $imcText = number_format($imc, 1, ',', '') . ' kg/m²';
        }

        // Section: Utilisateur
        $pdf->SetFillColor(236, 240, 241);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, utf8_decode('  Informations Client'), 0, 1, 'L', true);
        $pdf->Ln(2);

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(40, 7, utf8_decode('Nom:'), 0, 0);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 7, utf8_decode($user['nom']), 0, 1);
        $pdf->SetFont('Arial', '', 12);

        $pdf->Cell(40, 7, utf8_decode('Email:'), 0, 0);
        $pdf->Cell(0, 7, utf8_decode($user['email']), 0, 1);
        $pdf->Cell(40, 7, utf8_decode('Poids / Taille:'), 0, 0);
        $pdf->Cell(0, 7, utf8_decode($poidsKg . ' kg / ' . $tailleCm . ' cm'), 0, 1);
        $pdf->Cell(40, 7, utf8_decode('IMC Actuel:'), 0, 0);
        $pdf->Cell(0, 7, utf8_decode($imcText), 0, 1);
        
        $pdf->Cell(40, 7, utf8_decode('Objectif:'), 0, 0);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(231, 76, 60);
        $pdf->Cell(0, 7, utf8_decode($objectiveText), 0, 1);
        $pdf->SetTextColor(50, 50, 50);

        $pdf->Ln(5);

        // Section: Achats
        $pdf->SetFillColor(212, 239, 223); // Light green
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, utf8_decode('  Détails de la Commande N° ' . $commandeId), 0, 1, 'L', true);
        $pdf->Ln(2);

        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(40, 7, utf8_decode('Régime:'), 0, 0);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->Cell(0, 7, utf8_decode($purchase['nom_regime']), 0, 1);
        $pdf->SetFont('Arial', '', 12);
        
        $pdf->Cell(40, 7, utf8_decode('Objectif visé:'), 0, 0);
        $pdf->Cell(0, 7, utf8_decode($this->formatVariationLabel((float) ($purchase['variation_mensuelle_kg'] ?? 0))), 0, 1);
        
        $pdf->Cell(40, 7, utf8_decode('Composition:'), 0, 0);
        $pdf->Cell(0, 7, utf8_decode(
            ($purchase['pourcentage_viande'] ?? 0) . '% Viande, ' .
            ($purchase['pourcentage_poisson'] ?? 0) . '% Poisson, ' .
            ($purchase['pourcentage_volaille'] ?? 0) . '% Volaille'
        ), 0, 1);
        
        $pdf->Cell(40, 7, utf8_decode('Durée:'), 0, 0);
        $pdf->Cell(0, 7, utf8_decode($purchase['nb_jours'] . ' jours'), 0, 1);
        $pdf->Cell(40, 7, utf8_decode('Date d\'achat:'), 0, 0);
        $pdf->Cell(0, 7, utf8_decode(date('d/m/Y', strtotime((string) $purchase['date_achat']))), 0, 1);

        $estimatedLabel = rtrim(rtrim(number_format($estimated, 2, ',', ' '), '0'), ',');
        $estimatedSign = $estimated > 0 ? '+' : '';
        $pdf->Cell(40, 7, utf8_decode('Résultat estimé:'), 0, 0);
        $pdf->SetFont('Arial', 'B', 12);
        $pdf->SetTextColor(39, 174, 96);
        $pdf->Cell(0, 7, utf8_decode($estimatedSign . $estimatedLabel . ' kg'), 0, 1);
        $pdf->SetTextColor(50, 50, 50);

        $pdf->Ln(5);
        
        // Section Activites sportives
        $pdf->SetFillColor(252, 243, 207); // Light yellow
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(0, 10, utf8_decode('  Activités Sportives Requises'), 0, 1, 'L', true);
        $pdf->Ln(2);
        
        $pdf->SetFont('Arial', '', 12);
        if (empty($activites)) {
            $pdf->Cell(0, 7, utf8_decode('Aucune activité recommandée.'), 0, 1);
        } else {
            foreach ($activites as $activite) {
                $pdf->Cell(10, 7, '-', 0, 0, 'C');
                $pdf->Cell(0, 7, utf8_decode($activite['label_activite'] . ' (' . $activite['nb_par_semaine'] . 'x par semaine)'), 0, 1);
            }
        }

        $pdf->Ln(10);
        
        // Footer / Facturation
        $pdf->SetFillColor(236, 240, 241);
        $y = $pdf->GetY();
        $pdf->Rect(10, $y, 190, 25, 'F');
        $pdf->SetY($y + 5);
        $pdf->SetFont('Arial', 'B', 14);
        $pdf->Cell(95, 8, utf8_decode('MONTANT TOTAL PAYE :'), 0, 0, 'R');
        $pdf->SetTextColor(192, 57, 43); // Dark red
        $pdf->Cell(95, 8, utf8_decode(number_format((float) $purchase['montant_paye'], 0, ',', ' ') . ' Ariary'), 0, 1, 'L');
        
        $pdf->SetTextColor(100, 100, 100);
        $pdf->SetFont('Arial', 'I', 10);
        $pdf->Ln(2);
        $pdf->Cell(0, 6, utf8_decode('Merci de votre confiance. Gardez ce document comme reçu.'), 0, 1, 'C');

        $pdfData = $pdf->Output('S');

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'inline; filename="achat-' . $commandeId . '.pdf"')
            ->setBody($pdfData);
    }


    private function formatVariationLabel(float $variation): string
    {
        $formatted = number_format($variation, 2, ',', ' ');
        $formatted = rtrim(rtrim($formatted, '0'), ',');
        $sign = $variation > 0 ? '+' : '';

        return $sign . $formatted . ' kg / 30 j';
    }

    private function getObjectiveLabel(float $variation): string
    {
        if ($variation > 0) {
            return 'Prise de masse';
        }

        if ($variation < 0) {
            return 'Perte de poids';
        }

        return 'IMC idéal';
    }
}
