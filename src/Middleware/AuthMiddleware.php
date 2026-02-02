<?php

namespace Calvino\Middleware;

use Calvino\Core\Request;
use Calvino\Core\Response;

/**
 * AuthMiddleware
 * Vérifie si l'utilisateur est authentifié
 */
class AuthMiddleware implements Middleware
{
    /**
     * Routes à exclure de l'authentification
     *
     * @var array
     */
    protected array $except = [
        '/login',
        '/register',
        '/password/reset',
        '/api/auth/login',
        '/api/auth/register',
        '/api/docs',
    ];

    /**
     * Traite la requête
     *
     * @param Request $request
     * @param callable $next
     * @return mixed
     */
    public function handle(Request $request, callable $next)
    {
        // Vérifier si la route est exclue de l'authentification
        $path = $request->getPath();
        
        foreach ($this->except as $excludedPath) {
            if ($path === $excludedPath || strpos($path, $excludedPath) === 0) {
                // Route exclue, passer au middleware suivant
                return $next($request);
            }
        }
        
        // Pour les routes API, vérifier d'abord le token
        if (strpos($path, '/api/') === 0 && !in_array($path, $this->except)) {
            $token = $this->getBearerToken();
            
            if (!$token) {
                json([
                    'error' => true,
                    'message' => 'Token manquant',
                    'status' => 401
                ], 401);
                exit;
            }
            
            // Vérifier la validité du token
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                json([
                    'error' => true,
                    'message' => 'Format du token invalide',
                    'status' => 401
                ], 401);
                exit;
            }
        }
        
        // Vérifier si l'utilisateur est authentifié (via session ou token)
        if (!auth()->check()) {
            // Si la requête est une requête API, renvoyer une erreur JSON
            if ($request->isJson() || strpos($request->getPath(), '/api/') === 0) {
                json([
                    'error' => true,
                    'message' => 'Non authentifié ou token invalide',
                    'status' => 401
                ], 401);
                exit;
            }
            
            // Stocker l'URL actuelle pour la redirection après connexion
            if ($request->getMethod() === 'GET') {
                $_SESSION['redirect_after_login'] = $request->getPath();
            }
            exit;
        }
        
        // Utilisateur authentifié, continuer avec la requête
        return $next($request);
    }
    
    /**
     * Récupère le token Bearer de l'en-tête Authorization
     *
     * @return string|null
     */
    protected function getBearerToken(): ?string
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        
        if (!$authHeader) {
            return null;
        }
        
        if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
}
