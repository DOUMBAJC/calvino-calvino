<?php

namespace Calvino\Console\Commands;

use Calvino\Console\AnimatedCommand;
use Calvino\Core\Application;

/**
 * Commande pour exécuter les seeders
 */
class SeedCommand extends AnimatedCommand
{
    /**
     * Nom de la commande
     *
     * @var string
     */
    protected string $name = 'db:seed';
    
    /**
     * Description de la commande
     *
     * @var string
     */
    protected string $description = 'Remplit la base de données avec des données de test';
    
    /**
     * Arguments de la commande
     *
     * @var array
     */
    protected array $arguments = [
        ['class', 'optional', 'Classe du seeder à exécuter (par défaut: DatabaseSeeder)']
    ];
    
    /**
     * Options de la commande
     *
     * @var array
     */
    protected array $options = [
        ['--force', '-f', 'Forcer l\'exécution en production']
    ];
    
    /**
     * Retourne le nom de la commande
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
    
    /**
     * Retourne la description de la commande
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }
    
    /**
     * Vérifie si une option est présente
     *
     * @param string $name
     * @return bool
     */
    protected function hasOption(string $name): bool
    {
        $longOption = '--' . $name;
        $shortOption = '-' . substr($name, 0, 1);
        
        foreach ($this->args as $arg) {
            if ($arg === $longOption || $arg === $shortOption) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Récupère la valeur d'un argument
     *
     * @param string $name
     * @return string|null
     */
    protected function argument(string $name): ?string
    {
        // Trouver l'index de l'argument dans la définition
        $index = null;
        foreach ($this->arguments as $i => $arg) {
            if ($arg[0] === $name) {
                $index = $i;
                break;
            }
        }
        
        if ($index === null) {
            return null;
        }
        
        // Récupérer la valeur de l'argument dans les args
        $nonOptionArgs = array_filter($this->args, function($arg) {
            return substr($arg, 0, 1) !== '-';
        });
        
        return $nonOptionArgs[$index] ?? null;
    }
    
    /**
     * Exécute la commande
     *
     * @return void
     */
    public function handle(): void
    {
        // Afficher un titre
        $this->title("REMPLISSAGE DE LA BASE DE DONNÉES");
        
        // Vérifier si on est en production et si --force n'est pas défini
        if (env('APP_ENV') === 'production' && !$this->hasOption('force') && !$this->hasOption('f')) {
            $this->error('Cette commande ne peut pas être exécutée en production sans l\'option --force.');
            return;
        }
        
        // Animation de chargement des fichiers
        
        // Charger tous les fichiers de seeders
        $seederFiles = glob(BASE_PATH . '/database/seeders/*.php');
        
        // Afficher les seeders trouvés
        $seedersData = [];
        foreach ($seederFiles as $index => $file) {
            $seedersData[] = [($index + 1), basename($file)];
        }
        
        if (!empty($seedersData)) {
            $this->table(['#', 'Fichier Seeder'], $seedersData);
        } else {
            $this->error("Aucun fichier seeder trouvé.");
            return;
        }
        
        foreach ($seederFiles as $file) {
            require_once $file;
        }
        
        // Déterminer la classe de seeder à exécuter avec animation
        
        $class = $this->argument('class') ?? 'Database\\Seeders\\DatabaseSeeder';
        
        if (!class_exists($class)) {
            $this->error("La classe {$class} n'existe pas.");
            return;
        }
        
        // Compte à rebours avant de commencer
        
        try {
            $seeder = new $class();
            
            // Exécuter le seeder avec animation
            
            $seeder->run();
            
            // Message de succès
            $this->success('Seeding terminé avec succès');
        } catch (\Exception $e) {
            $this->error('Erreur lors du seeding: ' . $e->getMessage());
            echo $e->getTraceAsString() . "\n";
        }
    }
}
