<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Vente;
use App\Models\VenteDetail;
use App\Models\Produit;
use App\Models\User;
use App\Models\Boutique;

class VenteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $cosmetica = Boutique::where('nom', 'Cosmetica')->first();
        $abaya = Boutique::where('nom', 'Maison des Abaya')->first();

        $employeCosmetica = User::where('boutique_id', $cosmetica->id)->first();
        $employeAbaya = User::where('boutique_id', $abaya->id)->first();

        // Ventes pour Cosmetica
        $ventesCosmetica = [
            [
                'numero_vente' => 'V-COS-001',
                'boutique_id' => $cosmetica->id,
                'user_id' => $employeCosmetica->id,
                'total' => 0, // Sera calculé
                'remise' => 0,
                'total_final' => 0, // Sera calculé
                'mode_paiement' => 'especes',
                'notes' => 'Vente au comptoir',
            ],
            [
                'numero_vente' => 'V-COS-002',
                'boutique_id' => $cosmetica->id,
                'user_id' => $employeCosmetica->id,
                'total' => 0,
                'remise' => 1000,
                'total_final' => 0,
                'mode_paiement' => 'carte',
                'notes' => 'Vente avec remise client fidèle',
            ],
            [
                'numero_vente' => 'V-COS-003',
                'boutique_id' => $cosmetica->id,
                'user_id' => $employeCosmetica->id,
                'total' => 0,
                'remise' => 0,
                'total_final' => 0,
                'mode_paiement' => 'especes',
                'notes' => 'Vente standard',
            ],
        ];

        // Ventes pour Maison des Abaya
        $ventesAbaya = [
            [
                'numero_vente' => 'V-ABA-001',
                'boutique_id' => $abaya->id,
                'user_id' => $employeAbaya->id,
                'total' => 0,
                'remise' => 0,
                'total_final' => 0,
                'mode_paiement' => 'especes',
                'notes' => 'Vente abaya classique',
            ],
            [
                'numero_vente' => 'V-ABA-002',
                'boutique_id' => $abaya->id,
                'user_id' => $employeAbaya->id,
                'total' => 0,
                'remise' => 5000,
                'total_final' => 0,
                'mode_paiement' => 'carte',
                'notes' => 'Vente avec remise événement',
            ],
            [
                'numero_vente' => 'V-ABA-003',
                'boutique_id' => $abaya->id,
                'user_id' => $employeAbaya->id,
                'total' => 0,
                'remise' => 0,
                'total_final' => 0,
                'mode_paiement' => 'especes',
                'notes' => 'Vente standard',
            ],
        ];

        // Créer les ventes
        foreach ($ventesCosmetica as $venteData) {
            $vente = Vente::create($venteData);

            // Ajouter des détails de vente
            $produitsCosmetica = Produit::where('boutique_id', $cosmetica->id)->take(2)->get();

            $montantTotal = 0;
            foreach ($produitsCosmetica as $produit) {
                $quantite = rand(1, 3);
                $prixUnitaire = $produit->prix_vente;
                $sousTotal = $quantite * $prixUnitaire;
                $montantTotal += $sousTotal;

                VenteDetail::create([
                    'vente_id' => $vente->id,
                    'produit_id' => $produit->id,
                    'quantite' => $quantite,
                    'prix_unitaire' => $prixUnitaire,
                    'sous_total' => $sousTotal,
                ]);
            }

            // Mettre à jour les montants de la vente
            $totalFinal = $montantTotal - $vente->remise;

            $vente->update([
                'total' => $montantTotal,
                'total_final' => $totalFinal,
            ]);
        }

        foreach ($ventesAbaya as $venteData) {
            $vente = Vente::create($venteData);

            // Ajouter des détails de vente
            $produitsAbaya = Produit::where('boutique_id', $abaya->id)->take(2)->get();

            $montantTotal = 0;
            foreach ($produitsAbaya as $produit) {
                $quantite = rand(1, 3);
                $prixUnitaire = $produit->prix_vente;
                $sousTotal = $quantite * $prixUnitaire;
                $montantTotal += $sousTotal;

                VenteDetail::create([
                    'vente_id' => $vente->id,
                    'produit_id' => $produit->id,
                    'quantite' => $quantite,
                    'prix_unitaire' => $prixUnitaire,
                    'sous_total' => $sousTotal,
                ]);
            }

            // Mettre à jour les montants de la vente
            $totalFinal = $montantTotal - $vente->remise;

            $vente->update([
                'total' => $montantTotal,
                'total_final' => $totalFinal,
            ]);
        }
    }
}
