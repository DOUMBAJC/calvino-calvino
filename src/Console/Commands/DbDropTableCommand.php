<?php

namespace Calvino\Console\Commands;

use Calvino\Console\AnimatedCommand;

/**
 * Commande pour supprimer une table
 */
class DbDropTableCommand extends AnimatedCommand
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'db:drop-table';
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'Supprime une table spécifique';
    }
    
    /**
     * {@inheritdoc}
     */
    public function handle(): void
    {
        try {
            // Afficher un titre
            $this->title("SUPPRESSION DE TABLE");
            
            // Vérifier les arguments
            if (empty($this->args[0])) {
                $this->error("Nom de la table requis.");
                echo "Usage: php calvino db:drop-table [nom_table]\n";
                exit(1);
            }
            
            $tableName = $this->args[0];
            
            // Animation d'initialisation
            
            // Initialiser les services de base de données
            $dbManager = $this->initDatabaseServices();
            
            // Vérifier si la table existe
            
            if (!$dbManager->tableExists($tableName)) {
                $this->error("La table '$tableName' n'existe pas.");
                exit(1);
            }
            
            // Demander confirmation
            if (!$this->confirm("\033[31mATTENTION: Cette action va supprimer définitivement la table '$tableName' et toutes ses données.\033[0m\nVoulez-vous vraiment continuer?")) {
                $this->error("Opération annulée par l'utilisateur.");
                return;
            }
            
            // Animation de suppression
            
            // Supprimer la table
            if ($dbManager->dropTable($tableName)) {
                $this->success("Table '$tableName' supprimée avec succès");
            } else {
                $this->error("Échec de la suppression de la table '$tableName'.");
                exit(1);
            }
            
        } catch (\Exception $e) {
            $this->error("Erreur: " . $e->getMessage());
            exit(1);
        }
    }
} 