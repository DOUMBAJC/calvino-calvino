<?php

namespace Calvino\Console\Commands;

use Calvino\Console\AnimatedCommand;

/**
 * Commande d'aide affichant toutes les commandes disponibles
 */
class HelpCommand extends AnimatedCommand
{
    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'help';
    }
    
    /**
     * {@inheritdoc}
     */
    public function getDescription(): string
    {
        return 'Affiche ce message d\'aide';
    }
    
    /**
     * {@inheritdoc}
     */
    public function handle(): void
    {
        // Nettoyer l'écran
        $this->animation->clearScreen();
        
        $version = \Calvino\Core\Application::VERSION;
        $paddedVersion = str_pad($version, 6);
        
        // En-tête stylisé avec version dynamique
        echo "\n";
        echo "  \033[1;36m╭──────────────────────────────────────────────────────────╮\033[0m\n";
        echo "  \033[1;36m│                                                          │\033[0m\n";
        echo "  \033[1;36m│\033[0m   \033[1;37mCALVINO FRAMEWORK\033[0m \033[0;90mv{$paddedVersion}\033[0m                              \033[1;36m│\033[0m\n";
        echo "  \033[1;36m│\033[0m   \033[0;37mConsole d'administration et développement\033[0m              \033[1;36m│\033[0m\n";
        echo "  \033[1;36m│                                                          │\033[0m\n";
        echo "  \033[1;36m╰──────────────────────────────────────────────────────────╯\033[0m\n";
        echo "\n";
        
        // Récupérer et organiser les commandes
        $commands = [
            '🚀 DÉVELOPPEMENT' => [
                'serve' => ['Démarre le serveur de développement', 'serve [--host=IP] [--port=PORT]'],
                'make:controller' => ['Crée un nouveau contrôleur', 'make:controller [Nom]'],
                'make:model' => ['Crée un nouveau modèle', 'make:model [Nom]'],
                'make:migration' => ['Crée une nouvelle migration', 'make:migration [Nom]'],
                'make:middleware' => ['Crée un nouveau middleware', 'make:middleware [Nom]'],
                'make:command' => ['Crée une nouvelle commande CLI', 'make:command [Nom]'],
                'route:list' => ['Liste toutes les routes enregistrées', 'route:list'],
            ],
            '💾 BASE DE DONNÉES' => [
                'migrate' => ['Exécute les migrations en attente', 'migrate'],
                'db:refresh' => ['Réinitialise et re-migre la base de données', 'db:refresh'],
                'db:seed' => ['Peuple la base de données', 'db:seed'],
                'db:create' => ['Crée la base de données', 'db:create'],
                'db:tables' => ['Liste les tables de la base de données', 'db:tables'],
                'db:structure' => ['Affiche la structure d\'une table', 'db:structure [table]'],
                'db:check' => ['Vérifie l\'état de la base de données', 'db:check'],
            ],
            '🔧 UTILITAIRES SQL' => [
                'db:query' => ['Exécute une requête SQL brute', 'db:query "SELECT..."'],
                'db:export' => ['Exporte la base de données ou une table', 'db:export [table]'],
                'db:import' => ['Importe un fichier SQL', 'db:import [fichier.sql]'],
                'db:add-column' => ['Ajoute une colonne', 'db:add-column [table] [nom] [type]'],
                'db:modify-column' => ['Modifie une colonne', 'db:modify-column [table] [nom] [type]'],
                'db:drop-column' => ['Supprime une colonne', 'db:drop-column [table] [nom]'],
                'db:drop-table' => ['Supprime une table', 'db:drop-table [table]'],
            ],
            '⚙️  SYSTÈME' => [
                'help' => ['Affiche cette aide', 'help'],
            ]
        ];

        // Affichage des colonnes
        foreach ($commands as $category => $categoryCommands) {
            echo "  \033[1;33m" . $category . "\033[0m\n";
            
            foreach ($categoryCommands as $name => $info) {
                $description = $info[0];
                $usage = isset($info[1]) ? $info[1] : '';
                
                // Nom de la commande en vert
                printf("  \033[32m%-22s\033[0m %s\n", $name, $description);
                
                // Usage en gris, s'il existe et diffère du nom simple
                if ($usage && $usage !== $name) {
                    $usageStr = "Usage: composer calvino " . $usage;
                    printf("  \033[90m%-22s %s\033[0m\n", "└─", $usageStr);
                }
            }
            echo "\n";
        }
        
        echo "  \033[37mPour plus d'aide sur une commande :\033[0m composer calvino help [commande]\n";
        echo "\n";
    }
}