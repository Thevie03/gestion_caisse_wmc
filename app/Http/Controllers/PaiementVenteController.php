<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Vente;
use App\Models\PaiementVente;
use Illuminate\Support\Facades\DB;

class PaiementVenteController extends Controller
{
    /**
     * Store a newly created paiement.
     */
    public function store(Request $request, Vente $vente)
    {
        $request->validate([
            'montant' => 'required|numeric|min:0.01',
            'mode_paiement' => 'required|in:especes,wave,orange_money,mtn_money,carte,mobile_money',
            'notes' => 'nullable|string|max:500'
        ]);

        // Vérifier que le montant n'excède pas le solde restant
        $soldeRestant = $vente->getSoldeRestant();

        if ($request->montant > $soldeRestant) {
            return back()->with('error', 'Le montant du paiement partiel ne peut pas dépasser le solde restant de ' . number_format($soldeRestant, 2) . ' FCFA')->withInput();
        }

        DB::beginTransaction();
        try {
            // Créer le paiement
            $paiement = PaiementVente::create([
                'vente_id' => $vente->id,
                'montant' => $request->montant,
                'mode_paiement' => $request->mode_paiement,
                'notes' => $request->notes,
                'user_id' => auth()->id(),
            ]);

            // Mettre à jour le solde de la vente
            $montantTotalPaye = $vente->getMontantTotalPaye();
            $nouveauSolde = $vente->total_final - $montantTotalPaye;

            // Déterminer le statut
            $statut = 'complet';
            if ($nouveauSolde > 0) {
                $statut = 'partiel';
            }

            $vente->update([
                'montant_paye' => $montantTotalPaye,
                'solde_restant' => $nouveauSolde,
                'statut_paiement' => $statut
            ]);

            DB::commit();

            return redirect()->route('ventes.show', $vente)
                ->with('success', 'Paiement enregistré avec succès ! Solde restant: ' . number_format($nouveauSolde, 2) . ' FCFA');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Erreur lors de l\'enregistrement du paiement: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified paiement.
     */
    public function destroy(PaiementVente $paiementVente)
    {
        $vente = $paiementVente->vente;

        DB::beginTransaction();
        try {
            // Supprimer le paiement
            $paiementVente->delete();

            // Recalculer les soldes
            $montantTotalPaye = $vente->getMontantTotalPaye();
            $nouveauSolde = $vente->total_final - $montantTotalPaye;

            // Déterminer le statut
            $statut = 'complet';
            if ($nouveauSolde > 0) {
                $statut = 'partiel';
            } else if ($montantTotalPaye == 0) {
                $statut = 'impaye';
            }

            $vente->update([
                'montant_paye' => $montantTotalPaye,
                'solde_restant' => $nouveauSolde,
                'statut_paiement' => $statut
            ]);

            DB::commit();

            return redirect()->route('ventes.show', $vente)
                ->with('success', 'Paiement supprimé avec succès !');

        } catch (\Exception $e) {
            DB::rollback();
            return back()->with('error', 'Erreur lors de la suppression du paiement: ' . $e->getMessage());
        }
    }
}
