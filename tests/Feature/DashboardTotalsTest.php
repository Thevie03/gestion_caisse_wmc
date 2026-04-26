<?php

namespace Tests\Feature;

use App\Models\Boutique;
use App\Models\Category;
use App\Models\Depense;
use App\Models\Produit;
use App\Models\User;
use App\Models\Vente;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTotalsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_voit_les_totaux_filtrés_par_boutique_active()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $boutiqueA = Boutique::create([
            'nom' => 'Boutique A',
            'description' => 'Test A',
        ]);

        $boutiqueB = Boutique::create([
            'nom' => 'Boutique B',
            'description' => 'Test B',
        ]);

        $categorieA = Category::create([
            'nom' => 'Catégorie A',
        ]);

        $categorieB = Category::create([
            'nom' => 'Catégorie B',
        ]);

        Produit::create([
            'nom' => 'Produit A1',
            'categorie' => $categorieA->nom,
            'prix_achat' => 500,
            'prix_vente' => 1000,
            'quantite_stock' => 10,
            'stock_minimum' => 2,
            'boutique_id' => $boutiqueA->id,
            'actif' => true,
        ]);

        Produit::create([
            'nom' => 'Produit A2',
            'categorie' => $categorieB->nom,
            'prix_achat' => 200,
            'prix_vente' => 400,
            'quantite_stock' => 5,
            'stock_minimum' => 1,
            'boutique_id' => $boutiqueA->id,
            'actif' => true,
        ]);

        // Produits dans une autre boutique pour vérifier le filtrage
        Produit::create([
            'nom' => 'Produit B1',
            'categorie' => $categorieB->nom,
            'prix_achat' => 300,
            'prix_vente' => 600,
            'quantite_stock' => 3,
            'stock_minimum' => 1,
            'boutique_id' => $boutiqueB->id,
            'actif' => true,
        ]);

        Vente::create([
            'user_id' => $admin->id,
            'boutique_id' => $boutiqueA->id,
            'total' => 1500,
            'remise' => 0,
            'total_final' => 1500,
            'montant_paye' => 1500,
            'solde_restant' => 0,
            'statut_paiement' => 'complet',
            'mode_paiement' => 'especes',
            'notes' => null,
        ]);

        Vente::create([
            'user_id' => $admin->id,
            'boutique_id' => $boutiqueB->id,
            'total' => 800,
            'remise' => 0,
            'total_final' => 800,
            'montant_paye' => 800,
            'solde_restant' => 0,
            'statut_paiement' => 'complet',
            'mode_paiement' => 'especes',
            'notes' => null,
        ]);

        Depense::create([
            'description' => 'Dépense A',
            'categorie' => 'Charges',
            'montant' => 300,
            'boutique_id' => $boutiqueA->id,
            'user_id' => $admin->id,
            'date_depense' => Carbon::now()->toDateString(),
        ]);

        Depense::create([
            'description' => 'Dépense B',
            'categorie' => 'Charges',
            'montant' => 200,
            'boutique_id' => $boutiqueB->id,
            'user_id' => $admin->id,
            'date_depense' => Carbon::now()->toDateString(),
        ]);

        $response = $this->actingAs($admin)
            ->withSession(['boutique_active' => $boutiqueA->id])
            ->get(route('dashboard'));

        $response->assertStatus(200);

        $response->assertViewHas('totaux', function (array $totaux) {
            return $totaux['chiffre_affaires'] == 1500
                && $totaux['depenses_totales'] == 300
                && $totaux['nombre_ventes'] == 1
                && $totaux['nombre_produits'] == 2
                && $totaux['nombre_categories'] == 2;
        });
    }
}














