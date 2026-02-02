<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Category;
use App\Models\Manufacturer;

/**
 * Seeder pour la table products
 */
class ProductSeeder
{
    /**
     * Exécute le seeder
     *
     * @return void
     */
    public function run(): void
    {
        // La table est déjà vidée dans DatabaseSeeder
        
        // Connexion directe à la base de données
        $pdo = require __DIR__ . '/../connection.php';
        
        // Récupérer les catégories directement depuis la base de données
        $categoriesStmt = $pdo->query("SELECT id, name FROM categories");
        $categories = $categoriesStmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Récupérer les fabricants directement depuis la base de données
        $manufacturersStmt = $pdo->query("SELECT id FROM manufacturers");
        $manufacturers = $manufacturersStmt->fetchAll(\PDO::FETCH_ASSOC);
        
        if (empty($categories) || empty($manufacturers)) {
            echo "Erreur: Veuillez d'abord exécuter les seeders pour les catégories et les fabricants.\n";
            return;
        }
        
        // Afficher les catégories pour le débogage
        echo "Catégories trouvées: " . count($categories) . "\n";
        
        // Créer des produits de test adaptés au contexte camerounais
        $products = [
            [
                'name' => 'Amoxicilline 500mg',
                'description' => 'Antibiotique à large spectre',
                'category_id' => $this->getCategoryIdByName($categories, 'Antibiotiques'),
                'manufacturer_id' => $this->getRandomManufacturerId($manufacturers),
                'reference' => 'AMOX500',
                'barcode' => '123456789012',
                'price' => 3500.00,
                'discount_price' => null,
                'stock' => 100,
                'stock_alert' => 20,
                'storage_location' => 'Étagère A1',
                'dosage' => '500mg',
                'requires_prescription' => 1,
                'is_eco_friendly' => 0,
                'usage_instructions' => 'Prendre 1 comprimé 3 fois par jour pendant 7 jours',
                'expiry_date' => date('Y-m-d', strtotime('+2 years')),
                'image' => null,
                'is_active' => 1
            ],
            [
                'name' => 'Doliprane 500mg',
                'description' => 'Analgésique et antipyrétique',
                'category_id' => $this->getCategoryIdByName($categories, 'Analgésiques'),
                'manufacturer_id' => $this->getRandomManufacturerId($manufacturers),
                'reference' => 'DOLI500',
                'barcode' => '123456789013',
                'price' => 1200.00,
                'discount_price' => null,
                'stock' => 200,
                'stock_alert' => 50,
                'storage_location' => 'Étagère B2',
                'dosage' => '500mg',
                'requires_prescription' => 0,
                'is_eco_friendly' => 0,
                'usage_instructions' => 'Prendre 1 à 2 comprimés toutes les 6 heures, sans dépasser 8 comprimés par jour',
                'expiry_date' => date('Y-m-d', strtotime('+3 years')),
                'image' => null,
                'is_active' => 1
            ],
            [
                'name' => 'Brufen 400mg',
                'description' => 'Anti-inflammatoire non stéroïdien',
                'category_id' => $this->getCategoryIdByName($categories, 'Anti-inflammatoires'),
                'manufacturer_id' => $this->getRandomManufacturerId($manufacturers),
                'reference' => 'BRUF400',
                'barcode' => '123456789014',
                'price' => 2500.00,
                'discount_price' => 2200.00,
                'stock' => 150,
                'stock_alert' => 30,
                'storage_location' => 'Étagère C3',
                'dosage' => '400mg',
                'requires_prescription' => 0,
                'is_eco_friendly' => 0,
                'usage_instructions' => 'Prendre 1 comprimé toutes les 8 heures, après les repas',
                'expiry_date' => date('Y-m-d', strtotime('+2 years')),
                'image' => null,
                'is_active' => 1
            ],
            [
                'name' => 'Zyrtec 10mg',
                'description' => 'Antihistaminique de deuxième génération',
                'category_id' => $this->getCategoryIdByName($categories, 'Antihistaminiques'),
                'manufacturer_id' => $this->getRandomManufacturerId($manufacturers),
                'reference' => 'ZYRT10',
                'barcode' => '123456789015',
                'price' => 3200.00,
                'discount_price' => null,
                'stock' => 120,
                'stock_alert' => 25,
                'storage_location' => 'Étagère D4',
                'dosage' => '10mg',
                'requires_prescription' => 0,
                'is_eco_friendly' => 0,
                'usage_instructions' => 'Prendre 1 comprimé par jour, le soir de préférence',
                'expiry_date' => date('Y-m-d', strtotime('+18 months')),
                'image' => null,
                'is_active' => 1
            ],
            [
                'name' => 'Alvityl Sirop',
                'description' => 'Complément alimentaire multivitaminé',
                'category_id' => $this->getCategoryIdByName($categories, 'Vitamines et suppléments'),
                'manufacturer_id' => $this->getRandomManufacturerId($manufacturers),
                'reference' => 'ALVI01',
                'barcode' => '123456789016',
                'price' => 4500.00,
                'discount_price' => 3900.00,
                'stock' => 80,
                'stock_alert' => 15,
                'storage_location' => 'Étagère E5',
                'dosage' => '5ml',
                'requires_prescription' => 0,
                'is_eco_friendly' => 1,
                'usage_instructions' => 'Prendre 1 cuillère à café (5ml) par jour, pour les enfants de plus de 3 ans',
                'expiry_date' => date('Y-m-d', strtotime('+3 years')),
                'image' => null,
                'is_active' => 1
            ],
            [
                'name' => 'Nivea Crème',
                'description' => 'Soin quotidien pour peau sèche',
                'category_id' => $this->getCategoryIdByName($categories, 'Soins de la peau'),
                'manufacturer_id' => $this->getRandomManufacturerId($manufacturers),
                'reference' => 'NIVEA01',
                'barcode' => '123456789017',
                'price' => 3800.00,
                'discount_price' => null,
                'stock' => 60,
                'stock_alert' => 10,
                'storage_location' => 'Étagère F6',
                'dosage' => null,
                'requires_prescription' => 0,
                'is_eco_friendly' => 0,
                'usage_instructions' => 'Appliquer quotidiennement sur une peau propre et sèche',
                'expiry_date' => date('Y-m-d', strtotime('+2 years')),
                'image' => null,
                'is_active' => 1
            ],
            [
                'name' => 'Tensiomètre Omron',
                'description' => 'Appareil de mesure de la pression artérielle',
                'category_id' => $this->getCategoryIdByName($categories, 'Matériel médical'),
                'manufacturer_id' => $this->getRandomManufacturerId($manufacturers),
                'reference' => 'OMRON01',
                'barcode' => '123456789018',
                'price' => 25000.00,
                'discount_price' => 22000.00,
                'stock' => 30,
                'stock_alert' => 5,
                'storage_location' => 'Vitrine G7',
                'dosage' => null,
                'requires_prescription' => 0,
                'is_eco_friendly' => 0,
                'usage_instructions' => 'Suivre le mode d\'emploi fourni par le fabricant',
                'expiry_date' => null,
                'image' => null,
                'is_active' => 1
            ],
            [
                'name' => 'Augmentin 625mg',
                'description' => 'Antibiotique à large spectre (amoxicilline + acide clavulanique)',
                'category_id' => $this->getCategoryIdByName($categories, 'Antibiotiques'),
                'manufacturer_id' => $this->getRandomManufacturerId($manufacturers),
                'reference' => 'AUG625',
                'barcode' => '112233445566',
                'price' => 3500.00,
                'discount_price' => null,
                'stock' => 100,
                'stock_alert' => 20,
                'storage_location' => 'Étagère A2',
                'dosage' => '625mg',
                'requires_prescription' => 1,
                'is_eco_friendly' => 0,
                'usage_instructions' => 'Prendre 1 comprimé 2 fois par jour pendant 7 à 10 jours',
                'expiry_date' => date('Y-m-d', strtotime('+1 year')),
                'image' => null,
                'is_active' => 1
            ],
            [
                'name' => 'Efferalgan 500mg',
                'description' => 'Analgésique et antipyrétique à base de paracétamol',
                'category_id' => $this->getCategoryIdByName($categories, 'Analgésiques'),
                'manufacturer_id' => $this->getRandomManufacturerId($manufacturers),
                'reference' => 'EFFE500',
                'barcode' => '321654987012',
                'price' => 1200.00,
                'discount_price' => null,
                'stock' => 180,
                'stock_alert' => 40,
                'storage_location' => 'Étagère B3',
                'dosage' => '500mg',
                'requires_prescription' => 0,
                'is_eco_friendly' => 0,
                'usage_instructions' => 'Dissoudre 1 comprimé dans un verre d\'eau, toutes les 6 heures si nécessaire',
                'expiry_date' => date('Y-m-d', strtotime('+1 year')),
                'image' => null,
                'is_active' => 1,
            ],
            [
                'name' => 'Spasfon Lyoc',
                'description' => 'Antispasmodique utilisé contre les douleurs digestives',
                'category_id' => $this->getCategoryIdByName($categories, 'Antispasmodiques'),
                'manufacturer_id' => $this->getRandomManufacturerId($manufacturers),
                'reference' => 'SPASFONL',
                'barcode' => '741852963258',
                'price' => 3000.00,
                'discount_price' => 2800.00,
                'stock' => 75,
                'stock_alert' => 20,
                'storage_location' => 'Étagère C4',
                'dosage' => '80mg',
                'requires_prescription' => 0,
                'is_eco_friendly' => 0,
                'usage_instructions' => 'Laisser fondre le comprimé sous la langue, 2 à 6 fois par jour',
                'expiry_date' => date('Y-m-d', strtotime('+4 years')),
                'image' => null,
                'is_active' => 1,
            ],
            [
                'name' => 'Nexium 20mg',
                'description' => 'Inhibiteur de la pompe à protons, utilisé contre le reflux gastrique',
                'category_id' => $this->getCategoryIdByName($categories, 'Gastro-entérologie'),
                'manufacturer_id' => $this->getRandomManufacturerId($manufacturers),
                'reference' => 'NEX20',
                'barcode' => '963852741369',
                'price' => 4800.00,
                'discount_price' => null,
                'stock' => 60,
                'stock_alert' => 15,
                'storage_location' => 'Étagère D5',
                'dosage' => '20mg',
                'requires_prescription' => 1,
                'is_eco_friendly' => 0,
                'usage_instructions' => 'Prendre 1 gélule par jour, avant le repas',
                'expiry_date' => date('Y-m-d', strtotime('+2 years')),
                'image' => null,
                'is_active' => 1,
            ],
            [
                'name' => 'Ventoline Inhaler',
                'description' => 'Bronchodilatateur utilisé pour traiter l\'asthme',
                'category_id' => $this->getCategoryIdByName($categories, 'Respiratoire'),
                'manufacturer_id' => $this->getRandomManufacturerId($manufacturers),
                'reference' => 'VENTO200',
                'barcode' => '159753486231',
                'price' => 5500.00,
                'discount_price' => null,
                'stock' => 80,
                'stock_alert' => 20,
                'storage_location' => 'Étagère E6',
                'dosage' => '100μg/dose',
                'requires_prescription' => 1,
                'is_eco_friendly' => 0,
                'usage_instructions' => '1 à 2 inhalations en cas de crise, jusqu\'à 4 fois par jour',
                'expiry_date' => date('Y-m-d', strtotime('+1 year')),
                'image' => null,
                'is_active' => 1,
            ],
            [
                'name' => 'Doliprane Enfant 250mg',
                'description' => 'Paracétamol pour enfants, contre fièvre et douleurs',
                'category_id' => $this->getCategoryIdByName($categories, 'Pédiatrie'),
                'manufacturer_id' => $this->getRandomManufacturerId($manufacturers),
                'reference' => 'DOLI250K',
                'barcode' => '258147369852',
                'price' => 1600.00,
                'discount_price' => 1400.00,
                'stock' => 90,
                'stock_alert' => 20,
                'storage_location' => 'Étagère F7',
                'dosage' => '250mg',
                'requires_prescription' => 0,
                'is_eco_friendly' => 1,
                'usage_instructions' => 'Dosage selon le poids de l\'enfant, 4 à 6 fois par jour maximum',
                'expiry_date' => date('Y-m-d', strtotime('+2 years')),
                'image' => null,
                'is_active' => 1,
            ],
            [
                'name' => 'Zithromax 250mg',
                'description' => 'Antibiotique de la famille des macrolides (azithromycine)',
                'category_id' => $this->getCategoryIdByName($categories, 'Antibiotiques'),
                'manufacturer_id' => $this->getRandomManufacturerId($manufacturers),
                'reference' => 'ZITH250',
                'barcode' => '369258147753',
                'price' => 6700.00,
                'discount_price' => 6000.00,
                'stock' => 40,
                'stock_alert' => 10,
                'storage_location' => 'Étagère A3',
                'dosage' => '250mg',
                'requires_prescription' => 1,
                'is_eco_friendly' => 0,
                'usage_instructions' => 'Prendre 1 comprimé par jour pendant 3 jours',
                'expiry_date' => date('Y-m-d', strtotime('+1 year')),
                'image' => null,
                'is_active' => 1,
            ],
            [
                'name' => 'Toplexil Sirop 150ml',
                'description' => 'Antitussif utilisé pour soulager la toux sèche et les irritations de la gorge',
                'category_id' => $this->getCategoryIdByName($categories, 'Antitussifs'),
                'manufacturer_id' => $this->getRandomManufacturerId($manufacturers),
                'reference' => 'TOPLEX150',
                'barcode' => '789456123789',
                'price' => 3500.00,
                'discount_price' => null,
                'stock' => 120,
                'stock_alert' => 25,
                'storage_location' => 'Étagère G2',
                'dosage' => '0.33mg/ml',
                'requires_prescription' => 0,
                'is_eco_friendly' => 0,
                'usage_instructions' => 'Adultes : 1 cuillère à soupe 3 fois par jour. Enfants : selon l\'âge',
                'expiry_date' => date('Y-m-d', strtotime('+1 years')),
                'image' => null,
                'is_active' => 1,
            ],
            [
                'name' => 'Cicalfate+ Crème Réparatrice 40ml',
                'description' => 'Crème réparatrice pour les peaux irritées ou fragilisées, favorise la régénération cutanée',
                'category_id' => $this->getCategoryIdByName($categories, 'Soins de la peau'),
                'manufacturer_id' => $this->getRandomManufacturerId($manufacturers),
                'reference' => 'CICALFATE40',
                'barcode' => '3401578654321',
                'price' => 5500,
                'discount_price' => null,
                'stock' => 80,
                'stock_alert' => 20,
                'storage_location' => 'Étagère A4',
                'dosage' => '40ml',
                'requires_prescription' => 0,
                'is_eco_friendly' => 0,
                'usage_instructions' => 'Appliquer quotidiennement sur les zones fragilisées',
                'expiry_date' => date('Y-m-d', strtotime('+1 years')),
                'image' => null,
                'is_active' => 1,
            ],
            [
                'name' => 'Eucerin DermoPure Gel Nettoyant 200ml',
                'description' => 'Gel nettoyant doux pour peaux à tendance acnéique, élimine l’excès de sébum sans dessécher',
                'category_id' => $this->getCategoryIdByName($categories, 'Soins de la peau'),
                'manufacturer_id' => $this->getRandomManufacturerId($manufacturers),
                'reference' => 'EUCDERM200',
                'barcode' => '4005800205987',
                'price' => 6500,
                'discount_price' => null,
                'stock' => 60,
                'stock_alert' => 15,
                'storage_location' => 'Étagère A5',
                'dosage' => '200ml',
                'requires_prescription' => 0,
                'is_eco_friendly' => 0,
                'usage_instructions' => 'Appliquer quotidiennement sur la peau',
                'expiry_date' => date('Y-m-d', strtotime('+1 years')),
                'image' => null,
                'is_active' => 1,
            ],
            [
                'name' => 'Cétaphil Crème Hydratante 100g',
                'description' => 'Crème hydratante pour peaux sensibles et sèches, sans parfum, hypoallergénique',
                'category_id' => $this->getCategoryIdByName($categories, 'Soins de la peau'),
                'manufacturer_id' => $this->getRandomManufacturerId($manufacturers),
                'reference' => 'CETACRM100',
                'barcode' => '3029939201086',
                'price' => 7200,
                'discount_price' => null,
                'stock' => 100,
                'stock_alert' => 30,
                'storage_location' => 'Étagère A6',
                'dosage' => '100g',
                'requires_prescription' => 0,
                'is_eco_friendly' => 0,
                'usage_instructions' => 'Appliquer quotidiennement sur la peau',
                'expiry_date' => date('Y-m-d', strtotime('+1 years')),
                'image' => null,
                'is_active' => 1,
            ],
            [
                'name' => 'Effaclar Duo+ 40ml',
                'description' => 'Soin anti-imperfections pour peaux grasses, réduit les boutons et les marques',
                'category_id' => $this->getCategoryIdByName($categories, 'Soins de la peau'),
                'manufacturer_id' => $this->getRandomManufacturerId($manufacturers),
                'reference' => 'EFFDUO40',
                'barcode' => '3337875598070',
                'price' => 8900,
                'discount_price' => null,
                'stock' => 70,
                'stock_alert' => 20,
                'storage_location' => 'Étagère A7',
                'dosage' => '40ml',
                'requires_prescription' => 0,
                'is_eco_friendly' => 0,
                'usage_instructions' => 'Appliquer quotidiennement sur la peau',
                'expiry_date' => date('Y-m-d', strtotime('+1 years')),
                'image' => null,
                'is_active' => 1,
            ]
               
              
        ];
        
        echo "Validation et correction des données produits...\n";
        $correctedCount = 0;
        
        foreach ($products as &$productData) {
            $correctedCount += $this->validateAndFixProductData($productData);
        }
        
        if ($correctedCount > 0) {
            echo "{$correctedCount} corrections ont été effectuées sur les données produits.\n";
        }
        
        $successCount = 0;
        $errorCount = 0;
        
        foreach ($products as $productData) {
            try {
                Product::create($productData);
                $successCount++;
            } catch (\Exception $e) {
                echo "⚠ Erreur lors de la création du produit '{$productData['name']}': " . $e->getMessage() . "\n";
                $errorCount++;
            }
        }
        
        echo "Produits créés avec succès: {$successCount}, Erreurs: {$errorCount}\n";
    }
    
    /**
     * Valide et corrige les données d'un produit
     *
     * @param array &$productData Les données du produit à valider (passées par référence)
     * @return int Le nombre de corrections effectuées
     */
    private function validateAndFixProductData(array &$productData): int
    {
        $corrections = 0;
        
        // Vérifier la référence
        if (empty($productData['reference'])) {
            $cleanName = preg_replace('/[^A-Za-z0-9]/', '', $productData['name']);
            $productData['reference'] = strtoupper(substr($cleanName, 0, 6)) . rand(100, 999);
            echo "- Référence générée pour '{$productData['name']}': {$productData['reference']}\n";
            $corrections++;
        }
        
        // Vérifier la date d'expiration
        if ($productData['expiry_date'] === null) {
            // Pour les appareils médicaux, utiliser une date d'expiration de 5 ans
            $productData['expiry_date'] = date('Y-m-d', strtotime('+5 years'));
            echo "- Date d'expiration définie pour '{$productData['name']}': {$productData['expiry_date']}\n";
            $corrections++;
        }
        
        // Vérifier le prix
        if (!isset($productData['price']) || $productData['price'] === null) {
            $productData['price'] = 0.00;
            echo "- Prix par défaut défini pour '{$productData['name']}'\n";
            $corrections++;
        }
        
        // Vérifier le stock
        if (!isset($productData['stock']) || $productData['stock'] === null) {
            $productData['stock'] = 0;
            echo "- Stock par défaut défini pour '{$productData['name']}'\n";
            $corrections++;
        }
        
        // Vérifier les champs booléens
        $booleanFields = ['requires_prescription', 'is_eco_friendly', 'is_active'];
        foreach ($booleanFields as $field) {
            if (!isset($productData[$field])) {
                $productData[$field] = 0;
                echo "- Champ {$field} défini à 0 pour '{$productData['name']}'\n";
                $corrections++;
            }
        }
        
        // Vérifier la catégorie et le fabricant
        if (empty($productData['category_id'])) {
            echo "⚠ Attention: Catégorie manquante pour '{$productData['name']}'. Ce produit ne sera pas créé.\n";
        }
        
        if (empty($productData['manufacturer_id'])) {
            echo "⚠ Attention: Fabricant manquant pour '{$productData['name']}'. Ce produit ne sera pas créé.\n";
        }
        
        return $corrections;
    }
    
    /**
     * Obtient l'ID d'une catégorie par son nom
     *
     * @param array $categories
     * @param string $name
     * @return int|null
     */
    private function getCategoryIdByName(array $categories, string $name): ?int
    {
        // Vérifier si nous avons des catégories
        if (empty($categories)) {
            echo "Aucune catégorie trouvée.\n";
            return null;
        }

        echo "Recherche de la catégorie: {$name}\n";
        
        foreach ($categories as $category) {
            // Vérifier si le tableau catégorie a la clé 'name'
            if (isset($category['name']) && $category['name'] === $name) {
                echo "Catégorie trouvée: {$name} (ID: {$category['id']})\n";
                return (int)$category['id'];
            }
        }
        
        // Si la catégorie n'est pas trouvée, utiliser la première catégorie
        if (!empty($categories) && isset($categories[0]['id'])) {
            echo "Catégorie '{$name}' non trouvée, utilisation de la première catégorie (ID: {$categories[0]['id']})\n";
            return (int)$categories[0]['id'];
        }
        
        echo "Aucune catégorie valide trouvée.\n";
        return null;
    }
    
    /**
     * Obtient un ID de fabricant aléatoire
     *
     * @param array $manufacturers
     * @return int|null
     */
    private function getRandomManufacturerId(array $manufacturers): ?int
    {
        if (empty($manufacturers)) {
            return null;
        }
        
        $randomIndex = array_rand($manufacturers);
        return (int)$manufacturers[$randomIndex]['id'];
    }
}

