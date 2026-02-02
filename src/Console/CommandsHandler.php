<?php

namespace Calvino\Console;

use Calvino\Core\Application;

/**
 * Gestionnaire de commandes
 */
class CommandsHandler
{
    /**
     * L'application
     *
     * @var Application
     */
    protected $app;
    
    /**
     * Les commandes disponibles
     *
     * @var array
     */
    protected $commands = [];
    
    /**
     * Constructeur
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->registerCommands();
    }
    
    /**
     * Enregistre toutes les commandes disponibles
     *
     * @return void
     */
    protected function registerCommands(): void
    {
        // Répertoire des commandes
        $commandsDir = __DIR__ . '/Commands';
        
        // Vérifie si le répertoire existe
        if (!is_dir($commandsDir)) {
            return;
        }
        
        // Parcourir les fichiers PHP dans le répertoire
        foreach (glob($commandsDir . '/*.php') as $file) {
            $className = 'Calvino\\Console\\Commands\\' . pathinfo($file, PATHINFO_FILENAME);
            
            if (class_exists($className)) {
                $command = new $className($this->app);
                $this->commands[$command->getName()] = $className;
            }
        }
    }
    
    /**
     * Exécute une commande
     *
     * @param string $command Nom de la commande
     * @param array $args Arguments
     * @return void
     */
    public function execute(string $command, array $args = []): void
    {
        // Vérifier si la commande existe
        if (isset($this->commands[$command])) {
            $commandClass = $this->commands[$command];
            $commandInstance = new $commandClass($this->app, $args);
            $commandInstance->handle();
            return;
        }
        
        // Commande non trouvée, affiche l'aide
        $this->executeHelp();
    }
    
    /**
     * Exécute la commande d'aide
     *
     * @return void
     */
    public function executeHelp(): void
    {
        $helpCommand = $this->commands['help'] ?? Commands\HelpCommand::class;
        $command = new $helpCommand($this->app);
        $command->handle();
    }
    
    /**
     * Retourne les commandes disponibles
     *
     * @return array
     */
    public function getCommands(): array
    {
        $commands = [];
        
        foreach ($this->commands as $name => $class) {
            $instance = new $class($this->app);
            $commands[$name] = [
                'name' => $name,
                'description' => $instance->getDescription(),
                'class' => $class
            ];
        }
        
        return $commands;
    }
} 