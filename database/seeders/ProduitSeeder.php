<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Produit;
use App\Models\Boutique;

class ProduitSeeder extends Seeder
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

        // Produits pour Cosmetica
        $produitsCosmetica = [
            [
                'nom' => 'Crème hydratante Nivea',
                'categorie' => 'Soins',
                'description' => 'Crème hydratante pour le visage et le corps',
                'prix_achat' => 2500,
                'prix_vente' => 3500,
                'quantite_stock' => 50,
                'stock_minimum' => 10,
                'boutique_id' => $cosmetica->id,
                'actif' => true,
            ],
            [
                'nom' => 'Rouge à lèvres MAC',
                'categorie' => 'Maquillage',
                'description' => 'Rouge à lèvres longue tenue',
                'prix_achat' => 8000,
                'prix_vente' => 12000,
                'quantite_stock' => 25,
                'stock_minimum' => 5,
                'boutique_id' => $cosmetica->id,
                'actif' => true,
            ],
            [
                'nom' => 'Parfum Chanel N°5',
                'categorie' => 'Parfums',
                'description' => 'Parfum féminin iconique',
                'prix_achat' => 45000,
                'prix_vente' => 65000,
                'quantite_stock' => 8,
                'stock_minimum' => 3,
                'boutique_id' => $cosmetica->id,
                'actif' => true,
            ],
            [
                'nom' => 'Masque facial hydratant',
                'categorie' => 'Soins',
                'description' => 'Masque facial pour peau sèche',
                'prix_achat' => 1500,
                'prix_vente' => 2500,
                'quantite_stock' => 2, // Stock faible
                'stock_minimum' => 5,
                'boutique_id' => $cosmetica->id,
                'actif' => true,
            ],
            [
                'nom' => 'Fond de teint L\'Oréal',
                'categorie' => 'Maquillage',
                'description' => 'Fond de teint longue tenue',
                'prix_achat' => 6000,
                'prix_vente' => 9000,
                'quantite_stock' => 0, // En rupture
                'stock_minimum' => 8,
                'boutique_id' => $cosmetica->id,
                'actif' => true,
            ],
        ];

        // Produits pour Maison des Abaya
        $produitsAbaya = [
            [
                'nom' => 'Abaya noire classique',
                'categorie' => 'Vêtements',
                'description' => 'Abaya noire en coton de qualité',
                'prix_achat' => 15000,
                'prix_vente' => 25000,
                'quantite_stock' => 30,
                'stock_minimum' => 10,
                'boutique_id' => $abaya->id,
                'actif' => true,
            ],
            [
                'nom' => 'Abaya brodée dorée',
                'categorie' => 'Vêtements',
                'description' => 'Abaya avec broderie dorée élégante',
                'prix_achat' => 25000,
                'prix_vente' => 40000,
                'quantite_stock' => 15,
                'stock_minimum' => 5,
                'boutique_id' => $abaya->id,
                'actif' => true,
            ],
            [
                'nom' => 'Hijab en soie',
                'categorie' => 'Accessoires',
                'description' => 'Hijab en soie de qualité supérieure',
                'prix_achat' => 8000,
                'prix_vente' => 15000,
                'quantite_stock' => 40,
                'stock_minimum' => 15,
                'boutique_id' => $abaya->id,
                'actif' => true,
            ],
            [
                'nom' => 'Abaya de soirée',
                'categorie' => 'Vêtements',
                'description' => 'Abaya élégante pour occasions spéciales',
                'prix_achat' => 35000,
                'prix_vente' => 55000,
                'quantite_stock' => 5,
                'stock_minimum' => 3,
                'boutique_id' => $abaya->id,
                'actif' => true,
            ],
            [
                'nom' => 'Chaussures assorties',
                'categorie' => 'Accessoires',
                'description' => 'Chaussures assorties aux abayas',
                'prix_achat' => 12000,
                'prix_vente' => 20000,
                'quantite_stock' => 1, // Stock faible
                'stock_minimum' => 5,
                'boutique_id' => $abaya->id,
                'actif' => true,
            ],
        ];

        // Créer les produits
        foreach ($produitsCosmetica as $produit) {
            $produit['code_produit'] = 'PRD-' . strtoupper(uniqid());
            Produit::create($produit);
        }

        foreach ($produitsAbaya as $produit) {
            $produit['code_produit'] = 'PRD-' . strtoupper(uniqid());
            Produit::create($produit);
        }
    }
}
