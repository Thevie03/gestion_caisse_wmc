<?php

namespace Database\Factories;

use App\Models\Boutique;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Produit>
 */
class ProduitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'nom' => $this->faker->words(3, true),
            'categorie' => $this->faker->randomElement(['Mode', 'Beauté', 'Alimentation']),
            'description' => $this->faker->sentence,
            'prix_achat' => $this->faker->randomFloat(2, 1000, 10000),
            'prix_vente' => $this->faker->randomFloat(2, 2000, 20000),
            'quantite_stock' => $this->faker->numberBetween(1, 50),
            'stock_minimum' => 5,
            'code_produit' => $this->faker->unique()->ean13(),
            'boutique_id' => Boutique::factory(),
            'user_id' => User::factory(),
            'actif' => true,
        ];
    }
}
