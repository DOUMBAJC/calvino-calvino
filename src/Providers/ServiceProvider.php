<?php

namespace Calvino\Providers;

use Calvino\Core\Application;

/**
 * Classe ServiceProvider
 * Classe de base pour tous les fournisseurs de services
 */
abstract class ServiceProvider
{
    /**
     * Instance de l'application
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
    abstract public function register(): void;
    
    /**
     * Démarre les services après l'enregistrement
     * Cette méthode est optionnelle
     *
     * @return void
     */
    public function boot(): void
    {
        // Cette méthode peut être surchargée par les classes filles
    }
} 