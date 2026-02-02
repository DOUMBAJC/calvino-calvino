<?php

namespace Calvino\Console\Commands;

use Calvino\Console\AnimatedCommand;

/**
 * Commande pour ajouter une colonne à une table existante
 */
class DbAddColumnCommand extends AnimatedCommand
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'db:add-column';
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'Ajoute une colonne à une table existante';
    }
    
    /**
     * {@inheritdoc}
     */
    public function handle(): void
    {
        try {
            // Afficher un titre
            $this->title("AJOUT DE COLONNE");
            
            // Vérifier les arguments
            if (count($this->args) < 3) {
                $this->error("Arguments insuffisants.");
                echo "Usage: php calvino db:add-column [table] [nom_colonne] [type] [NULL|NOT NULL] [DEFAULT valeur] [AFTER colonne]\n";
                echo "Exemple: php calvino db:add-column utilisateurs email VARCHAR(255) NOT NULL\n";
                echo "Exemple: php calvino db:add-column produits prix DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER nom\n";
                exit(1);
            }
            
            $tableName = $this->args[0];
            $columnName = $this->args[1];
            $columnType = $this->args[2];
            
            // Options supplémentaires
            $nullable = true;
            $default = null;
            $after = null;
            
            // Animation d'initialisation
            
            // Parcourir les arguments restants pour des options
            for ($i = 3; $i < count($this->args); $i++) {
                $arg = strtoupper($this->args[$i]);
                
                if ($arg === 'NULL') {
                    $nullable = true;
                } elseif ($arg === 'NOT NULL') {
                    $nullable = false;
                } elseif ($arg === 'DEFAULT' && isset($this->args[$i + 1])) {
                    $default = $this->args[$i + 1];
                    $i++; // Sauter le prochain argument qui est la valeur par défaut
                } elseif ($arg === 'AFTER' && isset($this->args[$i + 1])) {
                    $after = $this->args[$i + 1];
                    $i++; // Sauter le prochain argument qui est le nom de la colonne
                }
            }
            
            // Animation d'initialisation de la base de données
            
            // Initialiser les services de base de données
            $dbManager = $this->initDatabaseServices();
            
            // Animation de vérification de la table
            
            // Vérifier si la table existe
            if (!$dbManager->tableExists($tableName)) {
                $this->error("La table '$tableName' n'existe pas.");
                exit(1);
            }
            
            $this->success("Table '$tableName' trouvée");
            
            // Animation de vérification de la colonne
            
            // Vérifier si la colonne existe déjà
            $structure = $dbManager->getTableStructure($tableName);
            foreach ($structure as $column) {
                if (strcasecmp($column['Field'], $columnName) === 0) {
                    $this->error("La colonne '$columnName' existe déjà dans la table '$tableName'.");
                    exit(1);
                }
            }
            
            $this->success("La colonne '$columnName' n'existe pas encore. Prêt à l'ajouter.");
            
            // Construire la requête SQL
            $sql = "ALTER TABLE `{$tableName}` ADD COLUMN `{$columnName}` {$columnType}";
            
            if (!$nullable) {
                $sql .= " NOT NULL";
            }
            
            if ($default !== null) {
                // Si la valeur par défaut est une chaîne, l'entourer de guillemets
                if (!is_numeric($default) && strtoupper($default) !== 'NULL' && 
                    strtoupper($default) !== 'CURRENT_TIMESTAMP') {
                    $default = "'{$default}'";
                }
                $sql .= " DEFAULT {$default}";
            }
            
            if ($after !== null) {
                $sql .= " AFTER `{$after}`";
            }
            
            // Afficher la requête SQL
            echo "\n\033[36mRequête SQL à exécuter:\033[0m\n";
            echo "\033[33m" . $sql . "\033[0m\n\n";
            
            // Confirmation
            if (!$this->confirm("Êtes-vous sûr de vouloir ajouter cette colonne à la table '$tableName'?")) {
                $this->error("Opération annulée par l'utilisateur.");
                return;
            }
            
            // Animation d'exécution
            
            // Exécuter la requête
            $result = $dbManager->executeQuery($sql);
            
            if ($result === false) {
                $this->error("Échec de l'ajout de la colonne.");
                exit(1);
            }
            
            $this->success("Colonne '$columnName' ajoutée avec succès à la table '$tableName'");
            
        } catch (\Exception $e) {
            $this->error("Erreur: " . $e->getMessage());
            exit(1);
        }
    }
} 