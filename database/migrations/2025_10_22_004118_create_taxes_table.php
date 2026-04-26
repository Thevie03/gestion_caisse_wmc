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
        Schema::create('taxes', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('code')->unique();
            $table->decimal('taux', 5, 2); // Taux en pourcentage (ex: 18.00 pour 18%)
            $table->text('description')->nullable();
            $table->boolean('inclus_prix')->default(false); // Taxe incluse dans le prix ou ajoutée
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('taxes');
    }
};
