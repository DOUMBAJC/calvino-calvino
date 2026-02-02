<?php

/**
 * Migration pour la table users
 */
class CreateUsersTable extends \App\Core\Migration
{
    /**
     * Exécute la migration
     *
     * @return void
     */
    public function up(): void
    {
        $this->create('users', function (\App\Core\Schema $table) {
            $table->id();
            $table->string('name', 255)->comment('Nom complet de l\'utilisateur');
            $table->string('email', 255)->unique()->comment('Adresse email unique');
            $table->string('password', 255)->comment('Mot de passe crypté');
            $table->enum('role', ['admin', 'manager', 'pharmacist', 'cashier'])->default('pharmacist')->comment('Rôle de l\'utilisateur');
            $table->string('phone', 20)->nullable()->comment('Numéro de téléphone');
            $table->text('address')->nullable()->comment('Adresse physique');
            $table->boolean('is_active')->default(1)->comment('Statut actif ou inactif');
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
        $this->drop('users');
    }
} 