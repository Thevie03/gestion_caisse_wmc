<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('clients', function () {
            DB::statement('ALTER TABLE clients MODIFY email VARCHAR(255) NULL UNIQUE');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('clients', function () {
            DB::statement('ALTER TABLE clients MODIFY email VARCHAR(255) NOT NULL UNIQUE');
        });
    }
};









