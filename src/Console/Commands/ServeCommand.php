<?php

namespace Calvino\Console\Commands;

use Calvino\Console\AnimatedCommand;

/**
 * Commande pour démarrer le serveur de développement
 */
class ServeCommand extends AnimatedCommand
{
    /**
     * Le nom de la commande
     *
     * @var string
     */
    protected string $name = 'serve';

    /**
     * La description de la commande
     *
     * @var string
     */
    protected string $description = 'Démarre le serveur de développement PHP';

    /**
     * Exécute la commande
     *
     * @return void
     */
    public function handle(): void
    {
        // Récupérer l'hôte et le port depuis les arguments ou utiliser les valeurs par défaut
        $host = $this->getArgument('host', '127.0.0.1');
        $port = $this->getArgument('port', '8000');
        
        // Déterminer le répertoire public
        $publicDir = $this->getPublicDirectory();
        
        if (!is_dir($publicDir)) {
            $this->error("Le répertoire public n'existe pas: {$publicDir}");
            return;
        }

        $this->info("╭─────────────────────────────────────────────────╮");
        $this->info("│                                                 │");
        $this->info("│   🚀 Serveur de développement Calvino           │");
        $this->info("│                                                 │");
        $this->info("╰─────────────────────────────────────────────────╯");
        $this->newLine();
        
        $this->success("✓ Serveur démarré avec succès!");
        $this->info("  URL locale:    http://{$host}:{$port}");
        $this->info("  Répertoire:    {$publicDir}");
        $this->newLine();
        $this->warning("  Appuyez sur Ctrl+C pour arrêter le serveur");
        $this->newLine();

        // Construire la commande
        $command = sprintf(
            'php -S %s:%s -t %s',
            escapeshellarg($host),
            escapeshellarg($port),
            escapeshellarg($publicDir)
        );

        // Démarrer le serveur
        passthru($command);
    }

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
     * Détermine le répertoire public
     *
     * @return string
     */
    protected function getPublicDirectory(): string
    {
        // Vérifier si nous sommes dans un projet avec un répertoire public
        $possibleDirs = [
            BASE_PATH . '/public',
            BASE_PATH . '/web',
            BASE_PATH
        ];

        foreach ($possibleDirs as $dir) {
            if (is_dir($dir) && file_exists($dir . '/index.php')) {
                return $dir;
            }
        }

        // Par défaut, utiliser le répertoire public
        return BASE_PATH . '/public';
    }

    /**
     * Récupère un argument de la commande
     *
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    protected function getArgument(string $name, $default = null)
    {
        // Chercher l'argument dans les arguments de la commande
        foreach ($this->args as $arg) {
            if (strpos($arg, "--{$name}=") === 0) {
                return substr($arg, strlen("--{$name}="));
            }
        }

        return $default;
    }
}
