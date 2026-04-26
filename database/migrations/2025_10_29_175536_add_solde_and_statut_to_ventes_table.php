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
        Schema::table('ventes', function (Blueprint $table) {
            $table->decimal('montant_paye', 10, 2)->default(0)->after('total_final');
            $table->decimal('solde_restant', 10, 2)->default(0)->after('montant_paye');
            $table->enum('statut_paiement', ['complet', 'partiel', 'impaye'])->default('complet')->after('solde_restant');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ventes', function (Blueprint $table) {
            $table->dropColumn(['montant_paye', 'solde_restant', 'statut_paiement']);
        });
    }
};
