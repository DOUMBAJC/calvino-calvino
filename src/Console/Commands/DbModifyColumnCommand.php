<?php

namespace Calvino\Console\Commands;

use Calvino\Console\AnimatedCommand;

/**
 * Commande pour modifier une colonne existante dans une table
 */
class DbModifyColumnCommand extends AnimatedCommand
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'db:modify-column';
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'Modifie une colonne existante dans une table';
    }
    
    /**
     * {@inheritdoc}
     */
    public function handle(): void
    {
        try {
            // Afficher un titre
            $this->title("MODIFICATION DE COLONNE");
            
            // Vérifier les arguments
            if (count($this->args) < 3) {
                $this->error("Arguments insuffisants.");
                echo "Usage: php calvino db:modify-column [table] [nom_colonne] [nouveau_type] [NULL|NOT NULL] [DEFAULT valeur] [AFTER colonne]\n";
                echo "Exemple: php calvino db:modify-column utilisateurs email VARCHAR(320) NOT NULL\n";
                echo "Exemple: php calvino db:modify-column produits prix DECIMAL(12,2) NOT NULL DEFAULT 0\n";
                exit(1);
            }
            
            $tableName = $this->args[0];
            $columnName = $this->args[1];
            $newType = $this->args[2];
            
            // Options supplémentaires
            $nullable = true;
            $default = null;
            $defaultSpecified = false;
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
                    $defaultSpecified = true;
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
            
            // Vérifier si la colonne existe
            $structure = $dbManager->getTableStructure($tableName);
            $columnExists = false;
            
            foreach ($structure as $column) {
                if (strcasecmp($column['Field'], $columnName) === 0) {
                    $columnExists = true;
                    break;
                }
            }
            
            if (!$columnExists) {
                $this->error("La colonne '$columnName' n'existe pas dans la table '$tableName'.");
                exit(1);
            }
            
            $this->success("Colonne '$columnName' trouvée. Prêt à la modifier.");
            
            // Construire la requête SQL
            $sql = "ALTER TABLE `{$tableName}` MODIFY COLUMN `{$columnName}` {$newType}";
            
            if (!$nullable) {
                $sql .= " NOT NULL";
            } else {
                $sql .= " NULL";
            }
            
            if ($defaultSpecified) {
                if ($default === null || strtoupper($default) === 'NULL') {
                    $sql .= " DEFAULT NULL";
                } else {
                    // Si la valeur par défaut est une chaîne, l'entourer de guillemets
                    if (!is_numeric($default) && strtoupper($default) !== 'CURRENT_TIMESTAMP') {
                        $default = "'{$default}'";
                    }
                    $sql .= " DEFAULT {$default}";
                }
            }
            
            if ($after !== null) {
                $sql .= " AFTER `{$after}`";
            }
            
            // Afficher la requête SQL
            echo "\n\033[36mRequête SQL à exécuter:\033[0m\n";
            echo "\033[33m" . $sql . "\033[0m\n\n";
            
            // Confirmation
            if (!$this->confirm("Êtes-vous sûr de vouloir modifier la colonne '$columnName' dans la table '$tableName'?")) {
                $this->error("Opération annulée par l'utilisateur.");
                return;
            }
            
            // Animation d'exécution
            
            // Exécuter la requête
            $result = $dbManager->executeQuery($sql);
            
            if ($result === false) {
                $this->error("Échec de la modification de la colonne.");
                exit(1);
            }
            
            $this->success("Colonne '$columnName' modifiée avec succès dans la table '$tableName'");
            
        } catch (\Exception $e) {
            $this->error("Erreur: " . $e->getMessage());
            exit(1);
        }
    }
} 