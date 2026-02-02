<?php

namespace Database\Seeders;

use App\Models\Customer;

/**
 * Seeder pour la table customers
 */
class CustomerSeeder
{
    /**
     * Exécute le seeder
     *
     * @return void
     */
    public function run(): void
    {
        // Vider la table avant le seeding
        $pdo = require __DIR__ . '/../connection.php';
        $pdo->exec("SET FOREIGN_KEY_CHECKS=0");
        $pdo->exec("TRUNCATE TABLE customers");
        $pdo->exec("SET FOREIGN_KEY_CHECKS=1");
        
        // Créer des clients de test adaptés au contexte camerounais
        $customers = [
            [
                'name' => 'Etienne Mbarga',
                'email' => 'etienne.mbarga@example.com',
                'phone' => '677123456',
                'address' => 'Quartier Bastos, Yaoundé',
                'is_active' => 1
            ],
            [
                'name' => 'Sophie Ngo Bassa',
                'email' => 'sophie.ngobassa@example.com',
                'phone' => '698234567',
                'address' => 'Avenue Kennedy, Yaoundé',
                'is_active' => 1
            ],
            [
                'name' => 'Kalang Pierre',
                'email' => 'k.pierre@example.com',
                'phone' => '655345678',
                'address' => 'Quartier Golf, Douala',
                'is_active' => 1
            ],
            [
                'name' => 'Isabelle Tchuente',
                'email' => 'isabelle.tchuente@example.com',
                'phone' => '677456789',
                'address' => 'Quartier Bonanjo, Douala',
                'is_active' => 1
            ],
            [
                'name' => 'Pierre Kamdem',
                'email' => 'pierre.kamdem@example.com',
                'phone' => '698567890',
                'address' => 'Quartier Akwa, Douala',
                'is_active' => 1
            ],
            [
                'name' => 'Marie Nkoulou',
                'email' => 'marie.nkoulou@example.com',
                'phone' => '655678901',
                'address' => 'Quartier Omnisport, Yaoundé',
                'is_active' => 1
            ],
            [
                'name' => 'Thomas Ndongo',
                'email' => 'thomas.ndongo@example.com',
                'phone' => '677789012',
                'address' => 'Quartier Bonapriso, Douala',
                'is_active' => 1
            ],
            [
                'name' => 'Camille Fotso',
                'email' => 'camille.fotso@example.com',
                'phone' => '698890123',
                'address' => 'Centre-ville, Bafoussam',
                'is_active' => 1
            ],
            [
                'name' => 'Lucas Atangana',
                'email' => 'lucas.atangana@example.com',
                'phone' => '655901234',
                'address' => 'Quartier Ngoa-Ekelle, Yaoundé',
                'is_active' => 1
            ],
            [
                'name' => 'Emma Simo',
                'email' => 'emma.simo@example.com',
                'phone' => '677012345',
                'address' => 'Quartier Bali, Douala',
                'is_active' => 1
            ]
        ];
        
        foreach ($customers as $customerData) {
            Customer::create($customerData);
        }
        
        echo "Clients créés avec succès.\n";
    }
}
