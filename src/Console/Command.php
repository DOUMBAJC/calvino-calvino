<?php

namespace Calvino\Console;

use Calvino\Core\Application;

/**
 * Classe de base pour toutes les commandes console
 */
abstract class Command
{
    /**
     * L'application
     *
     * @var Application
     */
    protected $app;
    
    /**
     * Les arguments de la commande
     *
     * @var array
     */
    protected $args = [];
    
    /**
     * Constructeur
     *
     * @param Application $app
     * @param array $args
     */
    public function __construct(Application $app, array $args = [])
    {
        $this->app = $app;
        $this->args = $args;
    }
    
    /**
     * Exécute la commande
     *
     * @return void
     */
    abstract public function handle(): void;
    
    /**
     * Retourne le nom de la commande
     *
     * @return string
     */
    abstract public function getName(): string;
    
    /**
     * Retourne la description de la commande
     *
     * @return string
     */
    abstract public function getDescription(): string;
    
    /**
     * Demande une confirmation à l'utilisateur
     *
     * @param string $message Message à afficher
     * @return bool
     */
    protected function confirm(string $message): bool
    {
        echo $message . " (o/n): ";
        $handle = fopen("php://stdin", "r");
        $line = trim(fgets($handle));
        fclose($handle);
        
        return strtolower($line) === 'o' || 
               strtolower($line) === 'oui' || 
               strtolower($line) === 'y' || 
               strtolower($line) === 'yes';
    }
    
    /**
     * Initialise les services de base de données
     *
     * @return mixed
     */
    protected function initDatabaseServices()
    {
        // Enregistrer le service de base de données
        $dbProvider = new \Calvino\Providers\DatabaseServiceProvider($this->app);
        $dbProvider->register();
        
        // Enregistrer le gestionnaire de base de données
        $dbManagerProvider = new \Calvino\Providers\DatabaseManagerServiceProvider($this->app);
        $dbManagerProvider->register();
        
        return $this->app->resolve('db.manager');
    }
} 