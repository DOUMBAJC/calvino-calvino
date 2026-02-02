<?php

namespace Database\Seeders;

use App\Models\Manufacturer;

/**
 * Seeder pour la table manufacturers
 */
class ManufacturerSeeder
{
    /**
     * Exécute le seeder
     *
     * @return void
     */
    public function run(): void
    {
        // La table est déjà vidée dans DatabaseSeeder
        
        // Créer des fabricants de test adaptés au contexte camerounais
        $manufacturers = [
            [
                'name' => 'Cinpharm',
                'contact_name' => 'Jean Noubissi',
                'email' => 'contact@cinpharm.cm',
                'phone' => '233423500',
                'website' => 'cinpharm.com',
                'address' => 'Zone Industrielle, Douala',
                'is_active' => 1
            ],
            [
                'name' => 'Africure Pharmaceuticals',
                'contact_name' => 'Marie Ndjeng',
                'email' => 'info@africure.cm',
                'phone' => '233456788',
                'website' => 'africure.com',
                'address' => 'Zone Industrielle Bonaberi, Douala',
                'is_active' => 1
            ],
            [
                'name' => 'Laborex Cameroun',
                'contact_name' => 'Pierre Talom',
                'email' => 'contact@laborex.cm',
                'phone' => '222234567',
                'address' => 'Quartier Akwa, Douala',
                'is_active' => 1
            ],
            [
                'name' => 'Saphyto Cameroun',
                'contact_name' => 'Sophie Mbakop',
                'email' => 'info@saphyto.cm',
                'phone' => '222345678',
                'address' => 'Quartier Bali, Douala',
                'is_active' => 1
            ],
            [
                'name' => 'Pharmaco Cameroun',
                'contact_name' => 'Thomas Mbida',
                'email' => 'contact@pharmaco.cm',
                'phone' => '233567890',
                'website' => 'pharmaco.cm',
                'address' => 'Avenue Ahmadou Ahidjo, Yaoundé',
                'is_active' => 1
            ]
        ];
        
        foreach ($manufacturers as $manufacturerData) {
            Manufacturer::create($manufacturerData);
        }
        
        echo "Fabricants créés avec succès.\n";
    }
}
