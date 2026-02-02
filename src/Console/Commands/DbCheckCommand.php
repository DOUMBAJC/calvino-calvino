<?php

namespace Calvino\Console\Commands;

use Calvino\Console\AnimatedCommand;

/**
 * Commande pour vérifier l'intégrité de la base de données
 */
class DbCheckCommand extends AnimatedCommand
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'db:check';
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'Vérifie l\'intégrité de la BD';
    }
    
    /**
     * {@inheritdoc}
     */
    public function handle(): void
    {
        try {
            // Afficher un titre
            $this->title("VÉRIFICATION DE L'INTÉGRITÉ DE LA BASE DE DONNÉES");
            
            // Animation d'initialisation
            
            // Initialiser les services de base de données
            $dbManager = $this->initDatabaseServices();
            
            // Animation de vérification
            
            // Vérifier l'intégrité de la base de données
            $results = $dbManager->checkDatabaseIntegrity();
            
            if (isset($results['error'])) {
                $this->error("Erreur lors de la vérification: " . $results['error']);
                exit(1);
            }
            
            if (isset($results['table_check']) && !empty($results['table_check'])) {
                // Animation de préparation des résultats
                
                echo "\nRésultats de la vérification des tables:\n";
                
                // Convertir les résultats pour le tableau
                $checkData = [];
                foreach ($results['table_check'] as $check) {
                    $checkData[] = [
                        $check['Table'],
                        $check['Op'],
                        $check['Msg_type'],
                        $check['Msg_text']
                    ];
                }
                
                // Afficher dans un tableau formaté
                $this->table(['Table', 'Op', 'Type de message', 'Message'], $checkData);
            } else {
                $this->success("Aucun problème détecté dans la base de données");
            }
            
            // Message de fin
            $this->success("Vérification terminée");
            
        } catch (\Exception $e) {
            $this->error("Erreur: " . $e->getMessage());
            exit(1);
        }
    }
} 