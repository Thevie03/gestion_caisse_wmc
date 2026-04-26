<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = [
            [
                'nom' => 'Cosmétiques',
                'icone' => 'fas fa-palette',
                'couleur' => 'primary',
                'description' => 'Produits de beauté et cosmétiques',
                'active' => true,
            ],
            [
                'nom' => 'Vêtements',
                'icone' => 'fas fa-tshirt',
                'couleur' => 'success',
                'description' => 'Vêtements et accessoires de mode',
                'active' => true,
            ],
            [
                'nom' => 'Accessoires',
                'icone' => 'fas fa-gem',
                'couleur' => 'info',
                'description' => 'Accessoires de mode et bijoux',
                'active' => true,
            ],
            [
                'nom' => 'Soins',
                'icone' => 'fas fa-spa',
                'couleur' => 'warning',
                'description' => 'Produits de soins et bien-être',
                'active' => true,
            ],
            [
                'nom' => 'Maquillage',
                'icone' => 'fas fa-paint-brush',
                'couleur' => 'danger',
                'description' => 'Produits de maquillage et beauté',
                'active' => true,
            ],
            [
                'nom' => 'Parfums',
                'icone' => 'fas fa-wind',
                'couleur' => 'secondary',
                'description' => 'Parfums et eaux de toilette',
                'active' => true,
            ],
            [
                'nom' => 'Chaussures',
                'icone' => 'fas fa-shoe-prints',
                'couleur' => 'dark',
                'description' => 'Chaussures et chaussons',
                'active' => true,
            ],
            [
                'nom' => 'Bijoux',
                'icone' => 'fas fa-ring',
                'couleur' => 'light',
                'description' => 'Bijoux et accessoires précieux',
                'active' => true,
            ],
            [
                'nom' => 'Électronique',
                'icone' => 'fas fa-laptop',
                'couleur' => 'primary',
                'description' => 'Appareils électroniques et gadgets',
                'active' => true,
            ],
            [
                'nom' => 'Maison',
                'icone' => 'fas fa-home',
                'couleur' => 'success',
                'description' => 'Articles pour la maison et décoration',
                'active' => true,
            ],
            [
                'nom' => 'Sport',
                'icone' => 'fas fa-dumbbell',
                'couleur' => 'warning',
                'description' => 'Équipements et vêtements de sport',
                'active' => true,
            ],
            [
                'nom' => 'Livre',
                'icone' => 'fas fa-book',
                'couleur' => 'info',
                'description' => 'Livres et publications',
                'active' => true,
            ],
            [
                'nom' => 'Alimentation',
                'icone' => 'fas fa-utensils',
                'couleur' => 'danger',
                'description' => 'Produits alimentaires et boissons',
                'active' => true,
            ],
            [
                'nom' => 'Santé',
                'icone' => 'fas fa-heart',
                'couleur' => 'success',
                'description' => 'Produits de santé et médicaments',
                'active' => true,
            ],
            [
                'nom' => 'Bébé',
                'icone' => 'fas fa-baby',
                'couleur' => 'primary',
                'description' => 'Articles pour bébés et enfants',
                'active' => true,
            ],
            [
                'nom' => 'Autres',
                'icone' => 'fas fa-box',
                'couleur' => 'secondary',
                'description' => 'Autres produits divers',
                'active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}
