<?php

/**
 * Autoloader personnalisé pour notre application
 * Implémente le chargement automatique PSR-4
 */

// Définir la fonction d'autoload
spl_autoload_register(function ($class) {
    // Préfixe de base pour le namespace de l'application
    $baseNamespace = 'App\\';
    $baseDir = dirname(__DIR__) . '/app/';
    
    // Si la classe ne commence pas par le préfixe App\, on ne s'en occupe pas
    if (strpos($class, $baseNamespace) !== 0) {
        return;
    }
    
    // Obtenir le chemin relatif de la classe (sans le préfixe App\)
    $relativeClass = substr($class, strlen($baseNamespace));
    
    // Remplacer les séparateurs de namespace par des séparateurs de répertoire
    // et ajouter .php à la fin
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
    
    // Si le fichier existe, on l'inclut
    if (file_exists($file)) {
        require_once $file;
    }
});

// Charger les fichiers helpers
require_once dirname(__DIR__) . '/app/Helpers/functions.php';

// Si le fichier autoload.php de Composer existe, on le charge aussi
$composerAutoload = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
} 