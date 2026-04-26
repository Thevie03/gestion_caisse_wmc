<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Permission;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Créer les permissions pour les onglets de la sidebar
        $sidebarPermissions = [
            ['nom' => 'sidebar.dashboard', 'description' => 'Accès au Tableau de bord', 'module' => 'sidebar', 'action' => 'dashboard', 'active' => true],
            ['nom' => 'sidebar.point_vente', 'description' => 'Accès au Point de vente', 'module' => 'sidebar', 'action' => 'point_vente', 'active' => true],
            ['nom' => 'sidebar.historique_ventes', 'description' => 'Accès à l\'Historique des ventes', 'module' => 'sidebar', 'action' => 'historique_ventes', 'active' => true],
            ['nom' => 'sidebar.recapitulatif_ventes', 'description' => 'Accès au Récapitulatif des ventes', 'module' => 'sidebar', 'action' => 'recapitulatif_ventes', 'active' => true],
            ['nom' => 'sidebar.produits', 'description' => 'Accès aux Produits', 'module' => 'sidebar', 'action' => 'produits', 'active' => true],
            ['nom' => 'sidebar.categories', 'description' => 'Accès aux Catégories', 'module' => 'sidebar', 'action' => 'categories', 'active' => true],
            ['nom' => 'sidebar.stock', 'description' => 'Accès au Stock', 'module' => 'sidebar', 'action' => 'stock', 'active' => true],
            ['nom' => 'sidebar.clients', 'description' => 'Accès aux Clients', 'module' => 'sidebar', 'action' => 'clients', 'active' => true],
            ['nom' => 'sidebar.fournisseurs', 'description' => 'Accès aux Fournisseurs', 'module' => 'sidebar', 'action' => 'fournisseurs', 'active' => true],
            ['nom' => 'sidebar.depenses', 'description' => 'Accès aux Dépenses', 'module' => 'sidebar', 'action' => 'depenses', 'active' => true],
            ['nom' => 'sidebar.rapports', 'description' => 'Accès aux Rapports', 'module' => 'sidebar', 'action' => 'rapports', 'active' => true],
            ['nom' => 'sidebar.archivage', 'description' => 'Accès à l\'Archivage', 'module' => 'sidebar', 'action' => 'archivage', 'active' => true],
            ['nom' => 'sidebar.boutiques', 'description' => 'Accès aux Boutiques', 'module' => 'sidebar', 'action' => 'boutiques', 'active' => true],
            ['nom' => 'sidebar.employes', 'description' => 'Accès aux Employés', 'module' => 'sidebar', 'action' => 'employes', 'active' => true],
        ];

        foreach ($sidebarPermissions as $permission) {
            Permission::firstOrCreate(
                ['nom' => $permission['nom']],
                $permission
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Supprimer les permissions de sidebar
        Permission::where('module', 'sidebar')->delete();
    }
};



