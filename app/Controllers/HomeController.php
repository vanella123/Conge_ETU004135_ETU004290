<?php

namespace App\Controllers;

use App\Models\RegimeModel;
use App\Models\UtilisateurModel;

class HomeController extends BaseController
{
    protected RegimeModel $regimeModel;
    protected UtilisateurModel $utilisateurModel;

    public function __construct()
    {
        $this->regimeModel = model(RegimeModel::class);
        $this->utilisateurModel = model(UtilisateurModel::class);
    }

    public function index()
    {
        if (session()->get('is_logged_in')) {
            return redirect()->to('/dashboard');
        }

        $featuredRegimes = $this->regimeModel->getFeaturedRegimes();

        $testimonials = [
            [
                'text' => "J'ai perdu 5kg en 2 mois grâce au régime Keto. L'accompagnement sportif fait toute la différence !",
                'name' => 'Marie R.',
                'role' => 'Utilisatrice depuis 3 mois',
                'initials' => 'MR',
            ],
            [
                'text' => "L'application m'a aidé à atteindre mon IMC idéal. Simple, efficace et motivant.",
                'name' => 'Jean P.',
                'role' => 'Utilisateur depuis 6 mois',
                'initials' => 'JP',
            ],
            [
                'text' => "Le suivi des objectifs et les activités sportives recommandées sont top. Je recommande !",
                'name' => 'Sofia N.',
                'role' => 'Utilisatrice depuis 1 mois',
                'initials' => 'SN',
            ],
        ];

        return view('frontoffice/home/index', [
            'featuredRegimes' => $featuredRegimes,
            'testimonials' => $testimonials,
        ]);
    }
}
