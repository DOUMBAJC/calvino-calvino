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
        UserSeeder::class,
        CategorySeeder::class,
        ManufacturerSeeder::class,
        ProductSeeder::class,
        CustomerSeeder::class,
        SaleSeeder::class,
        TransactionSeeder::class,
        NotificationSeeder::class
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
            // Vider d'abord les tables avec des dépendances
            $pdo->exec("TRUNCATE TABLE notifications");
            $pdo->exec("TRUNCATE TABLE transactions");
            $pdo->exec("TRUNCATE TABLE sale_details");
            $pdo->exec("TRUNCATE TABLE sales");
            $pdo->exec("TRUNCATE TABLE products");
            $pdo->exec("TRUNCATE TABLE categories");
            $pdo->exec("TRUNCATE TABLE manufacturers");
            $pdo->exec("TRUNCATE TABLE customers");
            
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
