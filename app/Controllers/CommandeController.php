<?php

namespace App\Controllers;

use App\Models\CommandeModel;
use App\Models\DureeRegimeModel;
use App\Models\OptionModel;
use App\Models\RegimeModel;
use App\Models\UtilisateurModel;

class CommandeController extends BaseController
{
    public function purchase(int $id)
    {
        if (! session()->get('is_logged_in')) {
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter.');
        }

        $regimeModel = new RegimeModel();
        $dureeModel = new DureeRegimeModel();
        $userModel = new UtilisateurModel();

        $regime = $regimeModel->find($id);
        if ($regime === null) {
            return redirect()->to('/regimes')->with('error', 'Regime introuvable.');
        }

        $durees = $dureeModel->getByRegimeId($id);
        $user = $userModel->find((int) session()->get('id_utilisateur'));
        if ($user === null) {
            return redirect()->to('/login')->with('error', 'Utilisateur introuvable.');
        }

        $isGold = (bool) ($user['is_gold'] ?? false);
        $discountPercent = $this->getGoldDiscountPercent($isGold);
        $dureesAffichees = array_map(static function (array $duree) use ($discountPercent): array {
            $prixBase = (float) $duree['prix'];

            return $duree + [
                'prix_final' => round($prixBase * (1 - ($discountPercent / 100)), 2),
            ];
        }, $durees);

        return view('frontoffice/regime/purchase', [
            'regime' => $regime,
            'durees' => $dureesAffichees,
            'isGold' => $isGold,
            'discountPercent' => $discountPercent,
            'argent' => (float) ($user['argent'] ?? 0),
        ]);
    }

    public function confirmPurchase(int $id)
    {
        if (! session()->get('is_logged_in')) {
            return $this->errorResponse('Veuillez vous connecter.', '/login', 401);
        }

        $rules = [
            'id_duree_regime' => 'required|is_natural_no_zero',
        ];

        if (! $this->validate($rules)) {
            return $this->validationErrorResponse(
                'Veuillez choisir une duree de regime valide.',
                $this->validator->getErrors()
            );
        }

        $regimeModel = new RegimeModel();
        $dureeRegimeModel = new DureeRegimeModel();
        $userModel = new UtilisateurModel();
        $commandeModel = new CommandeModel();

        $regime = $regimeModel->find($id);
        if ($regime === null) {
            return $this->errorResponse('Regime introuvable.', '/regimes/' . $id, 404);
        }

        $idDureeRegime = (int) $this->request->getPost('id_duree_regime');
        $duree = $dureeRegimeModel->find($idDureeRegime);

        if ($duree === null || (int) $duree['id_regime'] !== $id) {
            return $this->validationErrorResponse('Duree de regime invalide.', [
                'id_duree_regime' => 'Duree de regime invalide.',
            ]);
        }

        $userId = (int) session()->get('id_utilisateur');
        $user = $userModel->find($userId);
        if ($user === null) {
            return $this->errorResponse('Utilisateur introuvable.', '/login', 401);
        }

        if ($commandeModel->hasActiveRegime($userId, $id, $idDureeRegime)) {
            return $this->validationErrorResponse('Vous avez deja ce regime pour cette duree.', [
                'id_duree_regime' => 'Vous avez deja ce regime pour cette duree.',
            ]);
        }

        $isGold = (bool) ($user['is_gold'] ?? false);
        $discountPercent = $this->getGoldDiscountPercent($isGold);
        $argent = (float) ($user['argent'] ?? 0);
        $prix = $this->calculateFinalPrice((float) $duree['prix'], $discountPercent);
        if ($argent < $prix) {
            return $this->validationErrorResponse('Solde insuffisant pour acheter ce regime.', [
                'id_duree_regime' => 'Solde insuffisant pour acheter ce regime.',
            ]);
        }

        $newSolde = $argent - $prix;
        $userModel->update($userId, ['argent' => $newSolde]);

        $commandeModel->insert([
            'id_utilisateur' => $userId,
            'id_regime' => $id,
            'id_duree_regime' => $idDureeRegime,
            'montant_paye' => $prix,
        ]);

        $updatedUser = $userModel->find($userId);
        if (is_array($updatedUser)) {
            session()->set('argent', (float) ($updatedUser['argent'] ?? $newSolde));
            session()->set('is_gold', (bool) ($updatedUser['is_gold'] ?? false));
        }

        return $this->successResponse('Regime achete avec succes.', '/mes-regimes', [
            'argent' => $newSolde,
        ]);
    }

    private function successResponse(string $message, string $redirectTo, array $payload = [])
    {
        if ($this->request->isAJAX()) {
            return $this->response->setJSON(array_merge([
                'success' => true,
                'message' => $message,
                'redirect' => $redirectTo,
            ], $payload));
        }

        return redirect()->to($redirectTo)->with('success', $message);
    }

    private function errorResponse(string $message, string $redirectTo, int $status = 400)
    {
        if ($this->request->isAJAX()) {
            return $this->response->setStatusCode($status)->setJSON([
                'success' => false,
                'message' => $message,
            ]);
        }

        return redirect()->to($redirectTo)->with('error', $message);
    }

    private function validationErrorResponse(string $message, array $errors = [])
    {
        if ($this->request->isAJAX()) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => $message,
                'errors' => $errors,
            ]);
        }

        return redirect()->back()->withInput()->with('errors', $errors);
    }

    private function getGoldDiscountPercent(bool $isGold): float
    {
        if (! $isGold) {
            return 0.0;
        }

        $optionModel = new OptionModel();
        $gold = $optionModel->getGoldOption();

        return (float) ($gold['reduction_pourcentage'] ?? 0);
    }

    private function calculateFinalPrice(float $basePrice, float $discountPercent): float
    {
        if ($discountPercent <= 0) {
            return $basePrice;
        }

        return round($basePrice * (1 - ($discountPercent / 100)), 2);
    }
}
