<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    /**
     * Run the migrations.
     * Ajoute la colonne barcode à la table produits
     *
     * @return void
     */
    public function up()
    {
        Schema::table('produits', function (Blueprint $table) {
            // Ajouter la colonne barcode : VARCHAR(50) UNIQUE NULLABLE
            $table->string('barcode', 50)->nullable()->unique()->after('code_produit');
        });
    }

    /**
     * Reverse the migrations.
     * Supprime la colonne barcode de la table produits
     *
     * @return void
     */
    public function down()
    {
        Schema::table('produits', function (Blueprint $table) {
            // Supprimer la colonne barcode
            $table->dropColumn('barcode');
        });
    }
};
