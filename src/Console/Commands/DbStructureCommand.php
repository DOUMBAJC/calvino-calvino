<?php

namespace Calvino\Console\Commands;

use Calvino\Console\AnimatedCommand;

/**
 * Commande pour afficher la structure d'une table
 */
class DbStructureCommand extends AnimatedCommand
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'db:structure';
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'Affiche la structure d\'une table';
    }
    
    /**
     * {@inheritdoc}
     */
    public function handle(): void
    {
        try {
            // Afficher un titre
            $this->title("STRUCTURE DE TABLE");
            
            // Vérifier les arguments
            if (empty($this->args[0])) {
                $this->error("Nom de la table requis.");
                echo "Usage: php calvino db:structure [nom_table]\n";
                exit(1);
            }
            
            $tableName = $this->args[0];
            
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
            
            // Animation de récupération de la structure
            
            // Récupérer la structure
            $structure = $dbManager->getTableStructure($tableName);
            
            if (empty($structure)) {
                $this->error("Aucune information de structure disponible pour la table '$tableName'.");
                exit(1);
            }
            
            echo "\n";
            
            // Préparer les données pour la méthode table
            $headers = ['Field', 'Type', 'Null', 'Key', 'Default', 'Extra'];
            $data = [];
            
            foreach ($structure as $column) {
                $data[] = [
                    $column['Field'],
                    $column['Type'],
                    $column['Null'],
                    $column['Key'],
                    $column['Default'] ?? 'NULL',
                    $column['Extra']
                ];
            }
            
            // Afficher la structure sous forme de tableau
            $this->table($headers, $data);
            
            // Animation de comptage
            
            // Afficher le nombre d'enregistrements
            $count = $dbManager->countRecords($tableName);
            $this->success("Nombre d'enregistrements: $count");
            
        } catch (\Exception $e) {
            $this->error("Erreur: " . $e->getMessage());
            exit(1);
        }
    }
} 