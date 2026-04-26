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
        // Modifier l'enum pour ajouter 'super_admin'
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'employe', 'super_admin') DEFAULT 'employe'");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Retirer 'super_admin' de l'enum
        DB::statement("ALTER TABLE users MODIFY COLUMN role ENUM('admin', 'employe') DEFAULT 'employe'");
    }
};
