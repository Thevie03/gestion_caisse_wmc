<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('boutiques', function (Blueprint $table) {
            $table->string('pos_banner_image')->nullable()->after('logo');
            $table->string('pos_stock_image')->nullable()->after('pos_banner_image');
            $table->string('pos_payment_image')->nullable()->after('pos_stock_image');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boutiques', function (Blueprint $table) {
            $table->dropColumn([
                'pos_banner_image',
                'pos_stock_image',
                'pos_payment_image',
            ]);
        });
    }
};
