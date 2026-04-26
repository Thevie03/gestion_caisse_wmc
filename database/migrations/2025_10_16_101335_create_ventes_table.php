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
        Schema::create('ventes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('boutique_id')->constrained('boutiques')->onDelete('cascade');
            $table->decimal('total', 10, 2);
            $table->decimal('remise', 10, 2)->default(0);
            $table->decimal('total_final', 10, 2);
            $table->enum('mode_paiement', ['especes', 'mobile_money', 'carte']);
            $table->string('numero_vente')->unique();
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('ventes');
    }
};
