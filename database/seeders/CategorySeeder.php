<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Enums\CategoryIcon;

/**
 * Seeder pour la table categories
 */
class CategorySeeder
{
    /**
     * Exécute le seeder
     *
     * @return void
     */
    public function run(): void
    {
        // Créer des catégories de test
        $categories = [
            [
                'name' => 'Antibiotiques',
                'description' => 'Médicaments qui combattent les infections bactériennes',
                'icon' => CategoryIcon::CAPSULES,
                'is_active' => 1
            ],
            [
                'name' => 'Analgésiques',
                'description' => 'Médicaments contre la douleur',
                'icon' => CategoryIcon::PILLS,
                'is_active' => 1
            ],
            [
                'name' => 'Anti-inflammatoires',
                'description' => 'Médicaments qui réduisent l\'inflammation',
                'icon' => CategoryIcon::PRESCRIPTION,
                'is_active' => 1
            ],
            [
                'name' => 'Antihistaminiques',
                'description' => 'Médicaments contre les allergies',
                'icon' => CategoryIcon::FLASK,
                'is_active' => 1
            ],
            [
                'name' => 'Vitamines et suppléments',
                'description' => 'Produits pour compléter l\'alimentation',
                'icon' => CategoryIcon::CAPSULES,
                'is_active' => 1
            ],
            [
                'name' => 'Soins de la peau',
                'description' => 'Produits pour les soins cutanés',
                'icon' => CategoryIcon::HEART,
                'is_active' => 1
            ],
            [
                'name' => 'Matériel médical',
                'description' => 'Équipements et fournitures médicales',
                'icon' => CategoryIcon::BANDAGE,
                'is_active' => 1
            ],
            [
                'name' => 'Antispasmodiques',
                'description' => 'relaxation musculaire du tube digestif, utérus',
                'icon' => CategoryIcon::CAPSULES,
                'is_active' => 1
            ],
            [
                'name' => 'Gastro-entérologie',
                'description' => 'réduction acide gastrique',
                'icon' => CategoryIcon::CAPSULES,
                'is_active' => 1
            ],
            [
                'name' => 'Respiratoire',
                'description' => 'dilater les bronches en cas asthme',
                'icon' => CategoryIcon::FLASK,
                'is_active' => 1
            ],
            [
                'name' => 'Pédiatrie',
                'description' => 'douleur et la fièvre',
                'icon' => CategoryIcon::CAPSULES,
                'is_active' => 1
            ],
            [
                'name' => 'Antitussifs',
                'description' => 'toux',
                'icon' => CategoryIcon::FLASK,
                'is_active' => 1
            ],
        ];
        
        foreach ($categories as $categoryData) {
            Category::create($categoryData);
        }
        
        echo "Catégories créées avec succès.\n";
    }
}
