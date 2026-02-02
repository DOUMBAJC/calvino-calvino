<?php

namespace Calvino\Console\Commands;

use Calvino\Console\AnimatedCommand;

/**
 * Commande pour importer un fichier SQL
 */
class DbImportCommand extends AnimatedCommand
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'db:import';
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'Importe un fichier SQL';
    }
    
    /**
     * {@inheritdoc}
     */
    public function handle(): void
    {
        try {
            // Afficher un titre
            $this->title("IMPORTATION DE FICHIER SQL");
            
            // Vérifier les arguments
            if (empty($this->args[0])) {
                $this->error("Chemin du fichier SQL requis.");
                echo "Usage: php calvino db:import [chemin_fichier]\n";
                exit(1);
            }
            
            $filePath = $this->args[0];
            
            // Vérifier si le fichier existe
            if (!file_exists($filePath)) {
                $this->error("Le fichier '$filePath' n'existe pas.");
                exit(1);
            }
            
            // Animation d'initialisation
            
            // Initialiser les services de base de données
            $dbManager = $this->initDatabaseServices();
            
            // Animation de vérification
            $fileSize = filesize($filePath) / 1024; // KB
            
            echo "Fichier SQL: $filePath\n";
            echo "Taille: " . round($fileSize, 2) . " KB\n\n";
            
            // Demander confirmation
            if (!$this->confirm("Voulez-vous importer ce fichier dans la base de données?")) {
                $this->error("Importation annulée par l'utilisateur.");
                return;
            }
            
            // Animation d'importation
            
            // Importer le fichier SQL
            if ($dbManager->importSQL($filePath)) {
                $this->success("Fichier SQL importé avec succès");
            } else {
                $this->error("Échec de l'importation du fichier SQL.");
                exit(1);
            }
            
        } catch (\Exception $e) {
            $this->error("Erreur: " . $e->getMessage());
            exit(1);
        }
    }
} 