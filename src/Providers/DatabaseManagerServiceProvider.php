<?php

namespace Calvino\Providers;

use Calvino\Core\Application;
use Calvino\Core\DatabaseManager;

/**
 * Fournisseur de services pour le gestionnaire de base de données
 */
class DatabaseManagerServiceProvider extends ServiceProvider
{
    /**
     * Application
     *
     * @var Application
     */
    protected Application $app;

    /**
     * Constructeur
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    /**
     * Enregistre les services dans le conteneur
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind('db.manager', function ($app) {
            // Assurez-vous que la connexion à la base de données est disponible
            if (!$app->has('db')) {
                $dbProvider = new DatabaseServiceProvider($app);
                $dbProvider->register();
            }
            
            $connection = $app->make('db')->getConnection();
            return new DatabaseManager($connection);
        });
    }
    
    /**
     * Démarre le service après l'enregistrement
     *
     * @return void
     */
    public function boot(): void
    {
        // Rien à faire ici
    }
} 