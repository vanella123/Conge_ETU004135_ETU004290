<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

/**
 * AuthFilter
 *
 * Filtre générique : vérifie qu'un utilisateur est bien connecté.
 * Si non connecté → redirection vers /login.
 *
 * Usage dans app/Config/Filters.php :
 *   'auth' => \App\Filters\AuthFilter::class
 *
 * Usage dans app/Config/Routes.php :
 *   $routes->group('employe', ['filter' => 'auth'], function($routes) { ... });
 */
class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! session()->get('is_logged_in')) {
            // Mémoriser l'URL demandée pour rediriger après connexion
            session()->set('redirect_url', current_url());

            return redirect()->to('/login')
                ->with('error', 'Veuillez vous connecter pour accéder à cette page.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Rien à faire après la réponse
    }
}