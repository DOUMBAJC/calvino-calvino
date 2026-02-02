<?php

return [
    // Middlewares globaux appliqués à toutes les requêtes
    'global' => [
        \Calvino\Middleware\CorsMiddleware::class,
        \Calvino\Middleware\JsonMiddleware::class,
    ],
    
    // Middlewares nommés qui peuvent être appliqués par route
    'route' => [
        'auth' => \Calvino\Middleware\AuthMiddleware::class,
        'admin' => \Calvino\Middleware\AdminMiddleware::class,
    ],
]; 