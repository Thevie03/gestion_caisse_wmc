<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fournisseurs', function () {
            DB::statement('ALTER TABLE fournisseurs MODIFY email VARCHAR(255) NULL UNIQUE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fournisseurs', function () {
            DB::statement('ALTER TABLE fournisseurs MODIFY email VARCHAR(255) NOT NULL UNIQUE');
        });
    }
};

