<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Boutique>
 */
class BoutiqueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'nom' => $this->faker->company,
            'description' => $this->faker->sentence,
            'adresse' => $this->faker->streetAddress,
            'telephone' => $this->faker->phoneNumber,
            'email' => $this->faker->companyEmail,
            'devise' => 'FCFA',
            'actif' => true,
        ];
    }
}
