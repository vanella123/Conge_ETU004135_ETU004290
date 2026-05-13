<?php

namespace App\Controllers;

use App\Models\CommandeModel;
use App\Models\OptionModel;
use App\Models\UtilisateurModel;

class OptionController extends BaseController
{
    public function index()
    {
        if (! session()->get('is_logged_in')) {
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter.');
        }

        $optionModel = new OptionModel();
        $commandeModel = new CommandeModel();
        $utilisateurModel = new UtilisateurModel();
        $userId = (int) session()->get('id_utilisateur');
        $user = $utilisateurModel->find($userId);
        $gold = $optionModel->getGoldOption();

        if ($user === null) {
            return redirect()->to('/login')->with('error', 'Utilisateur introuvable.');
        }

        $purchaseCount = $commandeModel->countUserPurchases($userId);
        $requiredPurchases = (int) ($gold['nb_regimes_achetes'] ?? 0);
        $remainingPurchases = max(0, $requiredPurchases - $purchaseCount);
        $canUnlock = $gold !== null && $purchaseCount >= $requiredPurchases;
        $hasEnoughBalance = $gold !== null && (float) ($user['argent'] ?? 0) >= (float) ($gold['prix_unique'] ?? 0);

        return view('frontoffice/options/index', [
            'gold' => $gold,
            'user' => $user,
            'purchaseCount' => $purchaseCount,
            'requiredPurchases' => $requiredPurchases,
            'remainingPurchases' => $remainingPurchases,
            'canUnlock' => $canUnlock,
            'hasEnoughBalance' => $hasEnoughBalance,
        ]);
    }

    public function buyGold(int $id)
    {
        if (! session()->get('is_logged_in')) {
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter.');
        }

        $optionModel = new OptionModel();
        $commandeModel = new CommandeModel();
        $utilisateurModel = new UtilisateurModel();

        $option = $optionModel->find($id);
        if (! $option) {
            return redirect()->to('/options')->with('error', 'Option introuvable.');
        }

        $userId = (int) session()->get('id_utilisateur');
        $user = $utilisateurModel->find($userId);
        if ($user === null) {
            return redirect()->to('/login')->with('error', 'Utilisateur introuvable.');
        }

        if ((bool) ($user['is_gold'] ?? false)) {
            return redirect()->to('/options')->with('error', 'Vous possedez deja Gold.');
        }

        $purchaseCount = $commandeModel->countUserPurchases($userId);
        $requiredPurchases = (int) ($option['nb_regimes_achetes'] ?? 0);
        if ($purchaseCount < $requiredPurchases) {
            $remaining = $requiredPurchases - $purchaseCount;
            return redirect()->to('/options')->with('error', 'Vous devez encore acheter ' . $remaining . ' regime(s) pour acceder a Gold.');
        }

        $balance = (float) ($user['argent'] ?? 0);
        $price = (float) ($option['prix_unique'] ?? 0);
        if ($balance < $price) {
            return redirect()->to('/options')->with('error', 'Solde insuffisant pour acheter Gold.');
        }

        $newBalance = $balance - $price;
        $utilisateurModel->update($userId, [
            'argent' => $newBalance,
            'is_gold' => true,
        ]);

        session()->set('argent', $newBalance);
        session()->set('is_gold', true);

        return redirect()->to('/options')->with('success', 'Gold a ete achete avec succes.');
    }
}
