<?php

/**
 * Migration pour la table notifications
 */
class CreateNotificationsTable extends \Calvino\Core\Migration
{
    /**
     * Exécute la migration
     *
     * @return void
     */
    public function up(): void
    {
        // Création de la table notifications
        $this->create('notifications', function (\Calvino\Core\Schema $table) {
            $table->id();
            $table->integer('user_id')->comment('ID de l\'utilisateur destinataire');
            $table->string('title', 255)->comment('Titre de la notification');
            $table->text('message')->comment('Contenu de la notification');
            $table->enum('type', ['info', 'warning', 'error', 'success'])->default('info')->comment('Type de notification (info, warning, error, success)');
            $table->boolean('is_read')->default(0)->comment('Statut de lecture (0=non lu, 1=lu)');
            $table->json('data')->nullable()->comment('Données supplémentaires au format JSON');
            $table->timestamp('created_at')->default('CURRENT_TIMESTAMP')->comment('Date de création');
            
            // Index pour améliorer les performances
            $table->index('user_id', 'notifications_user_id_index');
            $table->index('is_read', 'notifications_is_read_index');
            
            // Clé étrangère vers la table users
            $table->foreign('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        });

        // Ajout de la colonne stock à la table products si elle n'existe pas déjà
        $this->table('products', function (\Calvino\Core\TableModifier $table) {
            if (!$this->columnExists('products', 'stock')) {
                $table->integer('stock')->default(0)->comment('Niveau de stock actuel');
            }
        });

        // Ajout de la colonne user_id à la table sales si elle n'existe pas déjà
        $this->table('sales', function (\Calvino\Core\TableModifier $table) {
            if (!$this->columnExists('sales', 'user_id')) {
                $table->integer('user_id')
                      ->nullable()
                      ->comment('ID de l\'utilisateur qui a effectué la vente');
                
                // Dans TableModifier, la méthode foreign a une signature différente
                if ($this->foreignKeyExists('sales', 'sales_user_id_foreign') === false) {
                    $table->foreign('user_id', 'users', 'id', 'SET NULL', 'CASCADE');
                }
            }
        });
    }

    /**
     * Annule la migration
     *
     * @return void
     */
    public function down(): void
    {
        // Supprimer les contraintes de clé étrangère d'abord
        $pdo = require dirname(__DIR__) . '/connection.php';
        
        // Supprimer la contrainte de clé étrangère sur sales.user_id
        $pdo->exec('ALTER TABLE `sales` DROP FOREIGN KEY IF EXISTS `sales_user_id_foreign`');
        
        // Supprimer la colonne user_id de la table sales
        $pdo->exec('ALTER TABLE `sales` DROP COLUMN IF EXISTS `user_id`');
        
        // Supprimer la colonne stock de la table products
        $pdo->exec('ALTER TABLE `products` DROP COLUMN IF EXISTS `stock`');
        
        // Supprimer la table notifications
        $this->drop('notifications');
    }

    /**
     * Vérifie si une colonne existe dans une table
     *
     * @param string $table
     * @param string $column
     * @return bool
     */
    private function columnExists(string $table, string $column): bool
    {
        $pdo = require dirname(__DIR__) . '/connection.php';
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as column_exists
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
            AND COLUMN_NAME = ?
        ");
        $stmt->execute([$table, $column]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $result['column_exists'] > 0;
    }
    
    /**
     * Vérifie si une clé étrangère existe dans une table
     *
     * @param string $table
     * @param string $constraintName
     * @return bool
     */
    private function foreignKeyExists(string $table, string $constraintName): bool
    {
        $pdo = require dirname(__DIR__) . '/connection.php';
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as constraint_exists
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = DATABASE()
            AND TABLE_NAME = ?
            AND CONSTRAINT_NAME = ?
            AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ");
        $stmt->execute([$table, $constraintName]);
        $result = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        return $result['constraint_exists'] > 0;
    }
} 