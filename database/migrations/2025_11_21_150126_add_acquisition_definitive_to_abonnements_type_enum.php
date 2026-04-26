<?php

use Illuminate\Database\Migrations\Migration;
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
        // Ajouter 'acquisition_definitive' à l'enum de type_abonnement
        DB::statement("ALTER TABLE `abonnements` MODIFY COLUMN `type_abonnement` ENUM('mensuel', 'trimestriel', 'semestriel', 'annuel', 'acquisition_definitive') NOT NULL");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Revenir à l'ancien enum sans 'acquisition_definitive'
        DB::statement("ALTER TABLE `abonnements` MODIFY COLUMN `type_abonnement` ENUM('mensuel', 'trimestriel', 'semestriel', 'annuel') NOT NULL");
    }
};
