<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ventes', function (Blueprint $table) {
            $table->uuid('offline_uuid')->nullable()->unique()->after('numero_vente');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->uuid('offline_uuid')->nullable()->unique()->after('boutique_id');
        });
    }

    public function down(): void
    {
        Schema::table('ventes', function (Blueprint $table) {
            $table->dropUnique(['offline_uuid']);
            $table->dropColumn('offline_uuid');
        });

        Schema::table('clients', function (Blueprint $table) {
            $table->dropUnique(['offline_uuid']);
            $table->dropColumn('offline_uuid');
        });
    }
};
