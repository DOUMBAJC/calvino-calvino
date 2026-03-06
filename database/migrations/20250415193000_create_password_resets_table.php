<?php

/**
 * Migration pour la table password_resets
 * Stocke les tokens de réinitialisation de mot de passe (hashés).
 */
class CreatePasswordResetsTable extends \App\Core\Migration
{
    public function up(): void
    {
        $this->create('password_resets', function (\App\Core\Schema $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('ID de l\'utilisateur concerné');
            $table->string('token', 64)->unique()->comment('Token SHA-256 (jamais le token brut)');
            $table->timestamp('expires_at')->comment('Date d\'expiration du token');
            $table->timestamp('created_at')->useCurrent()->comment('Date de création');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        $this->drop('password_resets');
    }
}
