<?php

namespace Calvino\Middleware;

use Calvino\Core\Request;

/**
 * CorsMiddleware
 * Gère les en-têtes CORS pour les requêtes cross-origin
 */
class CorsMiddleware implements Middleware
{
    /**
     * Valeurs par défaut pour les configurations CORS
     */
    private const DEFAULT_ALLOWED_HEADERS = 'Content-Type, Authorization, X-Requested-With, Accept, Origin, X-Auth-Token';
    private const DEFAULT_ALLOWED_METHODS = 'GET, POST, PUT, DELETE, PATCH, OPTIONS';
    private const DEFAULT_MAX_AGE = 86400; // 24 heures
    private const DEFAULT_EXPOSE_HEADERS = 'Content-Length, Content-Type';

    /**
     * Traite la requête
     *
     * @param Request $request
     * @param callable $next
     * @return mixed
     */
    public function handle(Request $request, callable $next)
    {
        try {
            // Configurer les en-têtes CORS
            $this->configureCorsHeaders();
            
            // Préflight request - répondre immédiatement avec un statut 200
            if ($request->getMethod() === 'OPTIONS') {
                http_response_code(200);
                exit;
            }
            
            // Continuer avec le traitement pour les requêtes non-OPTIONS
            return $next($request);
        } catch (\Exception $e) {
            // Gérer les erreurs liées à CORS
            error_log("Erreur CORS: " . $e->getMessage());
            http_response_code(500);
            exit;
        }
    }

    /**
     * Configure tous les en-têtes CORS nécessaires
     * 
     * @return void
     */
    private function configureCorsHeaders(): void
    {
        // Récupérer l'origine de la requête
        $requestOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';
        
        // Récupérer la configuration du domaine autorisé depuis .env
        $allowedOrigin = env('CORS_ALLOWED_ORIGINS', '');
        
        // Récupérer les autres configurations depuis la configuration de l'application
        $corsConfig = config('app.cors', []);
        
        $allowedHeaders = $corsConfig['allowed_headers'] ?? self::DEFAULT_ALLOWED_HEADERS;
        $allowedMethods = $corsConfig['allowed_methods'] ?? self::DEFAULT_ALLOWED_METHODS;
        $maxAge = (int)($corsConfig['max_age'] ?? self::DEFAULT_MAX_AGE);
        $allowCredentials = $corsConfig['supports_credentials'] ?? true;
        $exposeHeaders = $corsConfig['exposed_headers'] ?? self::DEFAULT_EXPOSE_HEADERS;
        
        // Définir les en-têtes standard
        header("Access-Control-Allow-Methods: {$allowedMethods}");
        header("Access-Control-Allow-Headers: {$allowedHeaders}");
        header("Access-Control-Max-Age: {$maxAge}");
        
        if (!empty($exposeHeaders)) {
            header("Access-Control-Expose-Headers: {$exposeHeaders}");
        }
        
        // Vary: Origin pour optimiser la mise en cache
        header('Vary: Origin');
        
        // Ajout des en-têtes de sécurité supplémentaires
        $this->addSecurityHeaders();
        
        // Vérifier si l'origine de la requête correspond au domaine autorisé
        $this->setAllowOriginHeader($requestOrigin, $allowedOrigin, $allowCredentials);
    }
    
    /**
     * Définit l'en-tête Access-Control-Allow-Origin si l'origine est autorisée
     * 
     * @param string $requestOrigin L'origine de la requête
     * @param string $allowedOrigin L'origine autorisée du fichier .env
     * @param bool $allowCredentials Si les credentials sont autorisés
     * @return void
     */
    private function setAllowOriginHeader(string $requestOrigin, string $allowedOrigin, bool $allowCredentials): void
    {
        // Si l'origine de la requête est vide, on ne fait rien
        if (empty($requestOrigin)) {
            return;
        }
        
        // Si l'origine est autorisée (et exactement égale à celle définie dans .env)
        if ($requestOrigin === $allowedOrigin && !empty($allowedOrigin)) {
            header("Access-Control-Allow-Origin: {$requestOrigin}");
            
            if ($allowCredentials) {
                header("Access-Control-Allow-Credentials: true");
            }
        } else {
            // Log si l'origine est refusée pour faciliter le débogage
            error_log("CORS: Origine refusée: {$requestOrigin}, origine autorisée: {$allowedOrigin}");
        }
    }
    
    /**
     * Ajoute des en-têtes de sécurité supplémentaires
     * 
     * @return void
     */
    private function addSecurityHeaders(): void
    {
        // Protection contre le clickjacking
        header('X-Frame-Options: SAMEORIGIN');
        
        // Protection contre les attaques XSS
        header('X-XSS-Protection: 1; mode=block');
        
        // Empêche le navigateur de détecter le type MIME
        header('X-Content-Type-Options: nosniff');
        
        // Politique de référence
        header('Referrer-Policy: strict-origin-when-cross-origin');
    }
} 