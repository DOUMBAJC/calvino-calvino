<?php

namespace Calvino\Console\Commands;

use Calvino\Console\AnimatedCommand;

/**
 * Commande pour exporter une table ou toute la base de données en fichier SQL
 */
class DbExportCommand extends AnimatedCommand
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'db:export';
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'Exporte en fichier SQL';
    }
    
    /**
     * {@inheritdoc}
     */
    public function handle(): void
    {
        try {
            // Afficher un titre
            $this->title("EXPORTATION SQL");
            
            // Vérifier les arguments
            $table = $this->args[0] ?? null;
            $filePath = $this->args[1] ?? BASE_PATH . '/database/exports/db_export_' . date('Y-m-d_H-i-s') . '.sql';
            $includeData = isset($this->args[2]) && ($this->args[2] === 'data' || $this->args[2] === 'true');
            
            // Animation d'initialisation
            
            // Créer le répertoire d'export s'il n'existe pas
            $exportDir = dirname($filePath);
            if (!is_dir($exportDir)) {
                mkdir($exportDir, 0755, true);
                $this->success("Répertoire créé: $exportDir");
            }
            
            // Initialiser les services de base de données
            $dbManager = $this->initDatabaseServices();
            
            // Message d'information
            if ($table) {
                echo "\033[34mExport de la table '$table'" . ($includeData ? " avec données" : " sans données") . "\033[0m\n";
            } else {
                echo "\033[34mExport de toutes les tables" . ($includeData ? " avec données" : " sans données") . "\033[0m\n";
            }
            
            // Demander confirmation
            if (!$this->confirm("Voulez-vous continuer avec l'exportation?")) {
                $this->error("Exportation annulée par l'utilisateur.");
                return;
            }
            
            // Animation d'exportation
            
            // Exporter
            if ($dbManager->exportTable($table, $filePath, $includeData)) {
                $this->success("Export terminé avec succès");
                echo "Fichier: $filePath\n";
            } else {
                $this->error("Échec de l'exportation");
                exit(1);
            }
        } catch (\Exception $e) {
            $this->error("Erreur: " . $e->getMessage());
            exit(1);
        }
    }
} 