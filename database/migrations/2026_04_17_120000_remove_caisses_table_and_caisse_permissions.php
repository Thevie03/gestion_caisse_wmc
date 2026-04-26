<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Supprime la table caisses et les permissions liées (module caisse + onglet sidebar).
     */
    public function up(): void
    {
        $permissionIds = DB::table('permissions')
            ->where(function ($q) {
                $q->where('module', 'caisse')
                    ->orWhere('nom', 'sidebar.caisse');
            })
            ->pluck('id');

        if ($permissionIds->isNotEmpty()) {
            DB::table('user_permissions')->whereIn('permission_id', $permissionIds)->delete();
            DB::table('permissions')->whereIn('id', $permissionIds)->delete();
        }

        Schema::dropIfExists('caisses');
    }

    /**
     * Pas de recréation automatique des données métier.
     */
    public function down(): void
    {
        //
    }
};
