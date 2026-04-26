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
        Schema::create('mouvements_stock', function (Blueprint $table) {
            $table->id();
            $table->foreignId('produit_id')->constrained('produits')->onDelete('cascade');
            $table->enum('type', ['entree', 'sortie', 'ajustement']);
            $table->integer('quantite');
            $table->string('motif');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('boutique_id')->constrained('boutiques')->onDelete('cascade');
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
        Schema::dropIfExists('mouvements_stock');
    }
};
