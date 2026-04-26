<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Depense;
use App\Models\Boutique;
use App\Models\User;

class DepenseSeeder extends Seeder
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

        // Dépenses pour Cosmetica
        $depensesCosmetica = [
            [
                'boutique_id' => $cosmetica->id,
                'user_id' => $employeCosmetica->id,
                'categorie' => 'Frais généraux',
                'description' => 'Paiement facture électricité',
                'montant' => 25000,
                'date_depense' => now()->subDays(10),
                'notes' => 'Facture électricité du mois',
            ],
            [
                'boutique_id' => $cosmetica->id,
                'user_id' => $employeCosmetica->id,
                'categorie' => 'Marketing',
                'description' => 'Publicité sur les réseaux sociaux',
                'montant' => 15000,
                'date_depense' => now()->subDays(8),
                'notes' => 'Campagne publicitaire Facebook et Instagram',
            ],
            [
                'boutique_id' => $cosmetica->id,
                'user_id' => $employeCosmetica->id,
                'categorie' => 'Maintenance',
                'description' => 'Réparation climatiseur',
                'montant' => 45000,
                'date_depense' => now()->subDays(6),
                'notes' => 'Réparation climatiseur principal',
            ],
            [
                'boutique_id' => $cosmetica->id,
                'user_id' => $employeCosmetica->id,
                'categorie' => 'Transport',
                'description' => 'Frais de livraison produits',
                'montant' => 8000,
                'date_depense' => now()->subDays(4),
                'notes' => 'Livraison de nouveaux produits',
            ],
            [
                'boutique_id' => $cosmetica->id,
                'user_id' => $employeCosmetica->id,
                'categorie' => 'Frais généraux',
                'description' => 'Achat fournitures de bureau',
                'montant' => 12000,
                'date_depense' => now()->subDays(2),
                'notes' => 'Papeterie et fournitures diverses',
            ],
        ];

        // Dépenses pour Maison des Abaya
        $depensesAbaya = [
            [
                'boutique_id' => $abaya->id,
                'user_id' => $employeAbaya->id,
                'categorie' => 'Frais généraux',
                'description' => 'Paiement loyer boutique',
                'montant' => 150000,
                'date_depense' => now()->subDays(12),
                'notes' => 'Loyer mensuel de la boutique',
            ],
            [
                'boutique_id' => $abaya->id,
                'user_id' => $employeAbaya->id,
                'categorie' => 'Marketing',
                'description' => 'Campagne publicitaire radio',
                'montant' => 75000,
                'date_depense' => now()->subDays(9),
                'notes' => 'Publicité radio pour promotion',
            ],
            [
                'boutique_id' => $abaya->id,
                'user_id' => $employeAbaya->id,
                'categorie' => 'Transport',
                'description' => 'Frais de transport marchandises',
                'montant' => 25000,
                'date_depense' => now()->subDays(7),
                'notes' => 'Transport de nouveaux vêtements',
            ],
            [
                'boutique_id' => $abaya->id,
                'user_id' => $employeAbaya->id,
                'categorie' => 'Maintenance',
                'description' => 'Réparation machine à coudre',
                'montant' => 35000,
                'date_depense' => now()->subDays(5),
                'notes' => 'Réparation machine à coudre industrielle',
            ],
            [
                'boutique_id' => $abaya->id,
                'user_id' => $employeAbaya->id,
                'categorie' => 'Frais généraux',
                'description' => 'Achat matériel de couture',
                'montant' => 18000,
                'date_depense' => now()->subDays(3),
                'notes' => 'Fils, aiguilles et accessoires de couture',
            ],
        ];

        // Créer les dépenses
        foreach ($depensesCosmetica as $depense) {
            Depense::create($depense);
        }

        foreach ($depensesAbaya as $depense) {
            Depense::create($depense);
        }
    }
}
