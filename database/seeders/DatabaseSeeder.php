<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            BoutiqueSeeder::class,
            UserSeeder::class,
            CategorySeeder::class,
            TaxeSeeder::class,
            ProduitSeeder::class,
            VenteSeeder::class,
            DepenseSeeder::class,
            MouvementStockSeeder::class,
        ]);
    }
}
