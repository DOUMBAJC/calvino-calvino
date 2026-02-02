<?php

/**
 * Migration pour la table user_sessions
 */
class CreateUserSessionsTable extends \Calvino\Core\Migration
{
    /**
     * Exécute la migration
     *
     * @return void
     */
    public function up(): void
    {
        $this->create('user_sessions', function (\Calvino\Core\Schema $table) {
            $table->id();
            $table->integer('user_id')->comment('ID de l\'utilisateur');
            $table->string('session_id', 100)->comment('Identifiant unique de la session');
            $table->text('token')->nullable()->comment('Token JWT actif');
            $table->text('refresh_token')->nullable()->comment('Refresh token actif');
            $table->string('ip_address', 45)->nullable()->comment('Adresse IP');
            $table->string('user_agent', 255)->nullable()->comment('User agent du navigateur');
            $table->string('device_name', 255)->nullable()->comment('Nom de l\'appareil');
            $table->string('device_type', 50)->nullable()->comment('Type d\'appareil (mobile, desktop, tablet)');
            $table->string('location', 255)->nullable()->comment('Localisation approximative');
            $table->timestamp('last_activity')->nullable()->comment('Dernière activité');
            $table->boolean('is_active')->default(1)->comment('Session active ou non');
            $table->timestamps();
            
            // Index pour améliorer les performances
            $table->index('user_id');
            $table->index('session_id');
            $table->index('is_active');
            
            // Clé étrangère vers la table users
            $table->foreign('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        });
    }

    /**
     * Annule la migration
     *
     * @return void
     */
    public function down(): void
    {
        $this->dropIfExists('user_sessions');
    }
} 