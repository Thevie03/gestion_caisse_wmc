<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Exécuter la migration.
     */
    public function up(): void
    {
        $ventesSansPaiements = DB::table('ventes')
            ->leftJoin('paiement_ventes', 'ventes.id', '=', 'paiement_ventes.vente_id')
            ->select('ventes.id', 'ventes.user_id', 'ventes.mode_paiement', 'ventes.montant_paye', 'ventes.total_final')
            ->whereNull('paiement_ventes.id')
            ->where('ventes.montant_paye', '>', 0)
            ->get();

        foreach ($ventesSansPaiements as $vente) {
            DB::table('paiement_ventes')->insert([
                'vente_id' => $vente->id,
                'montant' => $vente->montant_paye ?? $vente->total_final,
                'mode_paiement' => $vente->mode_paiement ?? 'especes',
                'notes' => 'Paiement importé (migration)',
                'user_id' => $vente->user_id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Annuler la migration.
     */
    public function down(): void
    {
        // Rien à annuler : les paiements importés doivent rester pour l'historique
    }
};






