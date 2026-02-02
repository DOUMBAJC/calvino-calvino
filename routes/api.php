<?php

/**
 * Routes API
 * Ce fichier contient toutes les routes pour l'API
 */

use Calvino\Core\Application;

// Récupérer le routeur de l'application
$router = Application::getInstance()->getRouter();

// Route de débug pour voir toutes les routes enregistrées
$router->get('/debug/routes', [function () use ($router) {
    $routes = $router->getRoutes();
    $routeList = [];

    foreach ($routes as $route) {
        $routeList[] = [
            'method' => $route->getMethod(),
            'path' => $route->getPath(),
            'controller' => $route->getController(),
            'action' => $route->getAction()
        ];
    }

    return [
        'total_routes' => count($routes),
        'routes' => $routeList
    ];
}]);

/**
 * Route de test pour vérifier si l'API répond
 */
$router->get('/', [function () {
    return [
        'status' => 'success',
        'message' => 'Bienvenue sur Calvino Framework.'
    ];
}]);

/**
 * Routes d'authentification publiques
 */
$router->post('auth/login', 'AuthController@login');
$router->post('auth/refresh-token', 'AuthController@refreshToken');

/**
 * Routes protégées par authentification
 */
$router->group([
    'middleware' => 'auth'
], function () use ($router) {
    // Routes d'authentification protégées
    $router->post('/auth/logout', 'AuthController@logout');
    $router->get('/auth/profile', 'AuthController@profile');
    $router->put('/auth/profile', 'AuthController@updateProfile');
    $router->post('/auth/password', 'AuthController@changePassword');
    
    // Routes pour la gestion des sessions
    $router->get('/auth/sessions', 'AuthController@sessions');
    $router->post('/auth/sessions/logout/{sessionId}', 'AuthController@logoutSession');
    $router->post('/auth/sessions/logout-others', 'AuthController@logoutOtherSessions');

    // Routes pour les utilisateurs (admin seulement)
    $router->group([
        'middleware' => 'admin'
    ], function () use ($router) {
        $router->get('/users', 'UserController@index');
        $router->get('/users/{id}', 'UserController@show');
        $router->post('/users', 'UserController@store');
        $router->put('/users/{id}', 'UserController@update');
        $router->delete('/users/{id}', 'UserController@destroy');
        $router->put('/users/{id}/status', 'UserController@toggleStatus');
        $router->post('/auth/register', 'AuthController@register');
    });
});
