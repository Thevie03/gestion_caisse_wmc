<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Boutique;
use App\Models\Produit;

class CreateTestProducts extends Command
{
    protected $signature = 'app:create-test-products';
    protected $description = 'Create test products for existing boutiques.';

    public function handle()
    {
        $this->info('Création de produits de test...');

        $boutiques = Boutique::all();

        if ($boutiques->isEmpty()) {
            $this->error('Aucune boutique trouvée. Veuillez créer des boutiques d\'abord.');
            return Command::FAILURE;
        }

        // Produits pour Cosmetica
        $cosmetica = $boutiques->where('nom', 'Cosmetica')->first();
        if ($cosmetica) {
            $produitsCosmetica = [
                ['nom' => 'Crème hydratante Nivea', 'categorie' => 'Soins du visage', 'prix_achat' => 2500, 'prix_vente' => 3500, 'quantite_stock' => 50],
                ['nom' => 'Shampoing Head & Shoulders', 'categorie' => 'Soins des cheveux', 'prix_achat' => 1800, 'prix_vente' => 2500, 'quantite_stock' => 30],
                ['nom' => 'Parfum Eau de Toilette', 'categorie' => 'Parfums', 'prix_achat' => 8000, 'prix_vente' => 12000, 'quantite_stock' => 15],
                ['nom' => 'Rouge à lèvres L\'Oréal', 'categorie' => 'Maquillage', 'prix_achat' => 3200, 'prix_vente' => 4500, 'quantite_stock' => 25],
                ['nom' => 'Masque facial', 'categorie' => 'Soins du visage', 'prix_achat' => 1500, 'prix_vente' => 2200, 'quantite_stock' => 40],
                ['nom' => 'Dentifrice Colgate', 'categorie' => 'Hygiène buccale', 'prix_achat' => 1200, 'prix_vente' => 1800, 'quantite_stock' => 60],
                ['nom' => 'Déodorant Axe', 'categorie' => 'Hygiène', 'prix_achat' => 2000, 'prix_vente' => 2800, 'quantite_stock' => 35],
                ['nom' => 'Crème solaire SPF 50', 'categorie' => 'Protection solaire', 'prix_achat' => 4000, 'prix_vente' => 5500, 'quantite_stock' => 20],
            ];

            foreach ($produitsCosmetica as $produit) {
                Produit::create([
                    'nom' => $produit['nom'],
                    'categorie' => $produit['categorie'],
                    'prix_achat' => $produit['prix_achat'],
                    'prix_vente' => $produit['prix_vente'],
                    'quantite_stock' => $produit['quantite_stock'],
                    'boutique_id' => $cosmetica->id,
                ]);
            }
            $this->info('Produits Cosmetica créés: ' . count($produitsCosmetica));
        }

        // Produits pour Maison des Abaya
        $abaya = $boutiques->where('nom', 'Maison des Abaya')->first();
        if ($abaya) {
            $produitsAbaya = [
                ['nom' => 'Abaya noire classique', 'categorie' => 'Abayas', 'prix_achat' => 15000, 'prix_vente' => 25000, 'quantite_stock' => 20],
                ['nom' => 'Abaya colorée moderne', 'categorie' => 'Abayas', 'prix_achat' => 18000, 'prix_vente' => 30000, 'quantite_stock' => 15],
                ['nom' => 'Hijab en soie', 'categorie' => 'Hijabs', 'prix_achat' => 5000, 'prix_vente' => 8000, 'quantite_stock' => 30],
                ['nom' => 'Hijab en coton', 'categorie' => 'Hijabs', 'prix_achat' => 3000, 'prix_vente' => 5000, 'quantite_stock' => 50],
                ['nom' => 'Jilbab traditionnel', 'categorie' => 'Jilbabs', 'prix_achat' => 12000, 'prix_vente' => 20000, 'quantite_stock' => 12],
                ['nom' => 'Chaussures fermées', 'categorie' => 'Chaussures', 'prix_achat' => 8000, 'prix_vente' => 12000, 'quantite_stock' => 25],
                ['nom' => 'Sac à main assorti', 'categorie' => 'Accessoires', 'prix_achat' => 6000, 'prix_vente' => 9000, 'quantite_stock' => 18],
                ['nom' => 'Ceinture décorative', 'categorie' => 'Accessoires', 'prix_achat' => 2500, 'prix_vente' => 4000, 'quantite_stock' => 35],
            ];

            foreach ($produitsAbaya as $produit) {
                Produit::create([
                    'nom' => $produit['nom'],
                    'categorie' => $produit['categorie'],
                    'prix_achat' => $produit['prix_achat'],
                    'prix_vente' => $produit['prix_vente'],
                    'quantite_stock' => $produit['quantite_stock'],
                    'boutique_id' => $abaya->id,
                ]);
            }
            $this->info('Produits Maison des Abaya créés: ' . count($produitsAbaya));
        }

        $this->info('Produits de test créés avec succès !');
        $this->info('Total produits: ' . Produit::count());
        return Command::SUCCESS;
    }
}
