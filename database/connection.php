<?php

/**
 * Établit la connexion à la base de données pour les migrations
 */

// Définir le chemin de base s'il n'est pas défini
if (!defined('BASE_PATH')) {
    define('BASE_PATH', dirname(__DIR__));
}

// Charger les variables d'environnement si ce n'est pas déjà fait
if (!function_exists('env')) {
    if (file_exists(BASE_PATH . '/.env')) {
        (new \Calvino\Core\Env())->load(BASE_PATH . '/.env');
    }
    
    function env($key, $default = null) {
        return isset($_ENV[$key]) ? $_ENV[$key] : $default;
    }
}

// Informations de connexion
$driver = env('DB_CONNECTION', 'mysql');
$host = env('DB_HOST', 'localhost');
$port = env('DB_PORT', '3306');
$database = env('DB_DATABASE', 'pharmacie_manager');
$username = env('DB_USERNAME', 'root');
$password = env('DB_PASSWORD', '');
$charset = env('DB_CHARSET', 'utf8mb4');

// Construire le DSN sans spécifier la base de données pour la vérification initiale
if (strpos($host, '/') !== false) {
    // Si le host contient un slash, on suppose qu'il s'agit d'un socket Unix
    $baseDsn = "{$driver}:unix_socket={$host};charset={$charset}";
} else {
    // Sinon, on utilise le host et le port
    $baseDsn = "{$driver}:host={$host};port={$port};charset={$charset}";
}

// DSN complet avec la base de données
$dsn = $baseDsn . ";dbname={$database}";

// Options PDO
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    // D'abord, se connecter sans spécifier la base de données
    $tempPdo = new PDO($baseDsn, $username, $password, $options);
    
    // Vérifier si la base de données existe
    $stmt = $tempPdo->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$database'");
    $dbExists = $stmt->fetchColumn();
    
    if (!$dbExists) {
        // Créer la base de données si elle n'existe pas
        $tempPdo->exec("CREATE DATABASE `$database` CHARACTER SET $charset");
        $databaseCreated = true;
    }
    
    // Fermer la connexion temporaire
    $tempPdo = null;
    
    // Se connecter à la base de données spécifique
    $pdo = new PDO($dsn, $username, $password, $options);
    
    // Si la base de données vient d'être créée, exécuter les migrations
    if (isset($databaseCreated) && $databaseCreated) {
        // Exécuter les migrations
        $migrationPath = BASE_PATH . '/database/migrations';
        if (is_dir($migrationPath)) {
            $migrations = glob($migrationPath . '/*.php');
            sort($migrations);
            
            foreach ($migrations as $migration) {
                require_once $migration;
                $className = pathinfo($migration, PATHINFO_FILENAME);
                if (class_exists($className)) {
                    $migrationInstance = new $className();
                    if (method_exists($migrationInstance, 'up')) {
                        $migrationInstance->up($pdo);
                    }
                }
            }
        }
    }
    
    return $pdo;
} catch (PDOException $e) {
    die("Erreur de connexion à la base de données: " . $e->getMessage());
} 