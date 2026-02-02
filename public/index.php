<?php
/**
 * Point d'entrée principal de l'application
 * Toutes les requêtes sont dirigées ici
 */

// Définir le chemin de base de l'application
define('BASE_PATH', dirname(__DIR__));

// Charger l'autoloader de Composer
require BASE_PATH . '/vendor/autoload.php';

// Charger les variables d'environnement
if (file_exists(BASE_PATH . '/.env')) {
    (new \Calvino\Core\Env())->load(BASE_PATH . '/.env');
}

// Charger la configuration
require_once BASE_PATH . '/config/app.php';

// Charger le bootstrap pour démarrer l'application
$app = require_once BASE_PATH . '/bootstrap/app.php';

// Démarrer l'application
$app->run();