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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('titre');
            $table->text('message');
            $table->enum('type', ['info', 'warning', 'error', 'success'])->default('info');
            $table->enum('priorite', ['basse', 'normale', 'haute', 'critique'])->default('normale');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('module')->nullable(); // stock, ventes, etc.
            $table->json('data')->nullable(); // Données supplémentaires
            $table->boolean('lue')->default(false);
            $table->timestamp('lue_at')->nullable();
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
        Schema::dropIfExists('notifications');
    }
};
