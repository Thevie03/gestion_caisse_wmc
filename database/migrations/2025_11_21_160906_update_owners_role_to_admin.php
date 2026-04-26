<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Mettre à jour tous les propriétaires de boutique pour qu'ils aient le rôle 'admin'
        // Un propriétaire de boutique (owner_id) doit avoir le rôle d'administrateur
        DB::statement("
            UPDATE users
            INNER JOIN boutiques ON users.id = boutiques.owner_id
            SET users.role = 'admin'
            WHERE users.role != 'admin' 
            AND users.role != 'super_admin'
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // On ne peut pas vraiment inverser cette migration car on ne sait pas quel était le rôle original
        // Mais on peut laisser vide ou mettre un commentaire
    }
};
