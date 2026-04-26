<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Abonnement;
use App\Models\User;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Abonnement>
 */
class AbonnementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $dateDebut = Carbon::now()->subDays(rand(1, 10));
        $dateExpiration = (clone $dateDebut)->addMonth();

        return [
            'user_id' => User::factory(),
            'type_abonnement' => Abonnement::TYPE_MENSUEL,
            'date_debut' => $dateDebut,
            'date_expiration' => $dateExpiration,
            'statut' => Abonnement::STATUT_ACTIF,
            'montant' => $this->faker->randomFloat(2, 10000, 50000),
        ];
    }
}
