<?php
/**
 * Point d'entrée principal de l'application
 * Toutes les requêtes sont dirigées ici
 */

// Définir le chemin de base de l'application
define('BASE_PATH', dirname(__DIR__));

// Charger l'autoloader (Composer ou personnalisé)
require BASE_PATH . '/bootstrap/autoload.php';

// Charger les variables d'environnement si dotenv est disponible
if (file_exists(BASE_PATH . '/.env')) {
    (new \App\Core\Env())->load(BASE_PATH . '/.env');
}

// Charger la configuration
require_once BASE_PATH . '/config/app.php';

// Charger le bootstrap pour démarrer l'application
require_once BASE_PATH . '/bootstrap/app.php';

// Démarrer l'application
$app = \App\Core\Application::getInstance();
$app->run(); 