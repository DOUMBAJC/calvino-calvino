<?php

namespace Calvino\Providers;

use Calvino\Core\Application;
use PDO;

/**
 * Fournisseur de services pour les migrations
 */
class MigrationServiceProvider extends ServiceProvider
{
    /**
     * Application
     *
     * @var Application
     */
    protected Application $app;

    /**
     * Constructeur
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Enregistre les services dans le conteneur
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind('migrate', $this);
    }
    
    /**
     * Démarre le service après l'enregistrement
     *
     * @return void
     */
    public function boot(): void
    {
        // Rien à faire ici
    }
    
    /**
     * Exécute toutes les migrations
     *
     * @return void
     */
    public function run(): void
    {
        $db = $this->app->make('db')->getConnection();
        
        // Créer la table des migrations si elle n'existe pas
        $this->createMigrationsTable($db);
        
        // Récupérer les migrations déjà exécutées
        $executedMigrations = $this->getExecutedMigrations($db);
        
        // Trouver tous les fichiers de migration
        $migrationFiles = $this->getMigrationFiles();
        
        $count = 0;
        // Exécuter les migrations qui n'ont pas encore été exécutées
        foreach ($migrationFiles as $file) {
            $migrationName = pathinfo($file, PATHINFO_FILENAME);
            
            if (!in_array($migrationName, $executedMigrations)) {
                $this->runMigration($db, $file, $migrationName);
                $count++;
            }
        }
        
        if ($count === 0) {
            echo "Rien à migrer.\n";
        } else {
            echo "Migrations terminées avec succès.\n";
        }
    }

    /**
     * Annule les dernières migrations
     * 
     * @param int $steps Nombre de migrations à annuler
     * @return void
     */
    public function rollback(int $steps = 1): void
    {
        $db = $this->app->make('db')->getConnection();
        
        // Récupérer les dernières migrations exécutées
        $stmt = $db->prepare("SELECT migration FROM migrations ORDER BY id DESC LIMIT ?");
        $stmt->execute([$steps]);
        $migrations = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        if (empty($migrations)) {
            echo "Rien à annuler.\n";
            return;
        }
        
        foreach ($migrations as $migrationName) {
            $this->rollbackMigration($db, $migrationName);
        }
        
        echo "Rollback terminé avec succès.\n";
    }

    /**
     * Annule une migration spécifique
     * 
     * @param PDO $db
     * @param string $migrationName
     * @return void
     */
    private function rollbackMigration(PDO $db, string $migrationName): void
    {
        echo "Rollback: $migrationName... ";
        
        // Trouver le fichier correspondant
        $migrationsPath = dirname(__DIR__, 2) . '/database/migrations';
        $file = $migrationsPath . '/' . $migrationName . '.php';
        
        if (!file_exists($file)) {
            echo "Erreur: Fichier de migration introuvable ($file)\n";
            return;
        }
        
        try {
            // Inclure le fichier de migration
            $migration = require $file;
            
            if (!is_object($migration)) {
                $className = $this->extractClassName($file);
                if (class_exists($className)) {
                    $migration = new $className();
                } else {
                    echo "Erreur: Classe de migration non trouvée\n";
                    return;
                }
            }
            
            // Exécuter la méthode down()
            if (method_exists($migration, 'down')) {
                $migration->down();
                
                // Supprimer de la table des migrations
                $stmt = $db->prepare("DELETE FROM migrations WHERE migration = ?");
                $stmt->execute([$migrationName]);
                
                echo "Terminé ✓\n";
            } else {
                echo "Erreur: Méthode down() manquante\n";
            }
        } catch (\Exception $e) {
            echo "ERREUR: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Crée la table des migrations si elle n'existe pas
     *
     * @param PDO $db
     * @return void
     */
    private function createMigrationsTable(PDO $db): void
    {
        $db->exec("
            CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                migration VARCHAR(255) NOT NULL,
                executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
    }
    
    /**
     * Récupère la liste des migrations déjà exécutées
     *
     * @param PDO $db
     * @return array
     */
    private function getExecutedMigrations(PDO $db): array
    {
        $stmt = $db->query("SELECT migration FROM migrations");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    /**
     * Récupère tous les fichiers de migration
     *
     * @return array
     */
    private function getMigrationFiles(): array
    {
        $migrationsPath = dirname(__DIR__, 2) . '/database/migrations';
        $files = glob($migrationsPath . '/*.php');
        
        // Trier les fichiers de migration par nom (pour exécuter dans l'ordre)
        sort($files);
        
        return $files;
    }
    
    /**
     * Exécute une migration
     *
     * @param PDO $db
     * @param string $file
     * @param string $migrationName
     * @return void
     */
    private function runMigration(PDO $db, string $file, string $migrationName): void
    {
        echo "Migration: $migrationName... ";
        
        try {
            // Inclure le fichier de migration et récupérer l'objet retourné
            $migration = require $file;
            
            // Si le fichier ne retourne pas d'objet (ancienne méthode avec classe nommée)
            if (!is_object($migration)) {
                $className = $this->extractClassName($file);
                if (class_exists($className)) {
                    $migration = new $className();
                } else {
                    echo "Erreur: Fichier de migration invalide (doit retourner une classe anonyme ou contenir une classe nommée attendue)\n";
                    return;
                }
            }
            
            // Exécuter la méthode up() de la migration
            if (method_exists($migration, 'up')) {
                $migration->up();
                
                // Enregistrer la migration comme exécutée
                $stmt = $db->prepare("INSERT INTO migrations (migration) VALUES (?)");
                $stmt->execute([$migrationName]);
                
                echo "Terminé ✓\n";
            } else {
                echo "Erreur: Méthode up() manquante\n";
            }
        } catch (\Exception $e) {
            echo "ERREUR: " . $e->getMessage() . "\n";
        }
    }
    
    /**
     * Extrait le nom de la classe à partir du chemin du fichier
     *
     * @param string $file
     * @return string
     */
    private function extractClassName(string $file): string
    {
        // Lire le contenu du fichier
        $content = file_get_contents($file);
        
        // Utiliser une expression régulière pour extraire le nom de la classe
        if (preg_match('/class\s+([a-zA-Z0-9_]+)\s+extends/', $content, $matches)) {
            return $matches[1];
        }
        
        // Par défaut, essayer de déduire le nom de classe à partir du nom de fichier
        $baseName = pathinfo($file, PATHINFO_FILENAME);
        $parts = explode('_', $baseName);
        array_shift($parts); // Ignorer le timestamp au début
        
        $className = '';
        foreach ($parts as $part) {
            $className .= ucfirst($part);
        }
        
        // Convertir le nom de la table (pluriel) en nom de classe (singulier)
        if (substr($className, -1) === 's') {
            $className = substr($className, 0, -1);
        }
        
        return $className . 'Table';
    }
} 