<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Supprimer l'ancienne contrainte unique sur 'nom' uniquement
        Schema::table('categories', function (Blueprint $table) {
            // Supprimer l'index unique existant sur 'nom'
            $table->dropUnique(['nom']);
        });

        // Créer une nouvelle contrainte unique composite sur (nom, boutique_id)
        // Cela permet à deux boutiques d'avoir la même catégorie, mais empêche une boutique d'avoir deux catégories avec le même nom
        Schema::table('categories', function (Blueprint $table) {
            $table->unique(['nom', 'boutique_id'], 'categories_nom_boutique_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer la contrainte unique composite
        Schema::table('categories', function (Blueprint $table) {
            $table->dropUnique('categories_nom_boutique_unique');
        });

        // Restaurer l'ancienne contrainte unique sur 'nom' uniquement
        Schema::table('categories', function (Blueprint $table) {
            $table->unique('nom');
        });
    }
};


