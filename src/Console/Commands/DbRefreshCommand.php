<?php

namespace Calvino\Console\Commands;

use Calvino\Console\AnimatedCommand;
use Calvino\Providers\MigrationServiceProvider;

/**
 * Commande pour rafraîchir la base de données
 */
class DbRefreshCommand extends AnimatedCommand
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'db:refresh';
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'Réinitialise toutes les tables';
    }
    
    /**
     * {@inheritdoc}
     */
    public function handle(): void
    {
        try {
            // Afficher un titre
            $this->title("RÉINITIALISATION DE LA BASE DE DONNÉES");

            // Demander confirmation
            if (!$this->confirm("\033[31mATTENTION: Cette action va supprimer toutes les données existantes.\033[0m\nVoulez-vous vraiment continuer?")) {
                $this->error("Opération annulée par l'utilisateur.");
                return;
            }
            
            // Animation de préparation
            
            // Initialiser les services de base de données
            $dbManager = $this->initDatabaseServices();
            
            // Animation de suppression
            
            // Si le service migrate n'existe pas, on l'initialise
            if (!$this->app->has('migrate')) {
                $migrateProvider = new MigrationServiceProvider($this->app);
                $migrateProvider->register();
            }
            
            $migrate = $this->app->make('migrate');
            
            // Rafraîchir la base de données
            if ($dbManager->refreshDatabase(function() use ($migrate) {
                // Animation pendant la migration
                $migrate->run();
            })) {
                $this->success("Base de données réinitialisée et migrations appliquées avec succès");
            } else {
                $this->error("Échec de la réinitialisation de la base de données");
                exit(1);
            }
        } catch (\Exception $e) {
            $this->error("Erreur: " . $e->getMessage());
            exit(1);
        }
    }
} 