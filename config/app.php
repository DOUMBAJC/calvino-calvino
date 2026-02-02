<?php

/**
 * Configuration principale de l'application
 */
return [
    /*
    |--------------------------------------------------------------------------
    | Nom de l'application
    |--------------------------------------------------------------------------
    |
    | Ce nom est utilisé pour l'application dans différents endroits.
    |
    */
    'name' => env('APP_NAME', 'Calvino Framework'),

    /*
    |--------------------------------------------------------------------------
    | Environnement de l'application
    |--------------------------------------------------------------------------
    |
    | Définit l'environnement d'exécution (production, development, testing)
    |
    */
    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Mode de débogage
    |--------------------------------------------------------------------------
    |
    | Activé ou désactive l'affichage des erreurs
    |
    */
    'debug' => env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | URL de l'application
    |--------------------------------------------------------------------------
    |
    | URL de base de l'application
    |
    */
    'url' => env('APP_URL', 'http://localhost:8000'),

    /*
    |--------------------------------------------------------------------------
    | Fuseau horaire
    |--------------------------------------------------------------------------
    |
    | Définit le fuseau horaire par défaut
    |
    */
    'timezone' => env('APP_TIMEZONE', 'UTC'),

    /*
    |--------------------------------------------------------------------------
    | Locale
    |--------------------------------------------------------------------------
    |
    | Définit la locale par défaut pour l'application
    |
    */
    'locale' => env('APP_LOCALE', 'fr'),

    /*
    |--------------------------------------------------------------------------
    | Locale de fallback
    |--------------------------------------------------------------------------
    |
    | Définit la locale de fallback pour l'application
    |
    */
    'fallback_locale' => 'en',

    /*
    |--------------------------------------------------------------------------
    | Locales disponibles
    |--------------------------------------------------------------------------
    |
    | Liste des locales disponibles pour l'application
    |
    */
    'available_locales' => ['fr', 'en'],

    /*
    |--------------------------------------------------------------------------
    | Providers de service
    |--------------------------------------------------------------------------
    |
    | Liste des providers de service à charger au démarrage
    |
    */
    'providers' => [
        // Core Providers
        \Calvino\Providers\AppServiceProvider::class,
        \Calvino\Providers\RouteServiceProvider::class,
        \Calvino\Providers\DatabaseServiceProvider::class,
        
        // Application Providers
    ],

    /*
    |--------------------------------------------------------------------------
    | Middlewares globaux
    |--------------------------------------------------------------------------
    |
    | Ces middlewares sont exécutés pour chaque requête
    |
    */
    'middlewares' => [
        \Calvino\Middleware\CorsMiddleware::class,
        \Calvino\Middleware\JsonMiddleware::class,
        \Calvino\Middleware\AuthMiddleware::class,
        \Calvino\Middleware\AdminMiddleware::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuration CORS
    |--------------------------------------------------------------------------
    */
    'cors' => [
        'allowed_origins' => env('CORS_ALLOWED_ORIGINS', ''),
        'allowed_methods' => 'GET, POST, PUT, DELETE, OPTIONS',
        'allowed_headers' => 'Content-Type, Authorization, X-Requested-With, X-CSRF-TOKEN',
        'exposed_headers' => '',
        'max_age' => 86400, // 24 heures
        'supports_credentials' => true,
    ],
]; 