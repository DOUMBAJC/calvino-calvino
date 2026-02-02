<?php

/**
 * Script pour exécuter les migrations
 */

// Définir le chemin de base
define('BASE_PATH', dirname(__DIR__));

// Couleurs ANSI
define('COLOR_GREEN', "\033[32m");
define('COLOR_BLUE', "\033[34m");
define('COLOR_YELLOW', "\033[33m");
define('COLOR_RED', "\033[31m");
define('COLOR_CYAN', "\033[36m");
define('COLOR_MAGENTA', "\033[35m");
define('COLOR_RESET', "\033[0m");
define('COLOR_BOLD', "\033[1m");

// Fonction pour afficher une animation de chargement
function showLoadingAnimation($message, $duration = 3) {
    echo COLOR_BOLD . $message . COLOR_RESET;
    $chars = ['⣾', '⣽', '⣻', '⢿', '⡿', '⣟', '⣯', '⣷'];
    $colors = [COLOR_CYAN, COLOR_BLUE, COLOR_MAGENTA];
    $end = microtime(true) + $duration;
    
    while (microtime(true) < $end) {
        foreach ($chars as $i => $char) {
            $color = $colors[$i % count($colors)];
            echo "\r$message " . $color . $char . COLOR_RESET;
            usleep(100000); // 100ms pause
        }
    }
    echo "\r$message " . COLOR_GREEN . "Terminé ✓" . COLOR_RESET . "\n";
}

// Fonction pour afficher une barre de progression
function showProgressBar($current, $total, $taskName) {
    $percent = round(($current / $total) * 100);
    $bar = '';
    $bar_length = 30;
    $completed = floor(($current / $total) * $bar_length);
    
    for ($i = 0; $i < $bar_length; $i++) {
        if ($i < $completed) {
            $bar .= COLOR_GREEN . '█' . COLOR_RESET;
        } else {
            $bar .= COLOR_BLUE . '░' . COLOR_RESET;
        }
    }
    
    echo "\r[" . $bar . "] " . COLOR_YELLOW . "$percent%" . COLOR_RESET . " - $taskName";
    
    if ($current == $total) {
        echo " " . COLOR_GREEN . "✓" . COLOR_RESET . "\n";
    }
}

// Animation élaborée pour un début de processus
function showStartAnimation() {
    echo COLOR_BOLD . "\n╭───────────────────────────────────╮\n";
    echo "│                                   │\n";
    echo "│   " . COLOR_CYAN . "DÉMARRAGE DES MIGRATIONS" . COLOR_RESET . COLOR_BOLD . "         │\n";
    echo "│                                   │\n";
    echo "╰───────────────────────────────────╯" . COLOR_RESET . "\n\n";

    $chars = ['◜', '◝', '◞', '◟'];
    for ($i = 0; $i < 10; $i++) {
        echo "\r" . COLOR_CYAN . $chars[$i % 4] . COLOR_RESET . " Initialisation du processus...";
        usleep(100000);
    }
    echo "\r" . COLOR_GREEN . "✓" . COLOR_RESET . " Initialisation terminée       \n\n";
}

// Animation de fin plus élaborée
function showEndAnimation() {
    echo "\n\n";
    showLoadingAnimation("Finalisation du processus...", 2);
    
    echo "\n" . COLOR_BOLD . COLOR_GREEN;
    $lines = [
        "╭───────────────────────────────────╮",
        "│                                   │",
        "│     MIGRATIONS TERMINÉES AVEC     │",
        "│            SUCCÈS! 🎉            │",
        "│                                   │",
        "╰───────────────────────────────────╯"
    ];
    
    foreach ($lines as $line) {
        echo "$line\n";
        usleep(100000);
    }
    echo COLOR_RESET . "\n";
}

// Démarrer avec une animation
showStartAnimation();

// Charger les classes de migration avec animation
showLoadingAnimation("Recherche des fichiers de migration...");

$migrationFiles = glob(__DIR__ . '/migrations/*.php');
$migrations = [];
$totalFiles = count($migrationFiles);

echo "\n" . COLOR_BOLD . "Chargement de " . COLOR_YELLOW . $totalFiles . COLOR_RESET . COLOR_BOLD . " fichiers de migration:" . COLOR_RESET . "\n";

foreach ($migrationFiles as $index => $file) {
    $fileName = basename($file);
    require_once $file;
    $className = pathinfo($file, PATHINFO_FILENAME);
    $className = preg_replace('/^\d_/', '', $className);
    $className = str_replace(['_', '-'], ' ', $className);
    $className = ucwords($className);
    $className = str_replace(' ', '', $className);
    
    showProgressBar($index + 1, $totalFiles, "Chargement de " . COLOR_CYAN . $fileName . COLOR_RESET);
    usleep(200000); // Ralentir légèrement pour l'effet visuel
    
    if (class_exists($className)) {
        $migrations[] = [
            'name' => $className,
            'file' => $file,
            'instance' => new $className()
        ];
    } else {
        echo "\n" . COLOR_RED . "Erreur : La classe {$className} n'existe pas dans le fichier {$file}" . COLOR_RESET . "\n";
    }
}

echo "\n";
showLoadingAnimation("Préparation de l'exécution des migrations...", 1);

// Tri des migrations par nom de fichier
usort($migrations, function ($a, $b) {
    return strnatcmp($a['file'], $b['file']);
});

// Exécuter les migrations dans l'ordre
$totalMigrations = count($migrations);
echo "\n" . COLOR_BOLD . "Exécution de " . COLOR_YELLOW . $totalMigrations . COLOR_RESET . COLOR_BOLD . " migrations:" . COLOR_RESET . "\n";

foreach ($migrations as $index => $migration) {
    $migrationName = basename($migration['file']);
    echo "\n[" . COLOR_CYAN . $migrationName . COLOR_RESET . "] "; 
    
    // Animation pendant l'exécution
    $chars = ['⣾', '⣽', '⣻', '⢿', '⡿', '⣟', '⣯', '⣷'];
    $colors = [COLOR_CYAN, COLOR_BLUE, COLOR_MAGENTA, COLOR_YELLOW];
    for ($i = 0; $i < 10; $i++) {
        $color = $colors[$i % count($colors)];
        echo "\r[" . COLOR_CYAN . $migrationName . COLOR_RESET . "] " . $color . $chars[$i % count($chars)] . COLOR_RESET;
        usleep(100000);
    }
    
    // Exécuter la migration
    $migration['instance']->up();
    
    echo "\r[" . COLOR_CYAN . $migrationName . COLOR_RESET . "] " . COLOR_GREEN . "Terminé ✓" . COLOR_RESET . "\n";
    showProgressBar($index + 1, $totalMigrations, "Progression globale");
}

// Animation de fin
showEndAnimation(); 