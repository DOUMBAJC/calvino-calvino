<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Notification;
use App\Models\Product;
use App\Services\NotificationService;

/**
 * Seeder pour la table notifications
 */
class NotificationSeeder
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
        $pdo->exec("TRUNCATE TABLE notifications");
        
        // Récupérer les utilisateurs
        $users = User::all();
        if (empty($users)) {
            echo "Aucun utilisateur trouvé, impossible de créer des notifications.\n";
            return;
        }
        
        // Service de notification
        $notificationService = new NotificationService();
        
        // Créer des notifications pour chaque utilisateur
        foreach ($users as $user) {
            $this->createNotificationsForUser($user, $notificationService);
        }
        
        // Créer des notifications de stock bas
        $this->createLowStockNotifications($notificationService);
        
        // Créer des notifications de ventes
        $this->createSaleNotifications($notificationService);
        
        echo "Notifications créées avec succès.\n";
    }
    
    /**
     * Crée des notifications pour un utilisateur donné
     *
     * @param User $user
     * @param NotificationService $notificationService
     * @return void
     */
    private function createNotificationsForUser(User $user, NotificationService $notificationService): void
    {
        // Notification de bienvenue
        $notificationService->send(
            $user,
            'Bienvenue sur l\'application',
            'Merci d\'avoir rejoint notre système de gestion de pharmacie. Nous sommes ravis de vous compter parmi nous.',
            'info'
        );
        
        // Notification système
        $notificationService->send(
            $user,
            'Mise à jour du système',
            'Une mise à jour importante sera déployée ce weekend. Veuillez sauvegarder vos données importantes.',
            'warning'
        );
        
        // Notification personnalisée selon le rôle
        if ($user->hasRole('admin')) {
            $notificationService->send(
                $user,
                'Rapport mensuel disponible',
                'Le rapport mensuel de performance est maintenant disponible dans la section rapports.',
                'info',
                ['report_id' => 123]
            );
            
            $notificationService->send(
                $user,
                'Nouvel utilisateur enregistré',
                'Un nouvel utilisateur s\'est inscrit sur la plateforme et attend votre validation.',
                'info'
            );
        } elseif ($user->hasRole('manager')) {
            $notificationService->send(
                $user,
                'Nouvelle commande fournisseur',
                'Une nouvelle commande a été placée auprès du fournisseur XYZ.',
                'info'
            );
        } elseif ($user->hasRole('pharmacist')) {
            $notificationService->send(
                $user,
                'Nouveaux produits disponibles',
                'De nouveaux produits ont été ajoutés à l\'inventaire et sont prêts à être vérifiés.',
                'info'
            );
        }
        
        // Marquer certaines notifications comme lues
        $notifications = (new Notification())->getForUser($user->id);
        if (!empty($notifications) && count($notifications) > 1) {
            (new Notification())->markAsRead($notifications[0]['id'], $user->id);
        }
    }
    
    /**
     * Crée des notifications de stock bas
     *
     * @param NotificationService $notificationService
     * @return void
     */
    private function createLowStockNotifications(NotificationService $notificationService): void
    {
        // Récupérer quelques produits
        $products = Product::all();
        
        if (empty($products)) {
            return;
        }
        
        // Sélectionner quelques produits aléatoires pour simuler des stocks bas
        $productsForLowStock = array_slice($products, 0, min(3, count($products)));
        
        foreach ($productsForLowStock as $product) {
            $notificationService->notifyLowStock(
                $product->id,
                $product->name,
                rand(1, 5) // Stock entre 1 et 5
            );
        }
    }
    
    /**
     * Crée des notifications de vente
     *
     * @param NotificationService $notificationService
     * @return void
     */
    private function createSaleNotifications(NotificationService $notificationService): void
    {
        // Récupérer tous les utilisateurs sauf admin
        $users = User::all();
        $nonAdminUsers = array_filter($users, function($user) {
            return !$user->hasRole('admin');
        });
        
        if (empty($nonAdminUsers)) {
            $nonAdminUsers = $users; // Utiliser tous les utilisateurs si aucun n'est non-admin
        }
        
        // Créer quelques notifications de vente
        foreach ($nonAdminUsers as $index => $user) {
            // Simuler une notification de vente pour chaque utilisateur
            $saleId = 1000 + $index;
            $amount = rand(5000, 50000) / 100; // Montant entre 50 et 500
            
            $notificationService->notifySale($saleId, $amount, $user->id);
            
            // Simuler une vente importante pour le premier utilisateur
            if ($index === 0) {
                $bigSaleId = 2000;
                $bigAmount = 1500.75;
                
                $notificationService->notifySale($bigSaleId, $bigAmount, $user->id);
                
                $notificationService->notifyAdmins(
                    'Vente importante',
                    "Une vente importante (#{$bigSaleId}) d'un montant de {$bigAmount} € a été enregistrée.",
                    'info',
                    ['sale_id' => $bigSaleId, 'total' => $bigAmount]
                );
            }
        }
    }
} 