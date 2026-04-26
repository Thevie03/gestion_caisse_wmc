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
        Schema::table('users', function (Blueprint $table) {
            // Modifier le type de colonne de timestamp à datetime pour supporter les dates très lointaines
            // (nécessaire pour l'acquisition définitive qui expire dans 100 ans)
            if (Schema::hasColumn('users', 'subscription_expires_at')) {
                // Utiliser DB::statement pour modifier directement le type de colonne
                DB::statement('ALTER TABLE `users` MODIFY COLUMN `subscription_expires_at` DATETIME NULL');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            // Revenir au type timestamp (mais cela peut causer des problèmes avec les dates > 2038)
            if (Schema::hasColumn('users', 'subscription_expires_at')) {
                DB::statement('ALTER TABLE `users` MODIFY COLUMN `subscription_expires_at` TIMESTAMP NULL');
            }
        });
    }
};
