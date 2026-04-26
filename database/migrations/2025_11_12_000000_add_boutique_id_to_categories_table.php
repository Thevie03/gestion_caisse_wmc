<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'boutique_id')) {
                $table->foreignId('boutique_id')
                    ->nullable()
                    ->constrained()
                    ->cascadeOnDelete()
                    ->after('id');
            }
        });

        // Associer automatiquement les catégories existantes à une boutique basée sur les produits.
        $categories = DB::table('categories')
            ->whereNull('boutique_id')
            ->get();

        foreach ($categories as $category) {
            $produit = DB::table('produits')
                ->where('categorie', $category->nom)
                ->whereNotNull('boutique_id')
                ->select('boutique_id')
                ->orderBy('boutique_id')
                ->first();

            if ($produit) {
                DB::table('categories')
                    ->where('id', $category->id)
                    ->update(['boutique_id' => $produit->boutique_id]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'boutique_id')) {
                $table->dropConstrainedForeignId('boutique_id');
            }
        });
    }
};

