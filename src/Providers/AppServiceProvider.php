<?php

namespace Calvino\Providers;

/**
 * Fournisseur de services de l'application
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Enregistre les services de l'application
     *
     * @return void
     */
    public function register(): void
    {
        // Enregistrer les services globaux ici
    }

    /**
     * Démarre les services de l'application
     *
     * @return void
     */
    public function boot(): void
    {
        // Code d'initialisation au démarrage de l'application
    }
}
