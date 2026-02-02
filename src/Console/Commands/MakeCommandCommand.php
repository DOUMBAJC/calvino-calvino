<?php

namespace Calvino\Console\Commands;

use Calvino\Console\AnimatedCommand;

/**
 * Commande pour créer une nouvelle commande
 */
class MakeCommandCommand extends AnimatedCommand
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'make:command';
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'Crée une nouvelle commande personnalisée';
    }
    
    /**
     * {@inheritdoc}
     */
    public function handle(): void
    {
        try {
            // Afficher un titre
            $this->title("CRÉATION DE COMMANDE");
            
            // Vérifier les arguments
            if (empty($this->args[0])) {
                $this->error("Nom de la commande requis.");
                echo "Usage: php calvino make:command [NomCommande]\n";
                exit(1);
            }
            
            $commandName = $this->args[0];
            
            // Animation de préparation
            
            // Créer la commande
            $this->createCommand($commandName);
            
        } catch (\Exception $e) {
            $this->error("Erreur: " . $e->getMessage());
            exit(1);
        }
    }
    
    /**
     * Crée un fichier de commande
     *
     * @param string $commandName
     * @return bool
     */
    protected function createCommand(string $commandName): bool
    {
        // S'assurer que le nom commence par une majuscule et finit par Command
        $commandName = ucfirst($commandName);
        if (!str_ends_with($commandName, 'Command')) {
            $commandName .= 'Command';
        }
        
        // Animation de vérification
        
        // Définir le chemin du fichier - Dans src/Console/Commands car c'est là que le framework cherche
        $commandsPath = __DIR__;
        $filePath = $commandsPath . '/' . $commandName . '.php';
        
        // Vérifier si le fichier existe déjà
        if (file_exists($filePath)) {
            $this->error("La commande '{$commandName}' existe déjà.");
            return false;
        }
        
        // Créer le contenu du fichier
        
        // Déterminer le nom de la commande CLI (kebab-case)
        $cliName = strtolower(preg_replace('/(?<!^)[A-Z]/', ':$0', str_replace('Command', '', $commandName)));
        $cliName = str_replace('::', ':', $cliName); // Correction au cas où
        
        $commandContent = $this->getCommandTemplate($commandName, $cliName);
        
        // Animation d'écriture
        
        // Écrire dans le fichier
        if (file_put_contents($filePath, $commandContent) !== false) {
            $this->success("Commande '{$commandName}' créée avec succès");
            echo "Fichier créé: " . $filePath . "\n\n";
            return true;
        }
        
        $this->error("Erreur lors de la création du fichier commande.");
        return false;
    }
    
    /**
     * Retourne le template pour une commande
     *
     * @param string $className
     * @param string $commandName
     * @return string
     */
    protected function getCommandTemplate(string $className, string $commandName): string
    {
        return <<<PHP
<?php

namespace Calvino\Console\Commands;

use Calvino\Console\AnimatedCommand;

/**
 * Commande {$className}
 */
class {$className} extends AnimatedCommand
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return '{$commandName}';
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'Description de la commande {$commandName}';
    }
    
    /**
     * {@inheritdoc}
     */
    public function handle(): void
    {
        // Afficher un titre
        \$this->title("TITRE DE LA COMMANDE");
        
        // Votre logique ici
        \$this->info("Exécution de la commande {$commandName}...");
        
        // Exemple d'animation
        
        \$this->success("Commande terminée avec succès!");
    }
}
PHP;
    }
}
