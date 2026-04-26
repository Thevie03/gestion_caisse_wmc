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
        Schema::create('archive_tasks', function (Blueprint $table) {
            $table->id();
            $table->string('type'); // export | import
            $table->string('status')->default('pending'); // pending | running | completed | failed
            $table->unsignedTinyInteger('progress')->default(0);
            $table->text('message')->nullable();
            $table->json('context')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('archive_id')->nullable()->constrained('archives')->nullOnDelete();
            $table->string('source_path')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archive_tasks');
    }
};














