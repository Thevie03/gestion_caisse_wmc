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
        Schema::create('archives', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('disk_path');
            $table->unsignedBigInteger('size')->default(0);
            $table->string('checksum', 128)->nullable();
            $table->string('status')->default('completed');
            $table->json('options')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('available_until')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archives');
    }
};














