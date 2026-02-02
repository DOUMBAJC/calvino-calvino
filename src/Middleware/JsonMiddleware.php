<?php

namespace Calvino\Middleware;

use Calvino\Core\Request;

/**
 * JsonMiddleware
 * Configure la réponse pour retourner du JSON
 */
class JsonMiddleware implements Middleware
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
        // Définir l'en-tête de type de contenu
        header('Content-Type: application/json');
        
        // Vérifier la méthode de la requête pour CORS
        if ($request->getMethod() === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
        
        // Continuer avec le traitement
        return $next($request);
    }
} 