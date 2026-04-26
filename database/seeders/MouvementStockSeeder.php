<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\MouvementStock;
use App\Models\Produit;
use App\Models\Boutique;
use App\Models\User;

class MouvementStockSeeder extends Seeder
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

        // Mouvements pour Cosmetica
        $produitsCosmetica = Produit::where('boutique_id', $cosmetica->id)->get();

        foreach ($produitsCosmetica as $produit) {
            // Entrée de stock (réapprovisionnement)
            MouvementStock::create([
                'produit_id' => $produit->id,
                'boutique_id' => $cosmetica->id,
                'user_id' => $employeCosmetica->id,
                'type' => 'entree',
                'quantite' => rand(20, 50),
                'motif' => 'Réapprovisionnement stock',
            ]);

            // Sortie de stock (vente)
            MouvementStock::create([
                'produit_id' => $produit->id,
                'boutique_id' => $cosmetica->id,
                'user_id' => $employeCosmetica->id,
                'type' => 'sortie',
                'quantite' => rand(5, 15),
                'motif' => 'Vente au détail',
            ]);

            // Ajustement de stock (inventaire)
            if (rand(0, 1)) {
                MouvementStock::create([
                    'produit_id' => $produit->id,
                    'boutique_id' => $cosmetica->id,
                    'user_id' => $employeCosmetica->id,
                    'type' => rand(0, 1) ? 'entree' : 'sortie',
                    'quantite' => rand(1, 5),
                    'motif' => 'Ajustement inventaire',
                ]);
            }
        }

        // Mouvements pour Maison des Abaya
        $produitsAbaya = Produit::where('boutique_id', $abaya->id)->get();

        foreach ($produitsAbaya as $produit) {
            // Entrée de stock (réapprovisionnement)
            MouvementStock::create([
                'produit_id' => $produit->id,
                'boutique_id' => $abaya->id,
                'user_id' => $employeAbaya->id,
                'type' => 'entree',
                'quantite' => rand(10, 25),
                'motif' => 'Réapprovisionnement stock',
            ]);

            // Sortie de stock (vente)
            MouvementStock::create([
                'produit_id' => $produit->id,
                'boutique_id' => $abaya->id,
                'user_id' => $employeAbaya->id,
                'type' => 'sortie',
                'quantite' => rand(2, 8),
                'motif' => 'Vente au détail',
            ]);

            // Ajustement de stock (inventaire)
            if (rand(0, 1)) {
                MouvementStock::create([
                    'produit_id' => $produit->id,
                    'boutique_id' => $abaya->id,
                    'user_id' => $employeAbaya->id,
                    'type' => rand(0, 1) ? 'entree' : 'sortie',
                    'quantite' => rand(1, 3),
                    'motif' => 'Ajustement inventaire',
                ]);
            }
        }

        // Quelques mouvements de transfert entre boutiques (si applicable)
        if ($produitsCosmetica->count() > 0 && $produitsAbaya->count() > 0) {
            $produitTransfert = $produitsCosmetica->first();

            MouvementStock::create([
                'produit_id' => $produitTransfert->id,
                'boutique_id' => $cosmetica->id,
                'user_id' => $employeCosmetica->id,
                'type' => 'sortie',
                'quantite' => 5,
                'motif' => 'Transfert vers autre boutique',
            ]);
        }
    }
}
