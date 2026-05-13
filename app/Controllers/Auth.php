<?php

namespace App\Controllers;

use App\Models\CodePromoModel;
use App\Models\DemandeCodePromoModel;
use App\Models\CommandeModel;
use App\Models\ImcModel;
use App\Models\ObjectifModel;
use App\Models\UtilisateurModel;

class Auth extends BaseController
{
    public function index()
    {
        if (session()->get('is_logged_in')) {
            return redirect()->to('/dashboard');
        }

        return redirect()->to('/login');
    }

    public function register()
    {
        if (session()->get('is_logged_in')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/register_personal', [
            'personal' => session()->get('register_step_personal') ?? [],
        ]);
    }

    public function saveRegisterPersonal()
    {
        $rules = [
            'nom' => 'required|min_length[2]|max_length[100]',
            'email' => 'required|valid_email',
            'mot_de_passe' => 'required|min_length[8]|regex_match[/[A-Z]/]|regex_match[/[0-9]/]',
            'genre' => 'permit_empty|in_list[Homme,Femme,Autre]',
            'date_naissance' => 'permit_empty|valid_date[Y-m-d]',
        ];

        if (! $this->validate($rules)) {
            $errors = $this->validator->getErrors();
            if (isset($errors['mot_de_passe'])) {
                $errors['mot_de_passe'] = 'Le mot de passe doit contenir au moins 8 caracteres, une majuscule et un chiffre.';
            }
            return $this->validationErrorResponse(
                'Veuillez compléter correctement les informations personnelles.',
                $errors
            );
        }

        $userModel = new UtilisateurModel();
        if ($userModel->findByEmail((string) $this->request->getPost('email')) !== null) {
            return $this->validationErrorResponse('Cet email est déjà utilisé.', [
                'email' => 'Cet email est déjà utilisé.',
            ]);
        }

        session()->set('register_step_personal', [
            'nom' => (string) $this->request->getPost('nom'),
            'email' => (string) $this->request->getPost('email'),
            'mot_de_passe' => (string) $this->request->getPost('mot_de_passe'),
            'genre' => (string) ($this->request->getPost('genre') ?: 'Autre'),
            'date_naissance' => ($this->request->getPost('date_naissance') ?: null),
        ]);

        return $this->successResponse(
            'Infos personnelles enregistrées. Complétez maintenant les informations santé.',
            '/register/health'
        );
    }

    public function registerHealth()
    {
        if (session()->get('is_logged_in')) {
            return redirect()->to('/dashboard');
        }

        $personalStep = session()->get('register_step_personal');
        if (! is_array($personalStep)) {
            return redirect()->to('/register')->with('error', 'Commencez par les informations personnelles.');
        }

        $objectifModel = new ObjectifModel();

        return view('auth/register_health', [
            'personal' => $personalStep,
            'objectifs' => $objectifModel->findAll(),
        ]);
    }

    public function saveRegisterHealth()
    {
        $personalStep = session()->get('register_step_personal');
        if (! is_array($personalStep)) {
            return $this->errorResponse('Commencez par les informations personnelles.', '/register');
        }

        $rules = [
            'taille_cm' => 'required|numeric|greater_than[0]',
            'poids_kg' => 'required|numeric|greater_than[0]',
            'id_objectif' => 'required|is_natural_no_zero',
            'poids_objectif' => 'permit_empty|numeric|greater_than[0]',
        ];

        if (! $this->validate($rules)) {
            return $this->validationErrorResponse(
                'Veuillez compléter correctement les informations santé.',
                $this->validator->getErrors()
            );
        }

        $userModel = new UtilisateurModel();
        $objectifModel = new ObjectifModel();
        $objectif = $objectifModel->find((int) $this->request->getPost('id_objectif'));

        if ($objectif === null) {
            return $this->validationErrorResponse('Objectif invalide.', [
                'id_objectif' => 'Objectif invalide.',
            ]);
        }

        $tailleCm = (float) $this->request->getPost('taille_cm');
        $poidsKg = (float) $this->request->getPost('poids_kg');
        $poidsObjectifRaw = $this->request->getPost('poids_objectif');
        $poidsObjectif = ($poidsObjectifRaw === null || $poidsObjectifRaw === '') ? null : (float) $poidsObjectifRaw;
        $coherenceErrors = $this->validateObjectiveConsistency((int) $objectif['id_objectif'], $poidsKg, $poidsObjectif, $tailleCm);
        if ($coherenceErrors !== []) {
            return $this->validationErrorResponse('Veuillez corriger les incohérences des données santé.', $coherenceErrors);
        }

        $userId = $userModel->insert([
            'nom' => $personalStep['nom'],
            'email' => $personalStep['email'],
            'mot_de_passe' => password_hash((string) $personalStep['mot_de_passe'], PASSWORD_DEFAULT),
            'genre' => $personalStep['genre'],
            'taille_cm' => $tailleCm,
            'poids_kg' => $poidsKg,
            'poids_objectif' => $poidsObjectif,
            'date_naissance' => $personalStep['date_naissance'],
            'id_objectif' => (int) $this->request->getPost('id_objectif'),
            'is_gold' => false,
            'argent' => 0,
            'role_utilisateur' => 'client',
        ], true);

        if (! is_int($userId) && ! is_string($userId)) {
            return $this->errorResponse('Impossible de créer le compte.');
        }

        $imc = $userModel->calculateImc($poidsKg, $tailleCm);

        session()->remove('register_step_personal');
        session()->set([
            'is_logged_in' => true,
            'id_utilisateur' => $userId,
            'nom' => $personalStep['nom'],
            'email' => $personalStep['email'],
            'role' => 'client',
            'is_gold' => false,
            'imc' => $imc,
            'objectif_label' => $objectif['label_objectif'],
        ]);

        return $this->successResponse('Compte créé avec succès.', '/dashboard');
    }

    public function login()
    {
        if (session()->get('is_logged_in')) {
            return redirect()->to('/dashboard');
        }

        return view('auth/login');
    }

    public function attemptLogin()
    {
        $rules = [
            'email' => 'required|valid_email',
            'mot_de_passe' => 'required',
        ];

        if (! $this->validate($rules)) {
            return $this->validationErrorResponse(
                'Email ou mot de passe invalide.',
                $this->validator->getErrors()
            );
        }

        $email = (string) $this->request->getPost('email');
        $motDePasse = (string) $this->request->getPost('mot_de_passe');

        $userModel = new UtilisateurModel();
        $user = $userModel->findByEmail($email);

        if ($user === null || ! password_verify($motDePasse, (string) $user['mot_de_passe'])) {
            return $this->validationErrorResponse('Identifiants incorrects.', [
                'email' => 'Identifiants incorrects.',
                'mot_de_passe' => 'Identifiants incorrects.',
            ]);
        }

        session()->set([
            'is_logged_in' => true,
            'id_utilisateur' => $user['id_utilisateur'],
            'nom' => $user['nom'],
            'email' => $user['email'],
            'role' => $user['role_utilisateur'],
            'is_gold' => (bool) $user['is_gold'],
            'argent' => (float) ($user['argent'] ?? 0),
        ]);

        $this->refreshUserSessionData($user);

    return $this->successResponse('', '/dashboard');
    }

    public function dashboard()
    {
        if (! session()->get('is_logged_in')) {
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter.');
        }

        $userModel = new UtilisateurModel();
        $commandeModel = new CommandeModel();
        $user = $userModel->find((int) session()->get('id_utilisateur'));

        if ($user !== null) {
            $this->refreshUserSessionData($user);
        }

        return view('frontoffice/dashboard/index', [
            'nom' => (string) session()->get('nom'),
            'email' => (string) session()->get('email'),
            'role' => (string) session()->get('role'),
            'imc' => session()->get('imc'),
            'objectifLabel' => session()->get('objectif_label'),
            'argent' => session()->get('argent'),
            'regimesCount' => $commandeModel->countUserPurchases((int) session()->get('id_utilisateur')),
        ]);
    }

    public function transactions()
    {
        if (! session()->get('is_logged_in')) {
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter.');
        }

        $commandeModel = new CommandeModel();
        $transactions = $commandeModel->getHistoryByUserId((int) session()->get('id_utilisateur'));

        return view('frontoffice/transactions/index', [
            'transactions' => $transactions,
        ]);
    }

    public function promo()
    {
        if (! session()->get('is_logged_in')) {
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter.');
        }

        $userModel = new UtilisateurModel();
        $user = $userModel->find((int) session()->get('id_utilisateur'));
        $demandeModel = new DemandeCodePromoModel();

        return view('frontoffice/promo/index', [
            'argent' => (float) ($user['argent'] ?? session()->get('argent') ?? 0),
            'demandes' => $demandeModel->getUserHistory((int) session()->get('id_utilisateur')),
        ]);
    }

    public function applyPromo()
    {
        if (! session()->get('is_logged_in')) {
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter.');
        }

        $rules = [
            'code_promo' => 'required|min_length[3]',
        ];

        if (! $this->validate($rules)) {
            return $this->validationErrorResponse(
                'Veuillez saisir un code promo valide.',
                $this->validator->getErrors()
            );
        }

        $code = strtoupper(trim((string) $this->request->getPost('code_promo')));
        $promoModel = new CodePromoModel();
        $promo = $promoModel->findByCode($code);

        if ($promo !== null && (bool) ($promo['deja_utilise'] ?? false)) {
            return $this->validationErrorResponse('Ce code promo a deja ete utilise.', [
                'code_promo' => 'Ce code promo a deja ete utilise.',
            ]);
        }

        $userId = (int) session()->get('id_utilisateur');
        $userModel = new UtilisateurModel();
        $currentUser = $userModel->find($userId);

        if ($currentUser === null) {
            return $this->errorResponse('Utilisateur introuvable.', '/login');
        }

        $demandeModel = new DemandeCodePromoModel();
        if (! $demandeModel->hasPendingRequest($userId, $code)) {
            $demandeModel->insert([
                'id_utilisateur' => $userId,
                'code_saisi' => $code,
                'statut' => 'en_attente',
                'date_demande' => date('Y-m-d H:i:s'),
            ]);
        }

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => 'Votre code promo a ete envoye. Un administrateur doit maintenant le valider.',
            ]);
        }

        return redirect()->to('/promo')->with('success', 'Votre code promo a ete envoye pour validation.');
    }

    private function applyPromoLegacy()
    {
        if (! session()->get('is_logged_in')) {
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter.');
        }

        $rules = [
            'code_promo' => 'required|min_length[3]',
        ];

        if (! $this->validate($rules)) {
            return $this->validationErrorResponse(
                'Veuillez saisir un code promo valide.',
                $this->validator->getErrors()
            );
        }

        $code = trim((string) $this->request->getPost('code_promo'));
        $promoModel = new CodePromoModel();
        $promo = $promoModel->findByCode($code);

        if ($promo === null) {
            return $this->validationErrorResponse('Code promo introuvable.', [
                'code_promo' => 'Code promo introuvable.',
            ]);
        }

        if ((bool) $promo['deja_utilise']) {
            return $this->validationErrorResponse('Ce code promo a déjà été utilisé.', [
                'code_promo' => 'Ce code promo a déjà été utilisé.',
            ]);
        }

        $userId = (int) session()->get('id_utilisateur');
        $userModel = new UtilisateurModel();
        $currentUser = $userModel->find($userId);

        if ($currentUser === null) {
            return $this->errorResponse('Utilisateur introuvable.', '/login');
        }

        $newArgent = (float) ($currentUser['argent'] ?? 0) + (float) $promo['montant'];

        $userModel->update($userId, [
            'argent' => $newArgent,
        ]);

        $promoModel->update((int) $promo['id_code'], [
            'deja_utilise' => true,
            'id_utilisateur_utilisation' => $userId,
        ]);

        $updatedUser = $userModel->find($userId);
        if (is_array($updatedUser)) {
            $this->refreshUserSessionData($updatedUser);
        }

        return $this->successResponse('Code promo appliqué. Votre solde a été crédité.', '/promo');
    }

    public function profile()
    {
        if (! session()->get('is_logged_in')) {
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter.');
        }

        $userModel = new UtilisateurModel();
        $user = $userModel->find((int) session()->get('id_utilisateur'));

        if ($user === null) {
            return redirect()->to('/login')->with('error', 'Utilisateur introuvable.');
        }

        $objectifModel = new ObjectifModel();
        $objectif = $objectifModel->find((int) $user['id_objectif']);

        $imc = $userModel->calculateImc((float) $user['poids_kg'], (float) $user['taille_cm']);

        return view('frontoffice/profile/view', [
            'user' => $user,
            'objectif' => $objectif,
            'imc' => $imc,
        ]);
    }

    public function editProfile()
    {
        if (! session()->get('is_logged_in')) {
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter.');
        }

        $userModel = new UtilisateurModel();
        $user = $userModel->find((int) session()->get('id_utilisateur'));

        if ($user === null) {
            return redirect()->to('/login')->with('error', 'Utilisateur introuvable.');
        }

        $objectifModel = new ObjectifModel();

        return view('frontoffice/profile/edit', [
            'user' => $user,
            'objectifs' => $objectifModel->findAll(),
        ]);
    }

    public function updateProfile()
    {
        if (! session()->get('is_logged_in')) {
            return redirect()->to('/login')->with('error', 'Veuillez vous connecter.');
        }

        $userModel = new UtilisateurModel();
        $currentUser = $userModel->find((int) session()->get('id_utilisateur'));

        if ($currentUser === null) {
            return redirect()->to('/login')->with('error', 'Utilisateur introuvable.');
        }

        $rules = [
            'nom' => 'required|min_length[2]|max_length[100]',
            'genre' => 'permit_empty|in_list[Homme,Femme,Autre]',
            'date_naissance' => 'permit_empty|valid_date[Y-m-d]',
            'taille_cm' => 'required|numeric|greater_than[0]',
            'poids_kg' => 'required|numeric|greater_than[0]',
            'poids_objectif' => 'permit_empty|numeric|greater_than[0]',
            'id_objectif' => 'required|is_natural_no_zero',
        ];

        if (! $this->validate($rules)) {
            return $this->validationErrorResponse(
                'Veuillez corriger les champs indiqués.',
                $this->validator->getErrors()
            );
        }

        $tailleCm = (float) $this->request->getPost('taille_cm');
        $poidsKg = (float) $this->request->getPost('poids_kg');
        $idObjectif = (int) $this->request->getPost('id_objectif');
        $poidsObjectifRaw = $this->request->getPost('poids_objectif');
        $poidsObjectif = ($poidsObjectifRaw === null || $poidsObjectifRaw === '') ? null : (float) $poidsObjectifRaw;

        // Verify objectif exists
        $objectifModel = new ObjectifModel();
        $objectif = $objectifModel->find($idObjectif);
        if ($objectif === null) {
            return $this->validationErrorResponse('Objectif invalide.', [
                'id_objectif' => 'Objectif invalide.',
            ]);
        }

        $coherenceErrors = $this->validateObjectiveConsistency($idObjectif, $poidsKg, $poidsObjectif, $tailleCm);
        if ($coherenceErrors !== []) {
            return $this->validationErrorResponse(
                'Veuillez corriger les champs indiqués.',
                $coherenceErrors
            );
        }

        // Update user in database
        $updateData = [
            'nom' => (string) $this->request->getPost('nom'),
            'genre' => (string) ($this->request->getPost('genre') ?: 'Autre'),
            'date_naissance' => ($this->request->getPost('date_naissance') ?: null),
            'taille_cm' => $tailleCm,
            'poids_kg' => $poidsKg,
            'poids_objectif' => $poidsObjectif,
            'id_objectif' => $idObjectif,
        ];

        $userModel->update((int) session()->get('id_utilisateur'), $updateData);

        // Calculate new IMC
        $newImc = $userModel->calculateImc($poidsKg, $tailleCm);

        // Update session with new data
        session()->set([
            'nom' => (string) $this->request->getPost('nom'),
            'imc' => $newImc,
            'objectif_label' => $objectif['label_objectif'],
        ]);

        return $this->successResponse('Profil mis à jour avec succès.', '/profile');
    }

    public function logout()
    {
        session()->destroy();

        return redirect()->to('/')->with('success', 'Déconnexion effectuée.');
    }

    private function successResponse(string $message, string $redirectTo)
    {
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true,
                'message' => $message,
                'redirect' => $redirectTo,
            ]);
        }

        if ($message === '') {
            return redirect()->to($redirectTo);
        }

        return redirect()->to($redirectTo)->with('success', $message);
    }

    private function errorResponse(string $message, string $redirectTo = '/')
    {
        if ($this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
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

        // Avoid repeating the same message in both global flash and field-level errors.
        return redirect()->back()->withInput()->with('errors', $errors);
    }

    private function refreshUserSessionData(array $user): void
    {
        $userModel = new UtilisateurModel();
        $objectifModel = new ObjectifModel();

        $objectif = null;
        if (! empty($user['id_objectif'])) {
            $objectif = $objectifModel->find((int) $user['id_objectif']);
        }

        session()->set([
            'nom' => $user['nom'],
            'email' => $user['email'],
            'role' => $user['role_utilisateur'],
            'is_gold' => (bool) $user['is_gold'],
            'imc' => $userModel->calculateImc((float) $user['poids_kg'], (float) $user['taille_cm']),
            'objectif_label' => $objectif['label_objectif'] ?? null,
            'argent' => (float) ($user['argent'] ?? 0),
        ]);
    }

    public function checkEmailAvailability()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON([
                'success' => false,
                'message' => 'Méthode non autorisée.',
            ]);
        }

        $email = trim((string) $this->request->getPost('email'));
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Email invalide.',
            ]);
        }

        $userModel = new UtilisateurModel();
        $existing = $userModel->findByEmail($email);

        return $this->response->setJSON([
            'success' => true,
            'available' => $existing === null,
            'message' => $existing === null ? 'Email disponible.' : 'Cet email est déjà utilisé.',
        ]);
    }

    public function imcPreview()
    {
        if (! $this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON([
                'success' => false,
                'message' => 'Méthode non autorisée.',
            ]);
        }

        $tailleCm = (float) $this->request->getPost('taille_cm');
        $poidsKg = (float) $this->request->getPost('poids_kg');

        $userModel = new UtilisateurModel();
        $imcModel = new ImcModel();
        $imc = $userModel->calculateImc($poidsKg, $tailleCm);

        if ($imc === null) {
            return $this->response->setStatusCode(422)->setJSON([
                'success' => false,
                'message' => 'Données IMC invalides.',
            ]);
        }

        $ranges = $imcModel->orderBy('imc_min', 'ASC')->findAll();
        $label = null;
        $isIdeal = false;
        foreach ($ranges as $range) {
            if ($imc >= (float) $range['imc_min'] && $imc <= (float) $range['imc_max']) {
                $label = $range['label_imc'];
                $isIdeal = strtolower((string) $label) === 'poids normal';
                break;
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'imc' => $imc,
            'label' => $label,
            'is_ideal' => $isIdeal,
            'ranges' => $ranges,
        ]);
    }

    private function validateObjectiveConsistency(int $idObjectif, float $poidsActuel, ?float $poidsObjectif, float $tailleCm): array
    {
        $errors = [];
        $objectifModel = new ObjectifModel();
        $objectif = $objectifModel->find($idObjectif);
        if (! is_array($objectif)) {
            $errors['id_objectif'] = 'Objectif invalide.';
            return $errors;
        }

        $label = mb_strtolower((string) $objectif['label_objectif']);
        $isLose = str_contains($label, 'perte');
        $isGain = str_contains($label, 'prise');
        $isIdealObjective = str_contains($label, 'ideal');

        if (($isLose || $isGain) && $poidsObjectif === null) {
            $errors['poids_objectif'] = 'Le poids cible est requis pour cet objectif.';
            return $errors;
        }

        if ($isLose && $poidsObjectif !== null && $poidsObjectif >= $poidsActuel) {
            $errors['poids_objectif'] = 'Pour perdre du poids, le poids cible doit être inférieur au poids actuel.';
        }

        if ($isGain && $poidsObjectif !== null && $poidsObjectif <= $poidsActuel) {
            $errors['poids_objectif'] = 'Pour prendre du poids, le poids cible doit être supérieur au poids actuel.';
        }

        if ($isIdealObjective) {
            $userModel = new UtilisateurModel();
            $imcModel = new ImcModel();
            $imc = $userModel->calculateImc($poidsActuel, $tailleCm);
            $ideal = $imcModel->getIdealRange();

            if ($imc !== null && $ideal !== null) {
                $isAlreadyIdeal = $imc >= (float) $ideal['imc_min'] && $imc <= (float) $ideal['imc_max'];
                if ($isAlreadyIdeal) {
                    $errors['id_objectif'] = 'Vous êtes déjà dans l\'IMC idéal. Choisissez un autre objectif.';
                }
            }
        }

        return $errors;
    }
}
