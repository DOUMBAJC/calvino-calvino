<?php

use Calvino\Core\Migration;
use Calvino\Core\Schema;

/**
 * Migration: CreateTasksTable
 */
return new class extends Migration
{
    /**
     * Exécute la migration
     *
     * @return void
     */
    public function up(): void
    {
        $this->create('tasks', function ($table) {
            $table->id();
            // TODO: Ajouter les colonnes
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
        $this->dropIfExists('tasks');
    }
};
