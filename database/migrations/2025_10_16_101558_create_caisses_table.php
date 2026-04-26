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
        Schema::create('caisses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boutique_id')->constrained('boutiques')->onDelete('cascade');
            $table->decimal('solde_ouverture', 10, 2);
            $table->decimal('solde_fermeture', 10, 2)->nullable();
            $table->date('date');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('statut', ['ouverte', 'fermee'])->default('ouverte');
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
        Schema::dropIfExists('caisses');
    }
};
