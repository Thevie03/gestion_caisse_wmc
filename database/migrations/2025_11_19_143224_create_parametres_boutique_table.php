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
        Schema::create('parametres_boutique', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('boutique_id')->nullable()->constrained('boutiques')->cascadeOnDelete();
            $table->string('nom_affichage');
            $table->string('logo_path')->nullable();
            $table->string('devise', 10)->default('FCFA');
            $table->string('timezone')->default('Africa/Dakar');
            $table->json('options')->nullable();
            $table->boolean('notifications_stock')->default(true);
            $table->boolean('notifications_abonnement')->default(true);
            $table->timestamps();

            $table->unique(['user_id', 'boutique_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('parametres_boutique');
    }
};
