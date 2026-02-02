<?php

namespace Database\Seeders;

use Calvino\Models\User;

/**
 * Seeder pour la table users
 */
class UserSeeder
{
    /**
     * Exécute le seeder
     *
     * @return void
     */
    public function run(): void
    {
        // Vider la table avant le seeding (sauf l'admin par défaut)
        $pdo = require __DIR__ . '/../connection.php';
        $pdo->exec("DELETE FROM users WHERE email != 'admin@example.com'");
        
        // Créer des utilisateurs de test
        $users = [
            [
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'password' => password_hash('password', PASSWORD_BCRYPT, ['cost' => 12]),
                'role' => 'admin',
                'phone' => '+237622037000',
                'address' => '123 Main Street',
                'is_active' => 1
            ],
            [
                'name' => 'Demo User',
                'email' => 'user@example.com',
                'password' => password_hash('password', PASSWORD_BCRYPT, ['cost' => 12]),
                'role' => 'user',
                'phone' => '+1234567891',
                'address' => '456 Second Avenue',
                'is_active' => 1
            ]
        ];
        
        foreach ($users as $userData) {
            User::create($userData);
        }
        
        echo "Utilisateurs créés avec succès.\n";
    }
}
