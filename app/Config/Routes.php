<?php

/**
 * Routes.php — Configuration des routes CI4
 *
 * Stratégie de sécurité :
 *   - Routes publiques  : /login, /logout (pas de filtre)
 *   - Routes employé    : filtre auth + role:employe,rh,admin
 *   - Routes RH         : filtre auth + role:rh,admin
 *   - Routes admin      : filtre auth + role:admin
 */

use CodeIgniter\Router\RouteCollection;
/** @var RouteCollection $routes */

// Aliases de compatibilité avec l'ancienne structure
$routes->get('connexion', 'AuthController::loginForm');
$routes->post('connexion', 'AuthController::loginAction');
$routes->get('deconnexion', 'AuthController::logout');
// ----------------------------------------------------------------
// Page d'accueil → redirection vers login
// ----------------------------------------------------------------
$routes->get('/', function () {
    return redirect()->to('/login');
});

// ----------------------------------------------------------------
// Authentification (routes PUBLIQUES — pas de filtre)
// ----------------------------------------------------------------
$routes->get('/login',  'AuthController::loginForm');
$routes->post('/login', 'AuthController::loginAction');
$routes->get('/logout', 'AuthController::logout');

// ================================================================
// Routes EMPLOYÉ  (accès : employe + rh + admin)
// ================================================================
$routes->group('employe', ['filter' => 'auth|role:employe,rh,admin'], function ($routes) {

    // Tableau de bord
    $routes->get('dashboard',  'Employe\DashboardController::index');

    // Congés
    $routes->get('conges',           'Employe\CongeController::index');
    $routes->get('conges/demande',   'Employe\CongeController::form');
    $routes->post('conges/demande',  'Employe\CongeController::submit');
    $routes->get('conges/annuler/(:num)', 'Employe\CongeController::cancel/$1');

    // Soldes
    $routes->get('soldes', 'Employe\SoldeController::index');

    // Profil
    $routes->get('profil',  'Employe\ProfilController::index');
    $routes->post('profil', 'Employe\ProfilController::update');
});

// ================================================================
// Routes RH  (accès : rh + admin seulement)
// ================================================================
$routes->group('rh', ['filter' => 'auth|role:rh,admin'], function ($routes) {

    // Tableau de bord RH
    $routes->get('dashboard', 'Rh\DashboardController::index');

    // Gestion des demandes
    $routes->get('demandes', 'Rh\DemandesController::index');
    $routes->post('demandes/approuver', 'Rh\DemandesController::approuver');
    $routes->post('demandes/refuser', 'Rh\DemandesController::refuser');

    // Gestion des employés
    $routes->get('employes', 'Rh\EmployesController::index');
    
    // Soldes employés
    $routes->get('soldes', 'Rh\SoldeController::index');
});

// ================================================================
// Routes ADMIN  (accès : admin seulement)
// ================================================================
$routes->group('admin', ['filter' => 'auth|role:admin'], function ($routes) {

    // Tableau de bord
    $routes->get('dashboard', 'Admin\DashboardController::index');

    // --- Gestion des employés ---
    $routes->get('employes',             'Admin\EmployeController::index');
    $routes->get('employes/ajouter',     'Admin\EmployeController::create');
    $routes->post('employes/ajouter',    'Admin\EmployeController::store');
    $routes->get('employes/edit/(:num)', 'Admin\EmployeController::edit/$1');
    $routes->post('employes/edit/(:num)', 'Admin\EmployeController::update/$1');
    $routes->get('employes/desactiver/(:num)', 'Admin\EmployeController::deactivate/$1');

    // --- Gestion des départements ---
    $routes->get('departements', 'Admin\DepartementController::index');
    $routes->post('departements/ajouter', 'Admin\DepartementController::store');
    $routes->post('departements/edit/(:num)', 'Admin\DepartementController::update/$1');
    $routes->get('departements/supprimer/(:num)', 'Admin\DepartementController::delete/$1');

    // --- Types de congé ---
    $routes->get('types-conge', 'Admin\TypeCongeController::index');
    $routes->post('types-conge/ajouter', 'Admin\TypeCongeController::store');
    $routes->post('types-conge/edit/(:num)', 'Admin\TypeCongeController::update/$1');

    // --- Soldes annuels ---
    $routes->get('soldes', 'Admin\SoldeController::index');
    $routes->post('soldes/initialiser', 'Admin\SoldeController::initialize');

    // --- Vue globale des demandes ---
    $routes->get('demandes', 'Admin\DemandeController::index');
});