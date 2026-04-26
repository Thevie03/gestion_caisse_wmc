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
        Schema::create('abonnements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type_abonnement', ['gratuit', 'mensuel', 'annuel']);
            $table->date('date_debut');
            $table->date('date_expiration');
            $table->enum('statut', ['actif', 'expire', 'suspendu'])->default('actif');
            $table->decimal('montant', 10, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('derniere_notification')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'statut']);
            $table->index('date_expiration');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('abonnements');
    }
};
