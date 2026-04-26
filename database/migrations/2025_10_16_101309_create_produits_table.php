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
    public function up()
    {
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('categorie');
            $table->text('description')->nullable();
            $table->decimal('prix_achat', 10, 2);
            $table->decimal('prix_vente', 10, 2);
            $table->integer('quantite_stock')->default(0);
            $table->integer('stock_minimum')->default(5);
            $table->string('code_produit')->unique()->nullable();
            $table->string('image')->nullable();
            $table->foreignId('boutique_id')->constrained('boutiques')->onDelete('cascade');
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('produits');
    }
};
