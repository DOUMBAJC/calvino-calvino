<?php
/**
 * Ce fichier bootstrap initialise l'application
 */

// Configurer la gestion des erreurs
error_reporting(E_ALL);
ini_set('display_errors', env('APP_DEBUG', false) ? '1' : '0');

// Configurer le fuseau horaire
date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

// Configurer l'encodage
if (function_exists('mb_internal_encoding')) {
    mb_internal_encoding('UTF-8');
} else {
    // Fallback for when mbstring extension is not available
    ini_set('default_charset', 'UTF-8');
}

// Initialiser l'application
$app = \Calvino\Core\Application::getInstance();

// Charger la configuration des routes
$routeConfig = require_once BASE_PATH . '/config/routes.php';

// Appliquer les middlewares globaux
if (isset($routeConfig['middleware']) && is_array($routeConfig['middleware'])) {
    foreach ($routeConfig['middleware'] as $middleware) {
        $app->addMiddleware(new $middleware());
    }
}

// Appliquer les middlewares de l'API
if (isset($routeConfig['middleware_groups']['api']) && is_array($routeConfig['middleware_groups']['api'])) {
    foreach ($routeConfig['middleware_groups']['api'] as $middleware) {
        $app->addMiddleware(new $middleware());
    }
}

// Enregistrer les services essentiels
$app->register(\Calvino\Providers\DatabaseServiceProvider::class);
$app->register(\Calvino\Providers\DatabaseManagerServiceProvider::class);
$app->register(\Calvino\Providers\RouteServiceProvider::class);
$app->register(\Calvino\Providers\MigrationServiceProvider::class);

// Charger les routes API
if (file_exists(BASE_PATH . '/routes/api.php')) {
    require_once BASE_PATH . '/routes/api.php';
}

return $app; 