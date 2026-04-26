<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Boutique;
use App\Models\Produit;
use App\Models\Vente;
use App\Models\VenteDetail;
use Illuminate\Support\Facades\DB;

class CreateTestSales extends Command
{
    protected $signature = 'app:create-test-sales';
    protected $description = 'Create test sales for existing boutiques, products, and employees.';

    public function handle()
    {
        $this->info('Création de ventes de test...');

        $boutiques = Boutique::all();
        $produits = Produit::all();
        $users = User::where('role', 'employe')->get(); // Only employees make sales

        if ($boutiques->isEmpty() || $produits->isEmpty() || $users->isEmpty()) {
            $this->error('Données manquantes. Assurez-vous que les boutiques, produits et employés existent.');
            return Command::FAILURE;
        }

        $modesPaiement = ['especes', 'wave', 'orange_money', 'mtn_money', 'carte'];
        $clients = [
            ['nom' => 'Fatou Diop', 'telephone' => '771234567', 'email' => 'fatou@example.com'],
            ['nom' => 'Moussa Fall', 'telephone' => '709876543', 'email' => 'moussa@example.com'],
            ['nom' => 'Aïcha Diallo', 'telephone' => '765432109', 'email' => 'aicha@example.com'],
            ['nom' => 'Client Anonyme', 'telephone' => null, 'email' => null],
        ];

        for ($i = 1; $i <= 10; $i++) { // Créer 10 ventes de test
            DB::beginTransaction();
            try {
                $user = $users->random();
                $boutique = $user->boutique; // Vente faite par un employé de sa boutique
                if (!$boutique) {
                    $this->warn("L'employé {$user->name} n'a pas de boutique assignée. Skipping sale creation.");
                    DB::rollBack();
                    continue;
                }

                $client = $clients[array_rand($clients)];
                $modePaiement = $modesPaiement[array_rand($modesPaiement)];

                // Générer le numéro de facture
                $numeroFacture = 'FAC' . date('Y') . date('m') . str_pad($i, 4, '0', STR_PAD_LEFT);

                // Créer la vente
                $remiseGlobale = rand(0, 1) ? rand(500, 2000) : 0;
                $vente = Vente::create([
                    'numero_facture' => $numeroFacture,
                    'user_id' => $user->id,
                    'boutique_id' => $boutique->id,
                    'nom_client' => $client['nom'],
                    'telephone_client' => $client['telephone'],
                    'email_client' => $client['email'],
                    'mode_paiement' => $modePaiement,
                    'remise_globale' => $remiseGlobale,
                    'total' => 0, // Sera calculé après les détails
                    'remise' => $remiseGlobale,
                    'total_final' => 0, // Sera calculé après les détails
                    'numero_vente' => 'V' . date('Ymd') . '-' . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'created_at' => now()->subDays(rand(0, 30))->subHours(rand(0, 23)),
                ]);

                // Ajouter 1 à 4 produits par vente
                $nbProduits = rand(1, 4);
                $produitsVente = $produits->where('boutique_id', $boutique->id)->random($nbProduits);
                $totalVente = 0;

                foreach ($produitsVente as $produit) {
                    $quantite = rand(1, 5);
                    $prixUnitaire = $produit->prix_vente;
                    $remiseProduit = rand(0, 1) ? rand(50, 200) : 0;
                    $sousTotal = ($prixUnitaire * $quantite) - $remiseProduit;

                    VenteDetail::create([
                        'vente_id' => $vente->id,
                        'produit_id' => $produit->id,
                        'quantite' => $quantite,
                        'prix_unitaire' => $prixUnitaire,
                        'remise' => $remiseProduit,
                        'sous_total' => $sousTotal,
                    ]);

                    $totalVente += $sousTotal;
                }

                // Appliquer la remise globale
                $totalFinal = $totalVente - $vente->remise_globale;
                $vente->update([
                    'total' => $totalVente,
                    'total_final' => $totalFinal
                ]);

                // Mettre à jour le stock des produits
                foreach ($produitsVente as $produit) {
                    $detail = VenteDetail::where('vente_id', $vente->id)
                        ->where('produit_id', $produit->id)
                        ->first();

                    $produit->decrement('quantite_stock', $detail->quantite);
                }

                DB::commit();
                $this->info("Vente {$i} créée: {$numeroFacture} - {$client['nom']} - {$totalFinal} FCFA");

            } catch (\Exception $e) {
                DB::rollBack();
                $this->error("Erreur lors de la création de la vente {$i}: " . $e->getMessage());
            }
        }

        $this->info('Ventes de test créées avec succès !');
        $this->info('Vous pouvez maintenant tester le module Facturation.');
        return Command::SUCCESS;
    }
}
