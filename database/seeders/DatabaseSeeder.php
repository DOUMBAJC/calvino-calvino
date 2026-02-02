<?php

namespace Database\Seeders;

/**
 * Seeder principal qui exécute tous les autres seeders
 */
class DatabaseSeeder
{
    /**
     * Liste des seeders à exécuter
     *
     * @var array
     */
    protected array $seeders = [
        UserSeeder::class
    ];

    /**
     * Exécute tous les seeders
     *
     * @return void
     */
    public function run(): void
    {
        // Désactiver les contraintes de clé étrangère pour toute la durée du seeding
        $pdo = require __DIR__ . '/../connection.php';
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
        
        try {
            // Exécuter les seeders dans l'ordre
            foreach ($this->seeders as $seeder) {
                echo "Exécution du seeder: " . $seeder . "\n";
                $seederInstance = new $seeder();
                $seederInstance->run();
            }
        } finally {
            // Réactiver les contraintes de clé étrangère à la fin
            $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
        }
    }
}
