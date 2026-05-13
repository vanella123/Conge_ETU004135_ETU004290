<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->get('/', 'HomeController::index');
$routes->get('register', 'Auth::register');
$routes->post('register', 'Auth::saveRegisterPersonal');
$routes->get('register/health', 'Auth::registerHealth');
$routes->post('register/health', 'Auth::saveRegisterHealth');
$routes->post('register/check-email', 'Auth::checkEmailAvailability');
$routes->post('register/imc-preview', 'Auth::imcPreview');
$routes->get('login', 'Auth::login');
$routes->post('login', 'Auth::attemptLogin');
$routes->get('dashboard', 'Auth::dashboard', ['filter' => 'auth']);
$routes->get('transactions', 'Auth::transactions', ['filter' => 'auth']);
$routes->get('promo', 'Auth::promo', ['filter' => 'auth']);
$routes->post('promo', 'Auth::applyPromo', ['filter' => 'auth']);
$routes->get('options', 'OptionController::index', ['filter' => 'auth']);
$routes->post('options/gold/buy/(:num)', 'OptionController::buyGold/$1', ['filter' => 'auth']);
$routes->get('regimes/purchase/(:num)', 'CommandeController::purchase/$1', ['filter' => 'auth']);
$routes->post('regimes/purchase/(:num)', 'CommandeController::confirmPurchase/$1', ['filter' => 'auth']);
$routes->get('profile', 'Auth::profile', ['filter' => 'auth']);
$routes->get('profile/edit', 'Auth::editProfile', ['filter' => 'auth']);
$routes->post('profile/update', 'Auth::updateProfile', ['filter' => 'auth']);
$routes->get('logout', 'Auth::logout', ['filter' => 'auth']);
$routes->get('regimes', 'RegimeController::index');
$routes->get('regimes/(:num)', 'RegimeController::show/$1');
$routes->get('regimes/(:num)/export-pdf', 'RegimeController::exportPdf/$1', ['filter' => 'auth']);
$routes->get('mes-regimes', 'RegimeController::myRegimes', ['filter' => 'auth']);
$routes->get('mes-regimes/(:num)', 'RegimeController::myRegimeDetail/$1', ['filter' => 'auth']);
$routes->get('mes-regimes/(:num)/export-pdf', 'RegimeController::exportRegimePdf/$1', ['filter' => 'auth']);

$routes->post('/admin/authenticate', 'AdminController::authenticate');
$routes->get('/admin/dashboard', 'AdminController::dashboard');
$routes->get('/admin/stats-croisees', 'AdminController::statsCroisees');
$routes->get('/admin', 'AdminController::login');
$routes->get('/admin/login', 'AdminController::login');
$routes->get('/admin/logout', 'AdminController::logout');
$routes->get('/admin/utilisateurs', 'AdminUtilisateurController::index');
$routes->get('/admin/utilisateurs/view/(:num)', 'AdminUtilisateurController::show/$1');
$routes->get('/admin/imc', 'AdminImcController::index');
$routes->get('/admin/imc/edit/(:num)', 'AdminImcController::edit/$1');
$routes->post('/admin/imc/update/(:num)', 'AdminImcController::update/$1');
$routes->get('/admin/regimes', 'AdminRegimeController::index');
$routes->get('/admin/regimes/create', 'AdminRegimeController::create');
$routes->post('/admin/regimes/store', 'AdminRegimeController::store');
$routes->get('/admin/regimes/view/(:num)', 'AdminRegimeController::show/$1');
$routes->get('/admin/regimes/edit/(:num)', 'AdminRegimeController::edit/$1');
$routes->post('/admin/regimes/update/(:num)', 'AdminRegimeController::update/$1');
$routes->post('/admin/regimes/delete/(:num)', 'AdminRegimeController::delete/$1');
$routes->get('/admin/activites', 'AdminActiviteController::index');
$routes->get('/admin/activites/create', 'AdminActiviteController::create');
$routes->post('/admin/activites/store', 'AdminActiviteController::store');
$routes->get('/admin/activites/view/(:num)', 'AdminActiviteController::show/$1');
$routes->get('/admin/activites/edit/(:num)', 'AdminActiviteController::edit/$1');
$routes->post('/admin/activites/update/(:num)', 'AdminActiviteController::update/$1');
$routes->post('/admin/activites/delete/(:num)', 'AdminActiviteController::delete/$1');
$routes->get('/admin/regimes/(:num)/activites', 'AdminActiviteController::regimeActivites/$1');
$routes->post('/admin/regimes/(:num)/activites', 'AdminActiviteController::addRegimeActivite/$1');
$routes->post('/admin/regimes/activites/delete/(:num)', 'AdminActiviteController::removeRegimeActivite/$1');
$routes->get('/admin/promos', 'AdminPromoController::index');
$routes->get('/admin/promos/create', 'AdminPromoController::create');
$routes->post('/admin/promos/store', 'AdminPromoController::store');
$routes->get('/admin/promos/edit/(:num)', 'AdminPromoController::edit/$1');
$routes->post('/admin/promos/update/(:num)', 'AdminPromoController::update/$1');
$routes->post('/admin/promos/delete/(:num)', 'AdminPromoController::delete/$1');
$routes->get('/admin/promos/validate', 'AdminPromoController::validatePage');
$routes->post('/admin/promos/validate/approve/(:num)', 'AdminPromoController::approveRequest/$1');
$routes->post('/admin/promos/validate/reject/(:num)', 'AdminPromoController::rejectRequest/$1');
$routes->get('/admin/options', 'AdminOptionController::index');
$routes->get('/admin/options/create', 'AdminOptionController::create');
$routes->post('/admin/options/store', 'AdminOptionController::store');
$routes->get('/admin/options/view/(:num)', 'AdminOptionController::show/$1');
$routes->get('/admin/options/edit/(:num)', 'AdminOptionController::edit/$1');
$routes->post('/admin/options/update/(:num)', 'AdminOptionController::update/$1');
$routes->post('/admin/options/delete/(:num)', 'AdminOptionController::delete/$1');
