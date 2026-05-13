<?php

namespace App\Controllers;

use App\Models\ActiviteSportiveModel;
use App\Models\DureeRegimeModel;
use App\Models\RegimeActiviteModel;
use App\Models\RegimeModel;

class AdminRegimeController extends BaseController
{
    private function requireAdmin()
    {
        if (! session()->has('admin_id')) {
            return redirect()->to('/admin/login');
        }

        return null;
    }

    public function index()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $filters = [
            'nom_regime' => trim((string) $this->request->getGet('nom_regime')),
            'variation_min' => $this->request->getGet('variation_min'),
            'variation_max' => $this->request->getGet('variation_max'),
            'duree_min' => $this->request->getGet('duree_min'),
            'duree_max' => $this->request->getGet('duree_max'),
            'prix_min' => $this->request->getGet('prix_min'),
            'prix_max' => $this->request->getGet('prix_max'),
        ];

        return view('backoffice/regime/index', [
            'regimes' => $this->getRegimeListing($filters),
            'filters' => $filters,
            'regimeDurations' => (new DureeRegimeModel())->getAllGroupedByRegime(),
            'activeNav' => 'regimes',
        ]);
    }

    public function create()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        return view('backoffice/regime/form', [
            'title' => 'Creer un regime',
            'action' => base_url('admin/regimes/store'),
            'regime' => null,
            'validation' => session('validation'),
            'formErrors' => session('form_errors') ?? [],
            'activities' => $this->getActivityChoices(),
            'selectedActivities' => old('activites') ?? [],
            'durationRows' => $this->resolveDurationRows(),
            'activeNav' => 'regimes',
        ]);
    }

    public function store()
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $formData = $this->collectFormData();
        $rules = $this->getBaseRules();

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('validation', $this->validator);
        }

        $formErrors = $this->validateAdvancedFields($formData);
        if ($formErrors !== []) {
            return redirect()->back()->withInput()
                ->with('form_errors', $formErrors);
        }

        $regimeModel = new RegimeModel();
        
        try {
            $regimeModel->createRegimeWithRelations(
                $this->buildRegimePayload($formData),
                $formData['activite_ids'],
                $formData['duration_rows']
            );
        } catch (\RuntimeException $e) {
            return redirect()->back()->withInput()->with('error', 'Impossible de creer ce regime pour le moment. ' . $e->getMessage());
        }

        return redirect()->to('/admin/regimes')->with('success', 'Regime cree avec succes.');
    }

    public function show(int $id)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $regime = $this->getRegimeDetail($id);

        if ($regime === null) {
            return redirect()->to('/admin/regimes')->with('error', 'Regime introuvable.');
        }

        return view('backoffice/regime/show', [
            'regime' => $regime,
            'estimates' => $this->buildWeightEstimates((float) $regime['variation_mensuelle_kg']),
            'suggestedUsers' => $this->buildSuggestedUsers((float) $regime['variation_mensuelle_kg']),
            'activeNav' => 'regimes',
        ]);
    }

    public function edit(int $id)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $regimeModel = new RegimeModel();
        $regime = $regimeModel->find($id);

        if (! $regime) {
            return redirect()->to('/admin/regimes')->with('error', 'Regime introuvable.');
        }

        $regimeActiviteModel = new RegimeActiviteModel();

        return view('backoffice/regime/form', [
            'title' => 'Modifier un regime',
            'action' => base_url('admin/regimes/update/' . $id),
            'regime' => $regime,
            'validation' => session('validation'),
            'formErrors' => session('form_errors') ?? [],
            'activities' => $this->getActivityChoices(),
            'selectedActivities' => old('activites') ?? $regimeActiviteModel->getActiviteIdsForRegime($id),
            'durationRows' => $this->resolveDurationRows($id),
            'lockedDurationIds' => $this->getLockedDurationIds($id),
            'activeNav' => 'regimes',
        ]);
    }

    public function update(int $id)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $regimeModel = new RegimeModel();
        $regime = $regimeModel->find($id);

        if (! $regime) {
            return redirect()->to('/admin/regimes')->with('error', 'Regime introuvable.');
        }

        $formData = $this->collectFormData();
        $rules = $this->getBaseRules();

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()
                ->with('validation', $this->validator);
        }

        $formErrors = $this->validateAdvancedFields($formData);
        if ($formErrors !== []) {
            return redirect()->back()->withInput()
                ->with('form_errors', $formErrors);
        }

        try {
            $regimeModel->updateRegimeWithRelations(
                $id,
                $this->buildRegimePayload($formData),
                $formData['activite_ids'],
                $formData['duration_rows']
            );
        } catch (\RuntimeException $exception) {
            return redirect()->back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->to('/admin/regimes')->with('success', 'Regime mis a jour avec succes.');
    }

    public function delete(int $id)
    {
        if ($redirect = $this->requireAdmin()) {
            return $redirect;
        }

        $regimeModel = new RegimeModel();
        $regime = $regimeModel->find($id);

        if (! $regime) {
            return redirect()->to('/admin/regimes')->with('error', 'Regime introuvable.');
        }

        $lockedDurations = $this->getLockedDurationLabels($id);
        if ($lockedDurations !== []) {
            return redirect()->to('/admin/regimes')->with(
                'error',
                'Suppression impossible: certaines durees sont deja utilisees (' . implode(', ', $lockedDurations) . ').'
            );
        }

        if (! $regimeModel->deleteWithRelations($id)) {
            return redirect()->to('/admin/regimes')->with('error', 'Suppression impossible pour le moment.');
        }

        return redirect()->to('/admin/regimes')->with('success', 'Regime supprime avec succes.');
    }

    private function getBaseRules(): array
    {
        return [
            'nom_regime' => 'required|min_length[3]|max_length[100]',
            'variation_mensuelle_kg' => 'required|numeric',
            'pourcentage_viande' => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]',
            'pourcentage_poisson' => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]',
            'pourcentage_volaille' => 'required|numeric|greater_than_equal_to[0]|less_than_equal_to[100]',
        ];
    }

    private function collectFormData(): array
    {
        return [
            'nom_regime' => trim((string) $this->request->getPost('nom_regime')),
            'variation_mensuelle_kg' => (float) $this->request->getPost('variation_mensuelle_kg'),
            'pourcentage_viande' => (float) $this->request->getPost('pourcentage_viande'),
            'pourcentage_poisson' => (float) $this->request->getPost('pourcentage_poisson'),
            'pourcentage_volaille' => (float) $this->request->getPost('pourcentage_volaille'),
            'activite_ids' => $this->sanitizeActivityIds($this->request->getPost('activites') ?? []),
            'duration_rows' => $this->sanitizeDurationRows($this->request->getPost('durees') ?? []),
        ];
    }

    private function buildRegimePayload(array $formData): array
    {
        $payload = [
            'nom_regime' => $formData['nom_regime'],
            'pourcentage_viande' => $formData['pourcentage_viande'],
            'pourcentage_poisson' => $formData['pourcentage_poisson'],
            'pourcentage_volaille' => $formData['pourcentage_volaille'],
        ];

        $payload[$this->getVariationColumn()] = $formData['variation_mensuelle_kg'];

        return $payload;
    }

    private function sanitizeActivityIds($rawIds): array
    {
        $ids = is_array($rawIds) ? $rawIds : [];
        $ids = array_map(static fn ($value): int => (int) $value, $ids);
        $ids = array_filter($ids, static fn (int $value): bool => $value > 0);

        return array_values(array_unique($ids));
    }

    private function sanitizeDurationRows($rawRows): array
    {
        $rows = is_array($rawRows) ? $rawRows : [];
        $cleanRows = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $nbJours = trim((string) ($row['nb_jours'] ?? ''));
            $prix = trim((string) ($row['prix'] ?? ''));

            if ($nbJours === '' && $prix === '') {
                continue;
            }

            $cleanRows[] = [
                'id_duree_regime' => (int) ($row['id_duree_regime'] ?? 0),
                'nb_jours' => $nbJours,
                'prix' => $prix,
            ];
        }

        return $cleanRows;
    }

    private function validateAdvancedFields(array $formData): array
    {
        $errors = [];
        $compositionTotal = $formData['pourcentage_viande']
            + $formData['pourcentage_poisson']
            + $formData['pourcentage_volaille'];

        if (abs($compositionTotal - 100) > 0.01) {
            $errors[] = 'La composition viande / poisson / volaille doit totaliser 100%.';
        }

        $activities = $this->getActivityChoices();
        $availableActivityIds = array_map(static fn (array $item): int => (int) $item['id_activite'], $activities);
        foreach ($formData['activite_ids'] as $activityId) {
            if (! in_array($activityId, $availableActivityIds, true)) {
                $errors[] = 'Une activite selectionnee est invalide.';
                break;
            }
        }

        if ($formData['duration_rows'] === []) {
            $errors[] = 'Ajoutez au moins une duree avec son prix.';
            return $errors;
        }

        $seenDays = [];
        foreach ($formData['duration_rows'] as $index => $row) {
            $rowNumber = $index + 1;

            if (! ctype_digit((string) $row['nb_jours']) || (int) $row['nb_jours'] <= 0) {
                $errors[] = 'La ligne de duree ' . $rowNumber . ' doit contenir un nombre de jours valide.';
            }

            if (! is_numeric($row['prix']) || (float) $row['prix'] <= 0) {
                $errors[] = 'La ligne de duree ' . $rowNumber . ' doit contenir un prix valide.';
            }

            $daysKey = (int) $row['nb_jours'];
            if (isset($seenDays[$daysKey])) {
                $errors[] = 'Chaque duree doit etre unique. Le nombre de jours ' . $daysKey . ' est duplique.';
            }
            $seenDays[$daysKey] = true;
        }

        return array_values(array_unique($errors));
    }



    private function getRegimeListing(array $filters): array
    {
        return (new RegimeModel())->getAdminRegimeListing($filters);
    }

    private function getRegimeDetail(int $id): ?array
    {
        return (new RegimeModel())->getAdminRegimeDetail($id);
    }

    private function resolveDurationRows(?int $regimeId = null): array
    {
        $oldRows = old('durees');
        if (is_array($oldRows) && $oldRows !== []) {
            return $this->sanitizeDurationRows($oldRows);
        }

        if ($regimeId !== null) {
            $rows = (new DureeRegimeModel())
                ->where('id_regime', $regimeId)
                ->orderBy('nb_jours', 'ASC')
                ->findAll();

            return array_map(static fn (array $row): array => [
                'id_duree_regime' => (int) ($row['id_duree_regime'] ?? 0),
                'nb_jours' => (string) ($row['nb_jours'] ?? ''),
                'prix' => (string) ($row['prix'] ?? ''),
            ], $rows);
        }

        return [
            ['nb_jours' => '', 'prix' => ''],
        ];
    }

    private function getActivityChoices(): array
    {
        return (new ActiviteSportiveModel())
            ->orderBy('label_activite', 'ASC')
            ->findAll();
    }

    private function getVariationColumn(): string
    {
        static $column = null;

        if ($column === null) {
            $db = db_connect();
            $column = $db->fieldExists('variation_mensuelle_kg', 'regime')
                ? 'variation_mensuelle_kg'
                : 'variation_poids';
        }

        return $column;
    }

    private function buildWeightEstimates(float $monthlyVariation): array
    {
        $durations = [0, 30, 60, 90];
        $estimates = [];

        foreach ($durations as $days) {
            $estimates[] = [
                'days' => $days,
                'value' => $monthlyVariation * ($days / 30),
            ];
        }

        return $estimates;
    }

    private function buildSuggestedUsers(float $monthlyVariation): array
    {
        $suggestions = [];

        if ($monthlyVariation < 0) {
            $suggestions[] = ['label' => 'Perte de poids', 'tone' => 'warn'];
        }

        if ($monthlyVariation > 0) {
            $suggestions[] = ['label' => 'Prise de masse', 'tone' => 'success'];
        }

        if (abs($monthlyVariation) <= 1) {
            $suggestions[] = ['label' => 'IMC proche de l ideal', 'tone' => 'neutral'];
        }

        if ($suggestions === []) {
            $suggestions[] = ['label' => 'Variation neutre', 'tone' => 'neutral'];
        }

        return $suggestions;
    }

    private function getLockedDurationIds(int $regimeId): array
    {
        $rows = (new DureeRegimeModel())
            ->where('id_regime', $regimeId)
            ->findAll();

        $ids = [];
        $regimeModel = new RegimeModel();
        foreach ($rows as $row) {
            $durationId = (int) ($row['id_duree_regime'] ?? 0);
            if ($durationId > 0 && $regimeModel->durationHasDependencies($durationId)) {
                $ids[] = $durationId;
            }
        }

        return $ids;
    }

    private function getLockedDurationLabels(int $regimeId): array
    {
        $rows = (new DureeRegimeModel())
            ->where('id_regime', $regimeId)
            ->orderBy('nb_jours', 'ASC')
            ->findAll();

        $labels = [];
        $regimeModel = new RegimeModel();
        foreach ($rows as $row) {
            $durationId = (int) ($row['id_duree_regime'] ?? 0);
            if ($durationId > 0 && $regimeModel->durationHasDependencies($durationId)) {
                $labels[] = (int) ($row['nb_jours'] ?? 0) . ' jours';
            }
        }

        return $labels;
    }


}
