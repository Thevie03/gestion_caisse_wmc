<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE ventes MODIFY mode_paiement ENUM('especes','wave','orange_money','mtn_money','mobile_money','carte') NOT NULL");
        DB::statement("ALTER TABLE paiement_ventes MODIFY mode_paiement ENUM('especes','wave','orange_money','mtn_money','mobile_money','carte') NOT NULL");
        DB::statement("ALTER TABLE paiements_abonnements MODIFY mode_paiement ENUM('especes','wave','orange_money','mtn_money','mobile_money','carte_bancaire','virement','cheque') NOT NULL DEFAULT 'wave'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE ventes MODIFY mode_paiement ENUM('especes','mobile_money','carte') NOT NULL");
        DB::statement("ALTER TABLE paiement_ventes MODIFY mode_paiement ENUM('especes','mobile_money','carte') NOT NULL");
        DB::statement("ALTER TABLE paiements_abonnements MODIFY mode_paiement ENUM('especes','mobile_money','carte_bancaire','virement','cheque') NOT NULL DEFAULT 'mobile_money'");
    }
};
