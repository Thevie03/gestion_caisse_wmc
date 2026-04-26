<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            // Module Produits
            [
                'nom' => 'produits.view',
                'description' => 'Voir les produits',
                'module' => 'produits',
                'action' => 'read',
                'active' => true,
            ],
            [
                'nom' => 'produits.create',
                'description' => 'Créer des produits',
                'module' => 'produits',
                'action' => 'create',
                'active' => true,
            ],
            [
                'nom' => 'produits.edit',
                'description' => 'Modifier les produits',
                'module' => 'produits',
                'action' => 'update',
                'active' => true,
            ],
            [
                'nom' => 'produits.delete',
                'description' => 'Supprimer les produits',
                'module' => 'produits',
                'action' => 'delete',
                'active' => true,
            ],

            // Module Ventes
            [
                'nom' => 'ventes.view',
                'description' => 'Voir les ventes',
                'module' => 'ventes',
                'action' => 'read',
                'active' => true,
            ],
            [
                'nom' => 'ventes.create',
                'description' => 'Créer des ventes',
                'module' => 'ventes',
                'action' => 'create',
                'active' => true,
            ],
            [
                'nom' => 'ventes.edit',
                'description' => 'Modifier les ventes',
                'module' => 'ventes',
                'action' => 'update',
                'active' => true,
            ],
            [
                'nom' => 'ventes.delete',
                'description' => 'Supprimer les ventes',
                'module' => 'ventes',
                'action' => 'delete',
                'active' => true,
            ],

            // Module Stock
            [
                'nom' => 'stock.view',
                'description' => 'Voir le stock',
                'module' => 'stock',
                'action' => 'read',
                'active' => true,
            ],
            [
                'nom' => 'stock.create',
                'description' => 'Ajouter des mouvements de stock',
                'module' => 'stock',
                'action' => 'create',
                'active' => true,
            ],
            [
                'nom' => 'stock.edit',
                'description' => 'Modifier le stock',
                'module' => 'stock',
                'action' => 'update',
                'active' => true,
            ],

            // Module Dépenses
            [
                'nom' => 'depenses.view',
                'description' => 'Voir les dépenses',
                'module' => 'depenses',
                'action' => 'read',
                'active' => true,
            ],
            [
                'nom' => 'depenses.create',
                'description' => 'Créer des dépenses',
                'module' => 'depenses',
                'action' => 'create',
                'active' => true,
            ],
            [
                'nom' => 'depenses.edit',
                'description' => 'Modifier les dépenses',
                'module' => 'depenses',
                'action' => 'update',
                'active' => true,
            ],
            [
                'nom' => 'depenses.delete',
                'description' => 'Supprimer les dépenses',
                'module' => 'depenses',
                'action' => 'delete',
                'active' => true,
            ],

            // Module Rapports
            [
                'nom' => 'rapports.view',
                'description' => 'Voir les rapports',
                'module' => 'rapports',
                'action' => 'read',
                'active' => true,
            ],

            // Module Employés
            [
                'nom' => 'employes.view',
                'description' => 'Voir les employés',
                'module' => 'employes',
                'action' => 'read',
                'active' => true,
            ],
            [
                'nom' => 'employes.create',
                'description' => 'Créer des employés',
                'module' => 'employes',
                'action' => 'create',
                'active' => true,
            ],
            [
                'nom' => 'employes.edit',
                'description' => 'Modifier les employés',
                'module' => 'employes',
                'action' => 'update',
                'active' => true,
            ],
            [
                'nom' => 'employes.delete',
                'description' => 'Supprimer les employés',
                'module' => 'employes',
                'action' => 'delete',
                'active' => true,
            ],
            [
                'nom' => 'employes.permissions',
                'description' => 'Gérer les permissions des employés',
                'module' => 'employes',
                'action' => 'permissions',
                'active' => true,
            ],

            // Module Catégories
            [
                'nom' => 'categories.view',
                'description' => 'Voir les catégories',
                'module' => 'categories',
                'action' => 'read',
                'active' => true,
            ],
            [
                'nom' => 'categories.create',
                'description' => 'Créer des catégories',
                'module' => 'categories',
                'action' => 'create',
                'active' => true,
            ],
            [
                'nom' => 'categories.edit',
                'description' => 'Modifier les catégories',
                'module' => 'categories',
                'action' => 'update',
                'active' => true,
            ],
            [
                'nom' => 'categories.delete',
                'description' => 'Supprimer les catégories',
                'module' => 'categories',
                'action' => 'delete',
                'active' => true,
            ],
        ];

        foreach ($permissions as $permission) {
            Permission::create($permission);
        }
    }
}
