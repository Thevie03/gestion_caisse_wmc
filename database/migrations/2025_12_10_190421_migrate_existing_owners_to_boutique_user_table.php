<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Migre les données existantes : pour chaque boutique avec un owner_id,
     * crée une entrée dans la table pivot boutique_user
     */
    public function up()
    {
        // Vérifier que la table pivot existe
        if (!Schema::hasTable('boutique_user')) {
            return;
        }

        // Migrer les boutiques existantes avec owner_id vers la table pivot
        $boutiques = DB::table('boutiques')
            ->whereNotNull('owner_id')
            ->get();

        foreach ($boutiques as $boutique) {
            // Vérifier si l'entrée existe déjà
            $exists = DB::table('boutique_user')
                ->where('boutique_id', $boutique->id)
                ->where('user_id', $boutique->owner_id)
                ->exists();

            if (!$exists) {
                // Déterminer si c'est la boutique principale
                // Si l'utilisateur a cette boutique comme boutique_id, c'est la principale
                $user = DB::table('users')->where('id', $boutique->owner_id)->first();
                $isPrimary = $user && $user->boutique_id == $boutique->id;

                DB::table('boutique_user')->insert([
                    'boutique_id' => $boutique->id,
                    'user_id' => $boutique->owner_id,
                    'is_primary' => $isPrimary,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Ne rien faire en cas de rollback
        // Les données dans owner_id restent intactes
    }
};
