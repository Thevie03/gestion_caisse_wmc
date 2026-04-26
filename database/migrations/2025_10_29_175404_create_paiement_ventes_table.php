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
        Schema::create('paiement_ventes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vente_id')->constrained('ventes')->onDelete('cascade');
            $table->decimal('montant', 10, 2);
            $table->enum('mode_paiement', ['especes', 'mobile_money', 'carte']);
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
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
        Schema::dropIfExists('paiement_ventes');
    }
};
