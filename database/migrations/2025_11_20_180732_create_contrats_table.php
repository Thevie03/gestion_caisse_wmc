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
        Schema::create('contrats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('boutique_id')->constrained('boutiques')->onDelete('cascade');
            $table->string('numero_contrat')->unique();
            $table->enum('type_contrat', ['standard', 'premium', 'entreprise', 'personnalise'])->default('standard');
            $table->date('date_signature');
            $table->date('date_expiration')->nullable();
            $table->string('fichier_contrat'); // Chemin du fichier uploadé
            $table->enum('statut', ['actif', 'expire', 'resilie', 'en_attente'])->default('actif');
            $table->decimal('montant', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index('boutique_id');
            $table->index('statut');
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
        Schema::dropIfExists('contrats');
    }
};
