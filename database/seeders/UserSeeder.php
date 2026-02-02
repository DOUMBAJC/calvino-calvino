<?php

namespace Database\Seeders;

use App\Models\User;

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
        $pdo->exec("DELETE FROM users WHERE email != 'admin@pharmacie.com'");
        
        // Créer des utilisateurs de test
        $users = [
            [
                'name' => 'Admin',
                'email' => 'admin@pharmacie.com',
                'password' => password_hash('Pa$$w0rd!', PASSWORD_BCRYPT, ['cost' => 12]),
                'role' => 'admin',
                'phone' => '0123456789',
                'address' => '123 Rue Principale',
                'is_active' => 1
            ],
            [
                'name' => 'Manager',
                'email' => 'manager@pharmacie.com',
                'password' => password_hash('Pa$$w0rd!', PASSWORD_BCRYPT, ['cost' => 12]),
                'role' => 'manager',
                'phone' => '0123456789',
                'address' => '123 Rue Principale',
                'is_active' => 1
            ],
            [
                'name' => 'Pharmacien',
                'email' => 'pharmacist@pharmacie.com',
                'password' => password_hash('Pa$$w0rd!', PASSWORD_BCRYPT, ['cost' => 12]),
                'role' => 'pharmacist',
                'phone' => '0123456788',
                'address' => '456 Avenue Secondaire',
                'is_active' => 1
            ],
            [
                'name' => 'Caissier',
                'email' => 'cashier@pharmacie.com',
                'password' => password_hash('Pa$$w0rd!', PASSWORD_BCRYPT, ['cost' => 12]),
                'role' => 'cashier',
                'phone' => '0123456787',
                'address' => '789 Boulevard Tertiaire',
                'is_active' => 1
            ]
        ];
        
        foreach ($users as $userData) {
            User::create($userData);
        }
        
        echo "Utilisateurs créés avec succès.\n";
    }
}
