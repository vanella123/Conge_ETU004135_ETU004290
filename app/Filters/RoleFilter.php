<?php

namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

/**
 * RoleFilter
 *
 * Vérifie que l'utilisateur connecté possède le rôle requis.
 * Doit être utilisé APRÈS AuthFilter.
 *
 * Enregistrement dans app/Config/Filters.php :
 *   'role' => \App\Filters\RoleFilter::class
 *
 * Usage dans les routes (arguments = rôles autorisés) :
 *   $routes->group('admin',   ['filter' => 'role:admin'],         ...);
 *   $routes->group('rh',      ['filter' => 'role:rh,admin'],      ...);
 *   $routes->group('employe', ['filter' => 'role:employe,rh,admin'], ...);
 *
 * Plusieurs rôles séparés par une virgule → logique OU.
 */
class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // 1. L'utilisateur doit être connecté
        if (! session()->get('is_logged_in')) {
            return redirect()->to('/login')
                ->with('error', 'Accès refusé. Veuillez vous connecter.');
        }

        // 2. Si aucun rôle requis → autoriser
        if (empty($arguments)) {
            return;
        }

        $userRole      = session('user_role');
        $rolesAutorises = is_array($arguments) ? $arguments : explode(',', $arguments[0] ?? '');
        $rolesAutorises = array_map('trim', $rolesAutorises);

        // 3. Vérifier que le rôle de l'utilisateur est dans la liste
        if (! in_array($userRole, $rolesAutorises, true)) {
            // Accès refusé → rediriger vers le tableau de bord du rôle actuel
            log_message('warning',
                "Accès refusé à " . current_url() .
                " pour {$userRole} (autorisés : " . implode(',', $rolesAutorises) . ")"
            );

            return redirect()->to($this->dashboardByRole($userRole))
                ->with('error', 'Vous n\'avez pas les droits pour accéder à cette page.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Rien
    }

    private function dashboardByRole(string $role): string
    {
        return match ($role) {
            'admin'   => '/admin/dashboard',
            'rh'      => '/rh/dashboard',
            'employe' => '/employe/dashboard',
            default   => '/login',
        };
    }
}