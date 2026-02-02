<?php

/**
 * Migration pour la table activity_logs
 */
class CreateActivityLogsTable extends \App\Core\Migration
{
    /**
     * Exécute la migration
     *
     * @return void
     */
    public function up(): void
    {
        $this->create('activity_logs', function (\App\Core\Schema $table) {
            $table->id();
            $table->integer('user_id')->comment('ID de l\'utilisateur qui a effectué l\'action');
            $table->string('action', 100)->comment('Type d\'action effectuée (login, logout, create, update, delete)');
            $table->string('module', 100)->comment('Module concerné (auth, products, sales, inventory, etc.)');
            $table->text('description')->comment('Description détaillée de l\'action');
            $table->text('old_values')->nullable()->comment('Anciennes valeurs (pour les mises à jour)');
            $table->text('new_values')->nullable()->comment('Nouvelles valeurs (pour les créations/mises à jour)');
            $table->string('ip_address', 45)->nullable()->comment('Adresse IP de l\'utilisateur');
            $table->string('user_agent', 255)->nullable()->comment('User agent du navigateur');
            $table->timestamps();
        });
    }

    /**
     * Annule la migration
     *
     * @return void
     */
    public function down(): void
    {
        $this->drop('activity_logs');
    }
} 