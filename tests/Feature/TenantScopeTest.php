<?php

namespace Tests\Feature;

use App\Models\Boutique;
use App\Models\Produit;
use App\Models\User;
use App\Support\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_produits_sont_filtres_par_utilisateur_connecte(): void
    {
        $boutiqueA = Boutique::factory()->create();
        $boutiqueB = Boutique::factory()->create();

        $userA = User::factory()->create([
            'role' => 'employe',
            'boutique_id' => $boutiqueA->id,
        ]);

        $userB = User::factory()->create([
            'role' => 'employe',
            'boutique_id' => $boutiqueB->id,
        ]);

        Produit::factory()->count(2)->create([
            'user_id' => $userA->id,
            'boutique_id' => $boutiqueA->id,
        ]);

        Produit::factory()->create([
            'user_id' => $userB->id,
            'boutique_id' => $boutiqueB->id,
        ]);

        /** @var TenantContext $tenantContext */
        $tenantContext = app(TenantContext::class);
        $tenantContext->setFromUser($userA);

        $this->assertEquals(2, Produit::count());
    }
}
