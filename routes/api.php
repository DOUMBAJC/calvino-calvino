<?php

/**
 * Routes API
 * Ce fichier contient toutes les routes pour l'API
 */

use Calvino\Core\Application;

// Récupérer le routeur de l'application
$router = Application::getInstance()->getRouter();

/**
 * Route de débug — uniquement disponible en environnement local/development
 */
if (in_array(env('APP_ENV', 'production'), ['local', 'development'])) {
    $router->get('/debug/routes', [function () use ($router) {
        $routes    = $router->getRoutes();
        $routeList = [];

        foreach ($routes as $route) {
            $routeList[] = [
                'method'     => $route->getMethod(),
                'path'       => $route->getPath(),
                'controller' => $route->getController(),
                'action'     => $route->getAction(),
            ];
        }

        return [
            'total_routes' => count($routes),
            'routes'       => $routeList,
        ];
    }]);
}

/**
 * Route de test pour vérifier si l'API répond
 */
$router->get('/', [function () {
    return [
        'status'  => 'success',
        'message' => 'Bienvenue sur Calvino Framework.',
        'version' => env('API_VERSION', '1.0'),
    ];
}]);

/**
 * Routes d'authentification publiques
 * La route de login est protégée par le ThrottleMiddleware (anti-brute force).
 */
$router->post('auth/login', 'AuthController@login', ['middleware' => 'throttle']);
$router->post('auth/refresh-token', 'AuthController@refreshToken');

/**
 * Routes de réinitialisation de mot de passe (publiques, avec throttle)
 */
$router->post('auth/forgot-password', 'PasswordResetController@forgotPassword', ['middleware' => 'throttle']);
$router->post('auth/reset-password', 'PasswordResetController@resetPassword', ['middleware' => 'throttle']);
$router->get('auth/reset-password/verify', 'PasswordResetController@verifyToken');

/**
 * Routes protégées par authentification
 */
$router->group([
    'middleware' => 'auth',
], function () use ($router) {
    // Authentification
    $router->post('/auth/logout', 'AuthController@logout');
    $router->get('/auth/me', 'AuthController@me');
    $router->get('/auth/profile', 'AuthController@profile');
    $router->put('/auth/profile', 'AuthController@updateProfile');
    $router->post('/auth/password', 'AuthController@changePassword');
    $router->post('/auth/password/force-change', 'AuthController@forceChangeDefaultPassword');

    // Sessions
    $router->get('/auth/sessions', 'AuthController@sessions');
    $router->post('/auth/sessions/logout/{sessionId}', 'AuthController@logoutSession');
    $router->post('/auth/sessions/logout-others', 'AuthController@logoutOtherSessions');

    // Notifications
    $router->get('/notifications', 'NotificationController@index');
    $router->get('/notifications/{id}', 'NotificationController@show');
    $router->put('/notifications/{id}/read', 'NotificationController@markAsRead');
    $router->put('/notifications/read-all', 'NotificationController@markAllAsRead');
    $router->delete('/notifications/{id}', 'NotificationController@destroy');
    $router->delete('/notifications', 'NotificationController@destroyAll');

    // Journaux d'activités (admin seulement)
    $router->group([
        'middleware' => 'admin',
    ], function () use ($router) {
        $router->get('/activity-logs', 'ActivityLogController@index');
        $router->get('/activity-logs/stats', 'ActivityLogController@stats');
        $router->get('/activity-logs/filters', 'ActivityLogController@filterOptions');
        $router->get('/activity-logs/export', 'ActivityLogController@export');
        $router->get('/activity-logs/download/{filename}', 'ActivityLogController@download');
        $router->get('/activity-logs/{id}', 'ActivityLogController@show');
    });

    // Utilisateurs (admin seulement)
    $router->group([
        'middleware' => 'admin',
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
