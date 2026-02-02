<?php

namespace Database\Seeders;

use App\Models\Sale;
use App\Models\Transaction;
use App\Models\User;

/**
 * Seeder pour la table transactions
 */
class TransactionSeeder
{
    /**
     * Exécute le seeder
     *
     * @return void
     */
    public function run(): void
    {
        // Connexion directe à la base de données
        $pdo = require __DIR__ . '/../connection.php';
        
        // Récupérer toutes les ventes
        $salesStmt = $pdo->query("SELECT id, total_amount FROM sales");
        $sales = $salesStmt->fetchAll(\PDO::FETCH_ASSOC);
        
        // Récupérer tous les utilisateurs
        $usersStmt = $pdo->query("SELECT id FROM users WHERE role = 'admin' OR role = 'pharmacist' OR role = 'cashier'");
        $users = $usersStmt->fetchAll(\PDO::FETCH_ASSOC);
        
        if (empty($sales) || empty($users)) {
            echo "Erreur: Veuillez d'abord exécuter les seeders pour les ventes et les utilisateurs.\n";
            return;
        }
        
        echo "Ventes trouvées: " . count($sales) . "\n";
        echo "Utilisateurs trouvés: " . count($users) . "\n";
        
        // Vider la table des transactions
        $pdo->exec("TRUNCATE transactions");
        
        // Pour chaque vente, créer 1 à 3 transactions
        $transactionsData = [];
        
        foreach ($sales as $sale) {
            $saleId = $sale['id'];
            $totalAmount = floatval($sale['total_amount']);
            
            // Déterminer si c'est un paiement complet, partiel ou en plusieurs fois
            $paymentType = rand(1, 10);
            
            // 60% des cas : paiement complet en une fois
            if ($paymentType <= 6) {
                $transactionsData[] = $this->createTransactionData(
                    $saleId,
                    $totalAmount,
                    'payment',
                    $this->getRandomPaymentMethod(),
                    'completed',
                    $this->getRandomUserId($users)
                );
            } 
            // 30% des cas : paiement en deux fois 
            else if ($paymentType <= 9) {
                $firstAmount = round($totalAmount * (rand(40, 70) / 100), 2);
                $secondAmount = round($totalAmount - $firstAmount, 2);
                
                // Premier paiement
                $transactionsData[] = $this->createTransactionData(
                    $saleId,
                    $firstAmount,
                    'payment',
                    $this->getRandomPaymentMethod(),
                    'completed',
                    $this->getRandomUserId($users)
                );
                
                // Deuxième paiement (50% chance d'être complété)
                $secondStatus = (rand(1, 2) === 1) ? 'completed' : 'pending';
                $transactionsData[] = $this->createTransactionData(
                    $saleId,
                    $secondAmount,
                    'payment',
                    $this->getRandomPaymentMethod(),
                    $secondStatus,
                    $this->getRandomUserId($users)
                );
            } 
            // 10% des cas : paiement plus complexe avec remboursement partiel
            else {
                // Paiement initial
                $initialPayment = round($totalAmount * 1.2, 2); // Paiement de 120% (erreur volontaire)
                $transactionsData[] = $this->createTransactionData(
                    $saleId,
                    $initialPayment,
                    'payment',
                    $this->getRandomPaymentMethod(),
                    'completed',
                    $this->getRandomUserId($users)
                );
                
                // Remboursement du trop-perçu
                $refundAmount = round($initialPayment - $totalAmount, 2);
                $transactionsData[] = $this->createTransactionData(
                    $saleId,
                    $refundAmount,
                    'refund',
                    $this->getRandomPaymentMethod(),
                    'completed',
                    $this->getRandomUserId($users)
                );
            }
        }
        
        // Insérer les transactions dans la base de données
        foreach ($transactionsData as $transaction) {
            $sql = "INSERT INTO transactions (sale_id, amount, type, payment_method, status, reference, created_by, note, created_at, updated_at) 
                    VALUES (:sale_id, :amount, :type, :payment_method, :status, :reference, :created_by, :note, :created_at, :updated_at)";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($transaction);
        }
        
        // Mettre à jour les statuts de paiement dans la table sales
        $updatePaidSql = "
            UPDATE sales s
            SET payment_status = CASE
                WHEN (
                    SELECT SUM(CASE WHEN t.type = 'payment' AND t.status = 'completed' THEN t.amount ELSE 0 END) - 
                           SUM(CASE WHEN t.type = 'refund' AND t.status = 'completed' THEN t.amount ELSE 0 END)
                    FROM transactions t
                    WHERE t.sale_id = s.id
                ) >= s.total_amount THEN 'paid'
                WHEN (
                    SELECT SUM(CASE WHEN t.type = 'payment' AND t.status = 'completed' THEN t.amount ELSE 0 END) - 
                           SUM(CASE WHEN t.type = 'refund' AND t.status = 'completed' THEN t.amount ELSE 0 END)
                    FROM transactions t
                    WHERE t.sale_id = s.id
                ) > 0 THEN 'partial'
                ELSE 'pending'
            END
        ";

        $pdo->exec($updatePaidSql);
        
        echo "Transactions générées: " . count($transactionsData) . "\n";
        echo "Statuts de paiement des ventes mis à jour.\n";
    }
    
    /**
     * Crée un tableau de données pour une transaction
     *
     * @param int $saleId
     * @param float $amount
     * @param string $type
     * @param string $paymentMethod
     * @param string $status
     * @param int $createdBy
     * @param string|null $reference
     * @param string|null $note
     * @return array
     */
    private function createTransactionData(
        int $saleId,
        float $amount,
        string $type,
        string $paymentMethod,
        string $status,
        int $createdBy,
        ?string $reference = null,
        ?string $note = null
    ): array {
        // Générer une date aléatoire dans les 30 derniers jours
        $date = date('Y-m-d H:i:s', rand(strtotime('-30 days'), time()));
        
        return [
            'sale_id' => $saleId,
            'amount' => $amount,
            'type' => $type,
            'payment_method' => $paymentMethod,
            'status' => $status,
            'reference' => $reference ?? ($paymentMethod === 'check' ? 'CHK-' . rand(1000, 9999) : null),
            'created_by' => $createdBy,
            'note' => $note ?? ($type === 'refund' ? 'Remboursement partiel' : 'Transaction générée par seeder'),
            'created_at' => $date,
            'updated_at' => $date
        ];
    }
    
    /**
     * Obtient un ID utilisateur aléatoire
     *
     * @param array $users
     * @return int
     */
    private function getRandomUserId(array $users): int
    {
        if (empty($users)) {
            return 1; // ID par défaut si aucun utilisateur
        }
        
        $randomIndex = array_rand($users);
        return (int)$users[$randomIndex]['id'];
    }
    
    /**
     * Obtient une méthode de paiement aléatoire
     *
     * @return string
     */
    private function getRandomPaymentMethod(): string
    {
        $methods = ['cash', 'card', 'transfer', 'check'];
        $weights = [70, 15, 10, 5]; // Pondération pour plus de réalisme
        
        $rand = rand(1, array_sum($weights));
        $sum = 0;
        
        foreach ($methods as $index => $method) {
            $sum += $weights[$index];
            if ($rand <= $sum) {
                return $method;
            }
        }
        
        return 'cash'; // Par défaut
    }
} 