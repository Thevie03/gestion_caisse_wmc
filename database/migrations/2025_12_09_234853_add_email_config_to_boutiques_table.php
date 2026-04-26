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
            $table->string('mail_mailer', 50)->nullable()->after('email')->default('smtp');
            $table->string('mail_host', 255)->nullable()->after('mail_mailer');
            $table->integer('mail_port')->nullable()->after('mail_host')->default(587);
            $table->string('mail_username', 255)->nullable()->after('mail_port');
            $table->string('mail_password', 255)->nullable()->after('mail_username');
            $table->string('mail_encryption', 10)->nullable()->after('mail_password')->default('tls');
            $table->string('mail_from_address', 255)->nullable()->after('mail_encryption');
            $table->string('mail_from_name', 255)->nullable()->after('mail_from_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('boutiques', function (Blueprint $table) {
            $table->dropColumn([
                'mail_mailer',
                'mail_host',
                'mail_port',
                'mail_username',
                'mail_password',
                'mail_encryption',
                'mail_from_address',
                'mail_from_name',
            ]);
        });
    }
};
