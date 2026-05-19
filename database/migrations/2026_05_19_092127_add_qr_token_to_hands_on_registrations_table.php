<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hands_on_registrations', function (Blueprint $table) {
            $table->string('qr_token', 64)->nullable()->unique()->after('registration_code');
            $table->timestamp('qr_expires_at')->nullable()->after('qr_token');
        });
    }

    public function down(): void
    {
        Schema::table('hands_on_registrations', function (Blueprint $table) {
            $table->dropColumn(['qr_token', 'qr_expires_at']);
        });
    }
};
