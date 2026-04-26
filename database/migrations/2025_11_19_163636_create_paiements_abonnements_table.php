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
        Schema::create('paiements_abonnements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('abonnement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->decimal('montant', 10, 2);
            $table->enum('mode_paiement', ['especes', 'mobile_money', 'carte_bancaire', 'virement', 'cheque'])->default('mobile_money');
            $table->enum('statut', ['en_attente', 'confirme', 'refuse', 'rembourse'])->default('en_attente');
            $table->date('date_paiement');
            $table->text('reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('confirme_par')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('confirme_le')->nullable();
            $table->timestamps();

            $table->index(['abonnement_id', 'statut']);
            $table->index('date_paiement');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('paiements_abonnements');
    }
};
