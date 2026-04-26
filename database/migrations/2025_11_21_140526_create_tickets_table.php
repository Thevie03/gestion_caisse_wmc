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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Qui a créé le ticket
            $table->foreignId('boutique_id')->constrained()->onDelete('cascade'); // Boutique concernée
            $table->string('sujet'); // Titre du problème
            $table->text('message'); // Description détaillée
            $table->enum('statut', ['ouvert', 'en_cours', 'resolu', 'ferme'])->default('ouvert');
            $table->enum('priorite', ['faible', 'normale', 'elevee', 'urgente'])->default('normale');
            $table->text('reponse')->nullable(); // Réponse du super admin
            $table->timestamp('reponse_at')->nullable(); // Date de réponse
            $table->foreignId('reponse_par')->nullable()->constrained('users')->onDelete('set null'); // Qui a répondu
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
        Schema::dropIfExists('tickets');
    }
};
