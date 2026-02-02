<?php

namespace Database\Seeders;

use App\Models\Sale;
use App\Models\SaleDetail;
use App\Models\Customer;
use App\Models\Product;
use App\Models\User;

/**
 * Seeder pour les tables sales et sale_details
 */
class SaleSeeder
{
    /**
     * Exécute le seeder
     *
     * @return void
     */
    public function run(): void
    {
        // Les tables sont déjà vidées dans DatabaseSeeder
        
        // Connexion directe à la base de données
        $pdo = require __DIR__ . '/../connection.php';
        
        // Récupérer les clients directement depuis la base de données
        $customersStmt = $pdo->query("SELECT id FROM customers");
        $customers = $customersStmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Récupérer les produits
        $productsStmt = $pdo->query("SELECT id, price, stock FROM products");
        $products = $productsStmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Récupérer les utilisateurs
        $usersStmt = $pdo->query("SELECT id FROM users");
        $users = $usersStmt->fetchAll(\PDO::FETCH_ASSOC);
        
        if (empty($customers) || empty($products) || empty($users)) {
            echo "Erreur: Veuillez d'abord exécuter les seeders pour les clients, produits et utilisateurs.\n";
            return;
        }
        
        echo "Clients trouvés: " . count($customers) . "\n";
        echo "Produits trouvés: " . count($products) . "\n";
        echo "Utilisateurs trouvés: " . count($users) . "\n";
        
        // Créer des ventes de test
        $salesData = [];
        $startDate = strtotime('-30 days');
        $endDate = time();
        
        // Générer 20 ventes sur les 30 derniers jours
        for ($i = 0; $i < 20; $i++) {
            $date = date('Y-m-d H:i:s', rand($startDate, $endDate));
            $customerId = $this->getRandomId($customers);
            $userId = $this->getRandomId($users);
            
            // Générer un numéro de facture unique
            $invoiceNumber = 'INV-' . date('Ymd', strtotime($date)) . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT);
            
            $salesData[] = [
                'customer_id' => $customerId,
                'user_id' => $userId,
                'invoice_number' => $invoiceNumber,
                'total_amount' => 0, // Sera calculé après l'ajout des détails
                'payment_method' => $this->getRandomPaymentMethod(),
                'payment_status' => 'paid',
                'notes' => 'Vente générée par seeder',
                'created_at' => $date,
                'updated_at' => $date
            ];
        }
        
        // Insérer les ventes et leurs détails
        foreach ($salesData as $saleData) {
            // Créer la vente
            $sale = Sale::create($saleData);
            
            // Générer entre 1 et 5 produits pour cette vente
            $numProducts = rand(1, 5);
            $selectedProducts = $this->getRandomProducts($products, $numProducts);
            $totalAmount = 0;
            
            foreach ($selectedProducts as $product) {
                $quantity = rand(1, 3);
                $price = (float)$product['price'];
                $subtotal = $price * $quantity;
                $totalAmount += $subtotal;
                
                // Créer le détail de vente
                SaleDetail::create([
                    'sale_id' => $sale->id,
                    'product_id' => (int)$product['id'],
                    'quantity' => $quantity,
                    'price' => $price,
                    'subtotal' => $subtotal
                ]);
                
                // Mettre à jour le stock du produit directement dans la base de données
                $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?")
                    ->execute([$quantity, $product['id']]);
            }
            
            // Mettre à jour le montant total de la vente
            $sale->total_amount = $totalAmount;
            $sale->save();
        }
        
        echo "Ventes et détails de ventes créés avec succès.\n";
    }
    
    /**
     * Obtient un ID aléatoire à partir d'une collection
     *
     * @param array $collection
     * @return int|null
     */
    private function getRandomId(array $collection): ?int
    {
        if (empty($collection)) {
            return null;
        }
        
        $randomIndex = array_rand($collection);
        
        // Vérifier si c'est un objet ou un tableau
        if (is_object($collection[$randomIndex])) {
            return $collection[$randomIndex]->id;
        } else if (isset($collection[$randomIndex]['id'])) {
            return (int)$collection[$randomIndex]['id'];
        }
        
        return null;
    }
    
    /**
     * Obtient une méthode de paiement aléatoire
     *
     * @return string
     */
    private function getRandomPaymentMethod(): string
    {
        $methods = ['cash', 'card', 'transfer', 'check'];
        return $methods[array_rand($methods)];
    }
    
    /**
     * Obtient un ensemble aléatoire de produits
     *
     * @param array $products
     * @param int $count
     * @return array
     */
    private function getRandomProducts(array $products, int $count): array
    {
        if (count($products) <= $count) {
            return $products;
        }
        
        $keys = array_rand($products, $count);
        
        if (!is_array($keys)) {
            $keys = [$keys];
        }
        
        $selectedProducts = [];
        foreach ($keys as $key) {
            $selectedProducts[] = $products[$key];
        }
        
        return $selectedProducts;
    }
}
