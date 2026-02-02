<?php

/**
 * Script pour exécuter les seeders
 */

// Définir le chemin de base
define('BASE_PATH', dirname(__DIR__));

// Charger l'autoloader
require_once BASE_PATH . '/bootstrap/autoload.php';

echo "=== Exécution des seeders ===\n\n";

// Charger tous les fichiers de seeders
$seederFiles = glob(__DIR__ . '/seeders/*.php');

foreach ($seederFiles as $file) {
    require_once $file;
}

// Exécuter le DatabaseSeeder principal
try {
    $seeder = new Database\Seeders\DatabaseSeeder();
    $seeder->run();
    echo "\n=== Seeding terminé avec succès ===\n";
} catch (Exception $e) {
    echo "\nErreur lors du seeding: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
