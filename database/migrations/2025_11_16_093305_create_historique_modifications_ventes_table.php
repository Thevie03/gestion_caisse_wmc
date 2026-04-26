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
        Schema::create('historique_modifications_ventes', function (Blueprint $table) {
            $table->id();
            $table->string('type_action'); // 'modification' ou 'suppression'
            $table->foreignId('vente_id')->nullable()->constrained('ventes')->onDelete('set null');
            $table->string('numero_vente'); // Conserver le numéro même si la vente est supprimée
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('boutique_id')->nullable()->constrained('boutiques')->onDelete('set null');

            // Données de la vente avant modification/suppression (JSON)
            $table->json('donnees_avant')->nullable();

            // Données après modification (JSON) - null pour les suppressions
            $table->json('donnees_apres')->nullable();

            // Détails des changements
            $table->text('changements')->nullable(); // Description textuelle des changements

            // Informations sur les produits modifiés
            $table->json('produits_modifies')->nullable();

            // Raison/motif (optionnel)
            $table->text('motif')->nullable();

            $table->timestamps();

            // Index pour améliorer les performances
            $table->index('vente_id');
            $table->index('user_id');
            $table->index('type_action');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('historique_modifications_ventes');
    }
};
