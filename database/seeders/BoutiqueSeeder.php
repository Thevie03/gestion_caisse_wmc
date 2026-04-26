<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class BoutiqueSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \App\Models\Boutique::create([
            'nom' => 'Cosmetica',
            'description' => 'Boutique de produits cosmétiques',
            'adresse' => '123 Rue de la Beauté, Dakar',
            'telephone' => '+221 33 123 45 67',
            'email' => 'cosmetica@example.com',
            'actif' => true,
        ]);

        \App\Models\Boutique::create([
            'nom' => 'Maison des Abaya',
            'description' => 'Boutique de vente d\'abayas et de vêtements',
            'adresse' => '456 Avenue de la Mode, Dakar',
            'telephone' => '+221 33 987 65 43',
            'email' => 'abaya@example.com',
            'actif' => true,
        ]);
    }
}
