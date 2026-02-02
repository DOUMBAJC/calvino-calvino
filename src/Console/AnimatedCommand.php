<?php

namespace Calvino\Console;

use Calvino\Core\Application;

/**
 * Classe de base pour les commandes avec animations
 */
abstract class AnimatedCommand extends Command
{
    /**
     * Instance de ConsoleAnimation
     *
     * @var ConsoleAnimation
     */
    protected $animation;
    
    /**
     * Constructeur
     *
     * @param Application $app
     * @param array $args
     */
    public function __construct(Application $app, array $args = [])
    {
        parent::__construct($app, $args);
        $this->animation = new ConsoleAnimation();
    }
    
    /**
     * Affiche un titre pour la commande
     *
     * @param string $title
     * @return void
     */
    protected function title(string $title): void
    {
        $this->animation->title($title);
    }
    
    /**
     * Affiche une animation de chargement
     *
     * @param string $message
     * @param int $duration
     * @param string $type
     * @return void
     */
    protected function loading(string $message, int $duration = 2, string $type = 'default'): void
    {
        $this->animation->spinner($message, $duration, $type);
    }
    
    /**
     * Affiche une barre de progression
     *
     * @param int $current
     * @param int $total
     * @param string $taskName
     * @param string $style
     * @return void
     */
    protected function progress(int $current, int $total, string $taskName, string $style = 'default'): void
    {
        $this->animation->progressBar($current, $total, $taskName, $style);
    }
    
    /**
     * Affiche un tableau de données
     *
     * @param array $headers
     * @param array $data
     * @return void
     */
    protected function table(array $headers, array $data): void
    {
        $this->animation->table($headers, $data);
    }
    
    /**
     * Affiche un message de succès
     *
     * @param string $message
     * @param string $symbol
     * @return void
     */
    protected function success(string $message, string $symbol = '🎉'): void
    {
        $this->animation->success($message, $symbol);
    }
    
    /**
     * Affiche un message d'erreur
     *
     * @param string $message
     * @return void
     */
    protected function error(string $message): void
    {
        $this->animation->error($message);
    }
    
    /**
     * Compte à rebours
     *
     * @param int $seconds
     * @param string $message
     * @return void
     */
    protected function countdown(int $seconds, string $message = "Démarrage dans"): void
    {
        $this->animation->countdown($seconds, $message);
    }
    
    /**
     * Attend avec symboles aléatoires
     *
     * @param string $message
     * @param int $duration
     * @return void
     */
    protected function wait(string $message, int $duration = 2): void
    {
        $this->animation->waitRandom($message, $duration);
    }
    /**
     * Affiche un message d'information
     *
     * @param string $message
     * @return void
     */
    protected function info(string $message): void
    {
        echo "  " . $message . "\n";
    }

    /**
     * Affiche un message d'avertissement
     *
     * @param string $message
     * @return void
     */
    protected function warning(string $message): void
    {
        echo "\033[33m  " . $message . "\033[0m\n";
    }

    /**
     * Affiche une nouvelle ligne
     *
     * @param int $count
     * @return void
     */
    protected function newLine(int $count = 1): void
    {
        echo str_repeat("\n", $count);
    }
} 