<?php

namespace Calvino\Console\Commands;

use Calvino\Console\AnimatedCommand;

/**
 * Commande pour lister les tables de la base de données
 */
class DbTablesCommand extends AnimatedCommand
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'db:tables';
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'Liste toutes les tables';
    }
    
    /**
     * {@inheritdoc}
     */
    public function handle(): void
    {
        try {
            // Afficher un titre
            $this->title("LISTE DES TABLES");
            
            // Animation de chargement
            
            // Initialiser les services de base de données
            $dbManager = $this->initDatabaseServices();
            
            // Animation de recherche
            
            // Récupérer la liste des tables
            $tables = $dbManager->listTables();
            
            if (empty($tables)) {
                $this->error("Aucune table n'existe dans la base de données.");
                exit(0);
            }
            
            // Préparation des données pour le tableau
            $tableData = [];
            $totalRows = 0;
            
            
            // Préparer les données pour le tableau
            foreach ($tables as $index => $table) {
                // Animation de progression pour chaque table
                $this->progress($index + 1, count($tables), "Analyse de la table: $table");
                
                $rowCount = $dbManager->countRecords($table);
                $totalRows += $rowCount;
                $tableData[] = [$table, $rowCount];
                
                // Pause pour voir l'effet d'animation
                usleep(100000);
            }
            
            // Afficher le tableau
            echo "\n";
            $this->table(['Table', 'Nombre de lignes'], $tableData);
            
            // Message récapitulatif
            $this->success("Base de données analysée: " . count($tables) . " tables, $totalRows enregistrements au total");
            
        } catch (\Exception $e) {
            $this->error("Erreur: " . $e->getMessage());
            exit(1);
        }
    }
} 