<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
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
            $table->enum('role', ['admin', 'employe'])->default('employe');
            $table->foreignId('boutique_id')->nullable()->constrained('boutiques')->onDelete('set null');
            $table->string('telephone')->nullable();
            $table->boolean('actif')->default(true);
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
            $table->dropForeign(['boutique_id']);
            $table->dropColumn(['role', 'boutique_id', 'telephone', 'actif']);
        });
    }
};
