<?php

/**
 * Configuration des routes
 * Ce fichier contient la configuration globale des routes
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Middlewares globaux
    |--------------------------------------------------------------------------
    |
    | Ces middlewares sont appliqués à toutes les routes
    |
    */
    'middleware' => [
        // Middlewares globaux pour l'API
        \Calvino\Middleware\CorsMiddleware::class,
        \Calvino\Middleware\JsonMiddleware::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Groupes de middlewares
    |--------------------------------------------------------------------------
    |
    | Ces groupes de middlewares peuvent être appliqués à des routes spécifiques
    |
    */
    'middleware_groups' => [
        'api' => [
            // Middlewares pour les routes API
            \Calvino\Middleware\JsonMiddleware::class,
            \Calvino\Middleware\CorsMiddleware::class,
        ],
        'auth' => [
            // Middlewares pour les routes API nécessitant une authentification
            \Calvino\Middleware\AuthMiddleware::class,
        ],
        'admin' => [
            // Middlewares pour les routes d'administration
            \Calvino\Middleware\AuthMiddleware::class,
            \Calvino\Middleware\AdminMiddleware::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Préfixes des routes
    |--------------------------------------------------------------------------
    |
    | Préfixes pour les groupes de routes
    |
    */
    'prefixes' => [
        'api' => 'api',
        'admin' => 'admin',
    ],

    /*
    |--------------------------------------------------------------------------
    | Espaces de noms des contrôleurs
    |--------------------------------------------------------------------------
    |
    | Espaces de noms pour les groupes de routes
    |
    */
    'namespaces' => [
        'api' => 'App\\Controllers\\Api',
    ],

    /*
    |--------------------------------------------------------------------------
    | Paramètres de chemin
    |--------------------------------------------------------------------------
    |
    | Configuration des paramètres de chemin pour les routes
    |
    */
    'patterns' => [
        'id' => '[0-9]+',
        'slug' => '[a-z0-9-]+',
    ],
]; 