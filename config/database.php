<?php

/**
 * Configuration de la base de données
 */
return [
    /*
    |--------------------------------------------------------------------------
    | Connexion par défaut
    |--------------------------------------------------------------------------
    |
    | Connexion de base de données par défaut
    |
    */
    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Connexions de base de données
    |--------------------------------------------------------------------------
    |
    | Configuration des différentes connexions de base de données
    |
    */
    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', 'localhost'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'pharmacie'),
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ],
        
        'sqlite' => [
            'driver' => 'sqlite',
            'database' => env('DB_DATABASE', BASE_PATH . '/database/database.sqlite'),
            'prefix' => '',
            'foreign_key_constraints' => env('DB_FOREIGN_KEYS', true),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Options de migration
    |--------------------------------------------------------------------------
    |
    | Options pour les migrations de base de données
    |
    */
    'migrations' => [
        'table' => 'migrations',
        'path' => BASE_PATH . '/database/migrations',
    ],
]; 