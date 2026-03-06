<?php

declare(strict_types=1);

/**
 * Bootstrap des tests PHPUnit
 * Configure l'environnement de test avant l'exécution des tests.
 */

// Charger l'autoloader Composer
require_once dirname(__DIR__) . '/vendor/autoload.php';

// Définir la constante racine de l'application
define('APP_ROOT', dirname(__DIR__));

// Charger les variables d'environnement de test
// (Les variables définies dans phpunit.xml ont priorité sur le .env)
if (file_exists(APP_ROOT . '/.env')) {
    $lines = file(APP_ROOT . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) {
            continue;
        }
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $key   = trim($key);
            $value = trim($value, " \t\n\r\0\x0B\"'");
            // Ne pas écraser les variables déjà définies (ex: celles de phpunit.xml)
            if (!isset($_ENV[$key])) {
                $_ENV[$key] = $value;
                putenv("{$key}={$value}");
            }
        }
    }
}

/**
 * Helper env() utilisé dans les tests si le framework n'est pas chargé
 */
if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        $value = $_ENV[$key] ?? getenv($key);
        return ($value !== false && $value !== null) ? $value : $default;
    }
}
