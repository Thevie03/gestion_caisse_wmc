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
     * @return void
     */
    public function up()
    {
        // D'abord, mettre à jour les anciennes valeurs 'gratuit' en 'mensuel'
        DB::table('abonnements')
            ->where('type_abonnement', 'gratuit')
            ->update(['type_abonnement' => 'mensuel']);
        
        // Ensuite, modifier l'enum de type_abonnement
        DB::statement("ALTER TABLE `abonnements` MODIFY COLUMN `type_abonnement` ENUM('mensuel', 'trimestriel', 'semestriel', 'annuel') NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revenir à l'ancien enum
        DB::statement("ALTER TABLE `abonnements` MODIFY COLUMN `type_abonnement` ENUM('gratuit', 'mensuel', 'annuel') NOT NULL");
    }
};
