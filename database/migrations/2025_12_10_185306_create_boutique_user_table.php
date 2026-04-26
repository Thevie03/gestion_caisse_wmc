<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Cette table pivot permet à un propriétaire d'avoir plusieurs boutiques
     * et à une boutique d'avoir plusieurs propriétaires (si nécessaire)
     */
    public function up()
    {
        Schema::create('boutique_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boutique_id')->constrained('boutiques')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->boolean('is_primary')->default(false)->comment('Boutique principale du propriétaire');
            $table->timestamps();

            // Index unique pour éviter les doublons
            $table->unique(['boutique_id', 'user_id']);

            // Index pour améliorer les performances
            $table->index('user_id');
            $table->index('boutique_id');
            $table->index('is_primary');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('boutique_user');
    }
};
