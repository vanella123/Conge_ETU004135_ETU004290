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
$routes->get('connexion', 'EmployeController::login');
$routes->post('connexion', 'EmployeController::checkEmployeExist');

$routes->get('login', 'EmployeController::login');
$routes->post('login', 'EmployeController::checkEmployeExist');

$routes->get('deconnexion', 'EmployeController::deconnexion', ['filter' => 'auth']);
$routes->get('logout', 'EmployeController::deconnexion', ['filter' => 'auth']);
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
    $routes->get('dashboard', 'RH\DashboardController::index');

    // Gestion des demandes
    $routes->get('demandes',                 'RH\DemandeController::index');
    $routes->get('demandes/(:num)',          'RH\DemandeController::show/$1');
    $routes->post('demandes/(:num)/approuver', 'RH\DemandeController::approve/$1');
    $routes->post('demandes/(:num)/refuser',   'RH\DemandeController::refuse/$1');

    // Soldes employés
    $routes->get('soldes',              'RH\SoldeController::index');
    $routes->get('soldes/(:num)',       'RH\SoldeController::show/$1');
});

// ================================================================
// Routes ADMIN  (accès : admin seulement)
// ================================================================
$routes->group('admin', ['filter' => 'auth|role:admin'], function ($routes) {

    // Tableau de bord
    $routes->get('dashboard', 'Admin\DashboardController::index');

    // --- Gestion des employés ---
    $routes->get('employes',             'Admin\EmployeController::index');
    $routes->get('employes/nouveau',     'Admin\EmployeController::create');
    $routes->post('employes/nouveau',    'Admin\EmployeController::store');
    $routes->get('employes/(:num)',      'Admin\EmployeController::edit/$1');
    $routes->post('employes/(:num)',     'Admin\EmployeController::update/$1');
    $routes->get('employes/(:num)/desactiver', 'Admin\EmployeController::deactivate/$1');

    // --- Gestion des départements ---
    $routes->get('departements',           'Admin\DepartementController::index');
    $routes->post('departements',          'Admin\DepartementController::store');
    $routes->post('departements/(:num)',   'Admin\DepartementController::update/$1');
    $routes->get('departements/(:num)/supprimer', 'Admin\DepartementController::delete/$1');

    // --- Types de congé ---
    $routes->get('types-conge',           'Admin\TypeCongeController::index');
    $routes->post('types-conge',          'Admin\TypeCongeController::store');
    $routes->post('types-conge/(:num)',   'Admin\TypeCongeController::update/$1');

    // --- Soldes annuels ---
    $routes->get('soldes',        'Admin\SoldeController::index');
    $routes->post('soldes/init',  'Admin\SoldeController::initialize');

    // --- Vue globale des demandes ---
    $routes->get('demandes',      'Admin\DemandeController::index');
});