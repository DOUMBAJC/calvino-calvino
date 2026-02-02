<?php

namespace Calvino\Console\Commands;

use Calvino\Console\AnimatedCommand;
use Calvino\Providers\MigrationServiceProvider;

/**
 * Commande pour exécuter les migrations
 */
class MigrateCommand extends AnimatedCommand
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'migrate';
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'Exécute les migrations';
    }
    
    /**
     * {@inheritdoc}
     */
    public function handle(): void
    {
        try {
            // Afficher le titre
            $this->title("MIGRATIONS DE BASE DE DONNÉES");
            
            // Animation de préparation
            
            // S'assurer que le service de base de données est enregistré
            $dbProvider = new \Calvino\Providers\DatabaseServiceProvider($this->app);
            $dbProvider->register();
            
            // Animation de connexion à la base de données
            
            // Si le service migrate n'existe pas, on l'initialise
            if (!$this->app->has('migrate')) {
                $migrateProvider = new MigrationServiceProvider($this->app);
                $migrateProvider->register();
            }
            
            // Animation sans affichage des détails de migrations
            
            // Récupérer le service de migration
            $migrate = $this->app->make('migrate');
            
            // Compte à rebours avant de commencer
            
            // Exécuter les migrations
            $migrate->run();
            
            // Message de succès
            $this->success("Migrations exécutées avec succès");
        } catch (\Exception $e) {
            // Message d'erreur
            $this->error("Erreur lors des migrations: " . $e->getMessage());
            exit(1);
        }
    }
} 