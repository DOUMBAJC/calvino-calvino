<?php

namespace Calvino\Middleware;

use Calvino\Core\Request;

/**
 * AdminMiddleware
 * Vérifie si l'utilisateur a le rôle d'administrateur
 */
class AdminMiddleware implements Middleware
{
    /**
     * Traite la requête
     *
     * @param Request $request
     * @param callable $next
     * @return mixed
     */
    public function handle(Request $request, callable $next)
    {
        // L'AuthMiddleware devrait déjà avoir vérifié l'authentification
        $user = auth()->user();
        
        // Vérifier si l'utilisateur a le rôle admin
        if (!$user || !$user->hasRole('admin')) {
            // Si la requête est une requête API, renvoyer une erreur JSON
            if ($request->isJson() || strpos($request->getPath(), '/api/') === 0) {
                json([
                    'error' => true,
                    'message' => 'Accès non autorisé',
                    'status' => 403
                ], 403);
                exit;
            }
        }
        
        // Utilisateur avec les droits admin, continuer avec la requête
        return $next($request);
    }
}
