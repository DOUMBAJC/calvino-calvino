<?php

namespace Calvino\Console\Commands;

use Calvino\Console\AnimatedCommand;

/**
 * Commande pour supprimer une colonne d'une table
 */
class DbDropColumnCommand extends AnimatedCommand
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'db:drop-column';
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'Supprime une colonne d\'une table';
    }
    
    /**
     * {@inheritdoc}
     */
    public function handle(): void
    {
        try {
            // Afficher un titre
            $this->title("SUPPRESSION DE COLONNE");
            
            // Vérifier les arguments
            if (count($this->args) < 2) {
                $this->error("Arguments insuffisants.");
                echo "Usage: php calvino db:drop-column [table] [nom_colonne]\n";
                echo "Exemple: php calvino db:drop-column utilisateurs ancien_champ\n";
                exit(1);
            }
            
            $tableName = $this->args[0];
            $columnName = $this->args[1];
            
            // Animation d'initialisation
            
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
            
            $this->success("Colonne '$columnName' trouvée.");
            
            // Construire la requête SQL
            $sql = "ALTER TABLE `{$tableName}` DROP COLUMN `{$columnName}`";
            
            // Afficher la requête SQL et avertissement
            echo "\n\033[31mATTENTION: Vous êtes sur le point de supprimer la colonne '$columnName' de la table '$tableName'.\033[0m\n";
            echo "\033[31mCette action est irréversible et toutes les données de cette colonne seront perdues.\033[0m\n\n";
            echo "\033[36mRequête SQL qui sera exécutée:\033[0m\n";
            echo "\033[33m" . $sql . "\033[0m\n\n";
            
            // Confirmation
            if (!$this->confirm("Êtes-vous absolument sûr de vouloir supprimer cette colonne?")) {
                $this->error("Opération annulée par l'utilisateur.");
                return;
            }
            
            // Confirmation supplémentaire
            if (!$this->confirm("\033[31mDernière chance\033[0m - confirmez que vous souhaitez supprimer définitivement la colonne '$columnName'?")) {
                $this->error("Opération annulée par l'utilisateur.");
                return;
            }
            
            // Animation de suppression
            
            // Exécuter la requête
            $result = $dbManager->executeQuery($sql);
            
            if ($result === false) {
                $this->error("Échec de la suppression de la colonne.");
                exit(1);
            }
            
            $this->success("Colonne '$columnName' supprimée avec succès de la table '$tableName'");
            
        } catch (\Exception $e) {
            $this->error("Erreur: " . $e->getMessage());
            exit(1);
        }
    }
} 