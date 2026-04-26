<?php

namespace Database\Seeders;

use App\Models\Taxe;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TaxeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $taxes = [
            [
                'nom' => 'TVA Standard',
                'code' => 'TVA_STD',
                'taux' => 18.00,
                'description' => 'Taxe sur la valeur ajoutée standard',
                'inclus_prix' => false,
                'actif' => true,
            ],
            [
                'nom' => 'TVA Réduite',
                'code' => 'TVA_RED',
                'taux' => 5.00,
                'description' => 'Taxe sur la valeur ajoutée réduite',
                'inclus_prix' => false,
                'actif' => true,
            ],
            [
                'nom' => 'Taxe de consommation',
                'code' => 'TC',
                'taux' => 10.00,
                'description' => 'Taxe de consommation locale',
                'inclus_prix' => true,
                'actif' => true,
            ],
            [
                'nom' => 'Sans taxe',
                'code' => 'SANS_TAXE',
                'taux' => 0.00,
                'description' => 'Produit exonéré de taxe',
                'inclus_prix' => false,
                'actif' => true,
            ],
        ];

        foreach ($taxes as $taxe) {
            Taxe::create($taxe);
        }
    }
}