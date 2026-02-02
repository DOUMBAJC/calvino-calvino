<?php

namespace Calvino\Core;

use PDO;

/**
 * Gestionnaire de base de données
 */
class DatabaseManager
{
    /**
     * Instance de PDO
     *
     * @var PDO
     */
    protected PDO $connection;

    /**
     * Constructeur
     *
     * @param PDO $connection
     */
    public function __construct(PDO $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Crée la base de données
     *
     * @param string $database Nom de la base de données
     * @param string $charset Jeu de caractères
     * @return bool
     */
    public function createDatabase(string $database, string $charset = 'utf8mb4'): bool
    {
        try {
            $this->connection->exec("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET {$charset} COLLATE {$charset}_unicode_ci");
            return true;
        } catch (\PDOException $e) {
            echo "Erreur lors de la création de la base de données: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Supprime une table spécifique
     *
     * @param string $table Nom de la table
     * @return bool
     */
    public function dropTable(string $table): bool
    {
        try {
            $this->connection->exec("DROP TABLE IF EXISTS `{$table}`");
            return true;
        } catch (\PDOException $e) {
            // Vérifier si l'erreur est liée à une contrainte de clé étrangère
            if (strpos($e->getMessage(), 'foreign key constraint fails') !== false) {
                try {
                    // Désactiver temporairement les contraintes
                    $this->disableForeignKeyChecks();
                    
                    // Réessayer la suppression
                    $this->connection->exec("DROP TABLE IF EXISTS `{$table}`");
                    
                    // Réactiver les contraintes
                    $this->enableForeignKeyChecks();
                    
                    return true;
                } catch (\PDOException $e2) {
                    // Réactiver les contraintes même en cas d'échec
                    $this->enableForeignKeyChecks();
                    
                    echo "Erreur lors de la suppression de la table '{$table}' (tentative avec contraintes désactivées): " . $e2->getMessage() . "\n";
                    return false;
                }
            }
            
            echo "Erreur lors de la suppression de la table '{$table}': " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Vérifie si une table existe
     *
     * @param string $table Nom de la table
     * @return bool
     */
    public function tableExists(string $table): bool
    {
        try {
            $stmt = $this->connection->query("SHOW TABLES LIKE '{$table}'");
            return $stmt->rowCount() > 0;
        } catch (\PDOException $e) {
            return false;
        }
    }

    /**
     * Liste toutes les tables de la base de données
     *
     * @return array
     */
    public function listTables(): array
    {
        try {
            $stmt = $this->connection->query("SHOW TABLES");
            return $stmt->fetchAll(PDO::FETCH_COLUMN);
        } catch (\PDOException $e) {
            echo "Erreur lors de la récupération des tables: " . $e->getMessage() . "\n";
            return [];
        }
    }

    /**
     * Rafraîchit la base de données (supprime et recrée toutes les tables via migrations)
     *
     * @param callable $migrationCallback Fonction à appeler pour exécuter les migrations
     * @return bool
     */
    public function refreshDatabase(callable $migrationCallback): bool
    {
        try {
            // Sauvegarde la table des migrations pour la recréer après
            $hasMigrationsTable = $this->tableExists('migrations');
            
            // Désactiver les contraintes de clés étrangères avant de supprimer les tables
            $this->disableForeignKeyChecks();
            
            // Supprimer toutes les tables sauf migrations
            $tables = $this->listTables();
            
            foreach ($tables as $table) {
                if ($table !== 'migrations') {
                    $this->dropTable($table);
                }
            }
            
            // Vider la table des migrations
            if ($hasMigrationsTable) {
                $this->connection->exec("TRUNCATE TABLE migrations");
            }
            
            // Exécuter les migrations
            $migrationCallback();
            
            // Réactiver les contraintes de clés étrangères après avoir recréé les tables
            $this->enableForeignKeyChecks();
            
            return true;
        } catch (\PDOException $e) {
            // Assurer que les contraintes sont réactivées même en cas d'erreur
            $this->enableForeignKeyChecks();
            
            echo "Erreur lors du rafraîchissement de la base de données: " . $e->getMessage() . "\n";
            return false;
        }
    }

    /**
     * Tronque une table (supprime toutes les données mais conserve la structure)
     *
     * @param string $table Nom de la table
     * @return bool
     */
    public function truncateTable(string $table): bool
    {
        try {
            $this->connection->exec("TRUNCATE TABLE `{$table}`");
            return true;
        } catch (\PDOException $e) {
            echo "Erreur lors du vidage de la table '{$table}': " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Exporte la structure d'une table (ou toutes les tables) dans un fichier SQL
     *
     * @param string|null $table Nom de la table à exporter (null pour toutes les tables)
     * @param string $filePath Chemin du fichier où exporter
     * @param bool $includeData Si true, inclut les données, sinon uniquement la structure
     * @return bool
     */
    public function exportTable(?string $table = null, string $filePath = 'database_export.sql', bool $includeData = false): bool
    {
        try {
            $tables = $table ? [$table] : $this->listTables();
            $output = "-- Export SQL généré le " . date('Y-m-d H:i:s') . "\n\n";
            
            if (empty($tables)) {
                echo "Aucune table à exporter.\n";
                return false;
            }
            
            // Désactiver les contraintes de clé étrangère pour l'import ultérieur
            $output .= "SET FOREIGN_KEY_CHECKS=0;\n\n";
            
            foreach ($tables as $tableName) {
                // Structure de la table
                $stmt = $this->connection->query("SHOW CREATE TABLE `{$tableName}`");
                $createTable = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (isset($createTable['Create Table'])) {
                    $output .= "-- Structure de la table `{$tableName}`\n";
                    $output .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                    $output .= $createTable['Create Table'] . ";\n\n";
                    
                    // Données de la table si demandé
                    if ($includeData) {
                        $rows = $this->connection->query("SELECT * FROM `{$tableName}`")->fetchAll(PDO::FETCH_ASSOC);
                        
                        if (count($rows) > 0) {
                            $output .= "-- Données de la table `{$tableName}`\n";
                            $output .= "INSERT INTO `{$tableName}` VALUES\n";
                            
                            $rowValues = [];
                            foreach ($rows as $row) {
                                $values = array_map(function ($value) {
                                    if ($value === null) {
                                        return 'NULL';
                                    }
                                    return $this->connection->quote($value);
                                }, array_values($row));
                                
                                $rowValues[] = "(" . implode(", ", $values) . ")";
                            }
                            
                            $output .= implode(",\n", $rowValues) . ";\n\n";
                        }
                    }
                }
            }
            
            // Réactiver les contraintes de clé étrangère
            $output .= "SET FOREIGN_KEY_CHECKS=1;\n";
            
            // Écrire dans le fichier
            if (file_put_contents($filePath, $output) !== false) {
                echo "Export effectué avec succès dans le fichier '{$filePath}'.\n";
                return true;
            } else {
                echo "Erreur lors de l'écriture du fichier '{$filePath}'.\n";
                return false;
            }
        } catch (\PDOException $e) {
            echo "Erreur lors de l'export: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Importe une base de données à partir d'un fichier SQL
     *
     * @param string $filePath Chemin du fichier SQL à importer
     * @return bool
     */
    public function importDatabase(string $filePath): bool
    {
        try {
            if (!file_exists($filePath)) {
                echo "Le fichier '{$filePath}' n'existe pas.\n";
                return false;
            }
            
            $sql = file_get_contents($filePath);
            if ($sql === false) {
                echo "Impossible de lire le fichier '{$filePath}'.\n";
                return false;
            }
            
            // Désactiver les contraintes de clés étrangères
            $this->disableForeignKeyChecks();
            
            try {
                // Exécuter les requêtes SQL
                $this->connection->exec($sql);
                
                // Réactiver les contraintes de clés étrangères
                $this->enableForeignKeyChecks();
                
                return true;
            } catch (\PDOException $e) {
                // S'assurer que les contraintes sont réactivées même en cas d'erreur
                $this->enableForeignKeyChecks();
                
                echo "Erreur lors de l'exécution du script SQL: " . $e->getMessage() . "\n";
                return false;
            }
        } catch (\PDOException $e) {
            echo "Erreur lors de l'import: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Alias pour importDatabase - Importe un fichier SQL
     *
     * @param string $filePath Chemin du fichier SQL à importer
     * @return bool
     */
    public function importSQL(string $filePath): bool
    {
        return $this->importDatabase($filePath);
    }
    
    /**
     * Exécute une requête SQL avec des paramètres
     * 
     * @param string $sql Requête SQL
     * @param array $params Paramètres de la requête
     * @return array|bool Résultats de la requête ou statut d'exécution
     */
    public function executeQuery(string $sql, array $params = []): array|bool
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            
            // Si c'est une requête SELECT, récupérer les résultats
            if (stripos(trim($sql), 'SELECT') === 0) {
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            }
            
            // Pour les autres requêtes (INSERT, UPDATE, DELETE), retourner true
            return true;
        } catch (\PDOException $e) {
            // Ne pas afficher l'erreur directement, la lancer pour qu'elle soit gérée par le contrôleur
            throw new \PDOException($e->getMessage(), $e->getCode());
        }
    }
    
    /**
     * Désactive les contraintes de clés étrangères
     * 
     * @return bool
     */
    public function disableForeignKeyChecks(): bool
    {
        try {
            $this->connection->exec("SET FOREIGN_KEY_CHECKS=0;");
            return true;
        } catch (\PDOException $e) {
            echo "Erreur lors de la désactivation des contraintes: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Active les contraintes de clés étrangères
     * 
     * @return bool
     */
    public function enableForeignKeyChecks(): bool
    {
        try {
            $this->connection->exec("SET FOREIGN_KEY_CHECKS=1;");
            return true;
        } catch (\PDOException $e) {
            echo "Erreur lors de l'activation des contraintes: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Obtient la structure d'une table (colonnes, types, etc.)
     * 
     * @param string $table Nom de la table
     * @return array Structure de la table
     */
    public function getTableStructure(string $table): array
    {
        try {
            $stmt = $this->connection->query("DESCRIBE `{$table}`");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            echo "Erreur lors de la récupération de la structure: " . $e->getMessage() . "\n";
            return [];
        }
    }
    
    /**
     * Compte le nombre d'enregistrements dans une table
     * 
     * @param string $table Nom de la table
     * @return int Nombre d'enregistrements
     */
    public function countRecords(string $table): int
    {
        try {
            $stmt = $this->connection->query("SELECT COUNT(*) FROM `{$table}`");
            return (int) $stmt->fetchColumn();
        } catch (\PDOException $e) {
            echo "Erreur lors du comptage des enregistrements: " . $e->getMessage() . "\n";
            return 0;
        }
    }
    
    /**
     * Vérifie l'intégrité de la base de données
     * 
     * @return array Résultats de la vérification
     */
    public function checkDatabaseIntegrity(): array
    {
        $results = [];
        
        try {
            // Vérifier les tables InnoDB
            $stmt = $this->connection->query("CHECK TABLE " . implode(', ', $this->listTables()));
            $results['table_check'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            return $results;
        } catch (\PDOException $e) {
            echo "Erreur lors de la vérification de l'intégrité: " . $e->getMessage() . "\n";
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Crée un fichier de migration pour un modèle donné
     * 
     * @param string $modelName Nom du modèle
     * @return bool
     */
    public function createMigrationFile(string $modelName): bool
    {
        try {
            // Normaliser le nom du modèle
            $modelName = ucfirst($modelName);
            
            // Générer un nom de table au format snake_case et pluriel
            $tableName = $this->toSnakeCase($modelName) . 's';
            
            // Créer le nom de la classe de migration
            $migrationClassName = 'Create' . $modelName . 'Table';
            
            // Générer un timestamp pour le préfixe du fichier
            $timestamp = date('YmdHis');
            
            // Chemin du fichier de migration
            $migrationPath = BASE_PATH . '/database/migrations/' . $timestamp . '_create_' . strtolower($tableName) . '.php';
            
            // Vérifier si le répertoire existe
            $migrationsDir = BASE_PATH . '/database/migrations';
            if (!is_dir($migrationsDir)) {
                if (!mkdir($migrationsDir, 0755, true)) {
                    echo "Impossible de créer le répertoire de migrations.\n";
                    return false;
                }
            }
            
            // Créer le contenu du fichier de migration
            $content = $this->generateMigrationContent($migrationClassName, $tableName, $modelName);
            
            // Écrire le fichier de migration
            if (file_put_contents($migrationPath, $content) === false) {
                echo "Erreur lors de l'écriture du fichier de migration.\n";
                return false;
            }
            
            echo "Fichier de migration créé : " . $migrationPath . "\n";
            return true;
        } catch (\Exception $e) {
            echo "Erreur lors de la création du fichier de migration: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Génère le contenu du fichier de migration
     * 
     * @param string $className Nom de la classe de migration
     * @param string $tableName Nom de la table
     * @param string $modelName Nom du modèle
     * @return string
     */
    private function generateMigrationContent(string $className, string $tableName, string $modelName): string
    {
        return "<?php

/**
 * Migration pour la table {$tableName}
 */
class {$className} extends \\Calvino\\Core\\Migration
{
    /**
     * Exécute la migration
     *
     * @return void
     */
    public function up(): void
    {
        \$this->create('{$tableName}', function (\\Calvino\\Core\\Schema \$table) {
            \$table->id();
            
            // Ajoutez d'autres colonnes ici selon vos besoins

            \$table->timestamps();
            
        });
    }

    /**
     * Annule la migration
     *
     * @return void
     */
    public function down(): void
    {
        \$this->drop('{$tableName}');
    }
}";
    }
    
    /**
     * Convertit une chaîne en snake_case
     * 
     * @param string $input Chaîne à convertir
     * @return string
     */
    private function toSnakeCase(string $input): string
    {
        // Convertir les caractères majuscules en minuscules précédés par un underscore
        $output = preg_replace('/(?<!^)[A-Z]/', '_$0', $input);
        // Convertir en minuscules
        return strtolower($output);
    }
} 