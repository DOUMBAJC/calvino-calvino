<?php

namespace Calvino\Console\Commands;

use Calvino\Console\AnimatedCommand;
use Calvino\Providers\MigrationServiceProvider;

/**
 * Commande pour annuler les dernières migrations
 */
class MigrateRollbackCommand extends AnimatedCommand
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'migrate:rollback';
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'Annule les dernières migrations';
    }
    
    /**
     * {@inheritdoc}
     */
    public function handle(): void
    {
        try {
            // Afficher le titre
            $this->title("ANNULATION DE MIGRATIONS");
            
            $steps = isset($this->args[0]) ? (int)$this->args[0] : 1;
            
            // Animation de préparation
            
            // S'assurer que le service de base de données est enregistré
            $dbProvider = new \Calvino\Providers\DatabaseServiceProvider($this->app);
            $dbProvider->register();
            
            // Si le service migrate n'existe pas, on l'initialise
            if (!$this->app->has('migrate')) {
                $migrateProvider = new MigrationServiceProvider($this->app);
                $migrateProvider->register();
            }
            
            // Récupérer le service de migration
            $migrate = $this->app->make('migrate');
            
            // Animation
            $this->wait("Planification de l'annulation de $steps migration(s)...", 2);
            
            // Demander confirmation si steps > 1
            if ($steps > 1) {
                if (!$this->confirm("Voulez-vous vraiment annuler les $steps dernières migrations ?")) {
                    $this->error("Opération annulée.");
                    return;
                }
            }
            
            // Exécuter le rollback
            $migrate->rollback($steps);
            
            // Message de succès
            $this->success("Rollback effectué avec succès");
        } catch (\Exception $e) {
            // Message d'erreur
            $this->error("Erreur lors du rollback : " . $e->getMessage());
            exit(1);
        }
    }
}
